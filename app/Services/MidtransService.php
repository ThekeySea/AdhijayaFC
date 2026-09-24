<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransService
{
    /**
     * Metode pembayaran yang didukung overlay custom.
     *
     * @var list<string>
     */
    public const SUPPORTED_METHODS = ['qris', 'bank_transfer'];

    /**
     * Bank VA yang ditawarkan (hanya untuk transfer bank).
     *
     * @var list<string>
     */
    public const SUPPORTED_BANKS = ['bca', 'bni', 'bri', 'mandiri'];

    public function amountDueFor(float $total): float
    {
        $threshold = (float) config('midtrans.dp_threshold', 100000);

        if ($total > $threshold) {
            $percent = (float) config('midtrans.dp_percent', 50);

            return round($total * $percent / 100, 0);
        }

        return round($total, 0);
    }

    public function isDownPayment(float $total): bool
    {
        return $total > (float) config('midtrans.dp_threshold', 100000);
    }

    /**
     * Charge Midtrans Core API v2 (QRIS / bank transfer) untuk order belum dibayar.
     *
     * @param  string  $method  qris|bank_transfer
     * @param  string  $bank  hanya untuk bank_transfer (bca|bni|bri|mandiri)
     * @return array{
     *     method: string,
     *     bank: ?string,
     *     amount_due: int,
     *     order_number: string,
     *     qr_string: ?string,
     *     qr_code: ?string,
     *     va_number: ?string,
     *     expires_at: ?string,
     *     payment_status: string
     * }
     *
     * @throws ConnectionException
     * @throws RuntimeException
     */
    public function createCharge(Order $order, string $method = 'qris', string $bank = 'bca'): array
    {
        if (! in_array($method, self::SUPPORTED_METHODS, true)) {
            throw new RuntimeException('Metode pembayaran tidak didukung.');
        }

        if ($method === 'bank_transfer' && ! in_array($bank, self::SUPPORTED_BANKS, true)) {
            throw new RuntimeException('Bank tidak didukung.');
        }

        if ($order->payment_status->value === 'PAID') {
            throw new RuntimeException('Pesanan sudah dibayar.');
        }

        $pending = Payment::query()
            ->where('order_id', $order->id)
            ->where('transaction_status', 'pending')
            ->latest()
            ->first();

        if ($pending !== null && is_array($pending->charge_response) && $pending->charge_response !== []) {
            $storedMethod = (string) ($pending->charge_response['method'] ?? '');
            $storedBank = (string) ($pending->charge_response['bank'] ?? '');
            $notExpired = $pending->expires_at === null || $pending->expires_at->isFuture();

            if ($notExpired && $storedMethod === $method && ($method !== 'bank_transfer' || $storedBank === $bank)) {
                return $this->chargePayload($order, $pending->charge_response);
            }
        }

        $payload = [
            'payment_type' => $method === 'qris' ? 'qris' : 'bank_transfer',
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->amount_due,
            ],
            'customer_details' => [
                'first_name' => $order->customer?->name ?? 'Pelanggan',
                'email' => $order->customer?->email,
            ],
            'item_details' => $this->itemDetailsFor($order),
        ];

        if ($method === 'bank_transfer') {
            $payload['bank_transfer'] = ['bank' => $bank];
        }

        $response = Http::withBasicAuth((string) config('midtrans.server_key'), '')
            ->acceptJson()
            ->asJson()
            ->post(config('midtrans.api_url').'/v2/charge', $payload);

        if ($response->failed()) {
            $message = $response->json('error_messages.0')
                ?? $response->json('validation_messages.0')
                ?? $response->json('status_message')
                ?? 'Gagal membuat pembayaran (HTTP '.$response->status().'). Coba lagi nanti.';

            throw new RuntimeException((string) $message);
        }

        $charge = $response->json();
        if (! is_array($charge)) {
            throw new RuntimeException('Respons pembayaran tidak valid dari Midtrans.');
        }
        $vaNumber = null;

        if (isset($charge['va_numbers'][0]['va_number'])) {
            $vaNumber = (string) $charge['va_numbers'][0]['va_number'];
        } elseif (isset($charge['bill_key'])) {
            $vaNumber = (string) $charge['bill_key'];
        }

        $expiresAt = null;
        $expiryRaw = $charge['expiry_time'] ?? $charge['expires_time'] ?? null;

        if (is_string($expiryRaw) && $expiryRaw !== '') {
            try {
                $expiresAt = Carbon::parse($expiryRaw);
            } catch (\Throwable) {
                $expiresAt = now()->addDay();
            }
        } else {
            $expiresAt = now()->addDay();
        }

        $stored = array_merge(is_array($charge) ? $charge : [], [
            'method' => $method,
            'bank' => $method === 'bank_transfer' ? $bank : null,
            'qr_string' => $charge['qr_string'] ?? $charge['canonical_qr_string'] ?? null,
            'qr_code' => $charge['qr_code'] ?? null,
            'va_number' => $vaNumber,
        ]);

        $payment = $pending ?? new Payment(['order_id' => $order->id]);
        $payment->fill([
            'provider' => 'midtrans',
            'snap_token' => $charge['transaction_id'] ?? $charge['order_id'] ?? $payment->snap_token,
            'gross_amount' => $order->amount_due,
            'transaction_status' => 'pending',
            'payment_type' => $method === 'qris' ? 'qris' : 'bank_transfer',
            'charge_response' => $stored,
            'expires_at' => $expiresAt,
        ])->save();

        return $this->chargePayload($order, $stored + [
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * item_details wajib berjumlah sama persis dengan gross_amount (amount_due).
     * DP / biaya opsional membuat jumlah item berbeda → konsolidasi jadi 1 baris tagihan.
     *
     * @return list<array{id: string, price: int, quantity: int, name: string}>
     */
    private function itemDetailsFor(Order $order): array
    {
        $grossAmount = max(1, (int) $order->amount_due);

        $items = $order->items
            ->map(fn ($item) => [
                'id' => (string) $item->id,
                'price' => max(1, (int) $item->unit_price_snapshot),
                'quantity' => max(1, (int) $item->quantity),
                'name' => mb_substr($item->service_name_snapshot, 0, 50),
            ])
            ->values()
            ->all();

        $itemSum = array_reduce(
            $items,
            static fn (int $sum, array $item) => $sum + ($item['price'] * $item['quantity']),
            0
        );

        if ($items !== [] && $itemSum === $grossAmount) {
            return $items;
        }

        $name = $order->hasDownPayment()
            ? 'Uang muka pesanan '.$order->order_number
            : 'Tagihan pesanan '.$order->order_number;

        if ($items !== []) {
            $name = mb_substr(
                collect($items)->pluck('name')->unique()->implode(', ').' — '.$name,
                0,
                50
            );
        }

        return [[
            'id' => $order->order_number,
            'price' => $grossAmount,
            'quantity' => 1,
            'name' => $name,
        ]];
    }

    /**
     * @param  array<string, mixed>  $charge
     * @return array{
     *     method: string,
     *     bank: ?string,
     *     amount_due: int,
     *     order_number: string,
     *     qr_string: ?string,
     *     qr_code: ?string,
     *     va_number: ?string,
     *     expires_at: ?string,
     *     payment_status: string
     * }
     */
    private function chargePayload(Order $order, array $charge): array
    {
        $method = (string) ($charge['method'] ?? 'qris');
        $expiresAt = $charge['expires_at'] ?? null;

        if (is_string($expiresAt) && $expiresAt !== '') {
            try {
                $expiresAt = Carbon::parse($expiresAt)->toIso8601String();
            } catch (\Throwable) {
                $expiresAt = null;
            }
        } elseif ($expiresAt instanceof \DateTimeInterface) {
            $expiresAt = $expiresAt->format(DATE_ATOM);
        } else {
            $expiresAt = null;
        }

        return [
            'method' => $method,
            'bank' => isset($charge['bank']) && $charge['bank'] !== null ? (string) $charge['bank'] : null,
            'amount_due' => (int) $order->amount_due,
            'order_number' => $order->order_number,
            'qr_string' => isset($charge['qr_string']) ? (string) $charge['qr_string'] : null,
            'qr_code' => isset($charge['qr_code']) ? (string) $charge['qr_code'] : null,
            'va_number' => isset($charge['va_number']) && $charge['va_number'] !== null ? (string) $charge['va_number'] : null,
            'expires_at' => $expiresAt,
            'payment_status' => $order->payment_status->value,
        ];
    }

    public function isValidSignature(array $notification): bool
    {
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey === '' || ! isset($notification['signature_key'])) {
            return false;
        }

        $expected = hash('sha512', implode('', [
            $notification['order_id'] ?? '',
            $notification['gross_amount'] ?? '',
            $notification['status_code'] ?? '',
            $notification['transaction_status'] ?? '',
            $serverKey,
        ]));

        return hash_equals($expected, (string) $notification['signature_key']);
    }
}
