<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransService
{
    /**
     * Metode pembayaran yang diizinkan: QRIS dan transfer bank saja.
     *
     * @var list<string>
     */
    public const ENABLED_PAYMENTS = ['bank_transfer', 'qris'];

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
     * Buat atau perbarui transaksi Snap untuk order yang belum dibayar.
     * Mengembalikan snap token.
     *
     * @throws ConnectionException
     */
    public function createSnapToken(Order $order): string
    {
        if ($order->payment_status->value === 'PAID') {
            throw new RuntimeException('Pesanan sudah dibayar.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) $order->amount_due,
            ],
            'enabled_payments' => self::ENABLED_PAYMENTS,
            'customer_details' => [
                'first_name' => $order->customer?->name ?? 'Pelanggan',
                'email' => $order->customer?->email,
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id' => (string) $item->id,
                'price' => (int) $item->unit_price_snapshot,
                'quantity' => $item->quantity,
                'name' => mb_substr($item->service_name_snapshot, 0, 50),
            ])->values()->all(),
            'expiry' => [
                'unit' => 'hours',
                'duration' => 24,
            ],
        ];

        $response = Http::withBasicAuth((string) config('midtrans.server_key'), '')
            ->acceptJson()
            ->post(config('midtrans.api_url').'/snap/v1/transactions', $payload);

        if ($response->failed()) {
            throw new RuntimeException('Gagal membuat transaksi pembayaran. Coba lagi nanti.');
        }

        $token = (string) $response->json('token');

        if ($token === '') {
            throw new RuntimeException('Token pembayaran tidak diterima.');
        }

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('transaction_status', 'pending')
            ->latest()
            ->first();

        if ($payment === null) {
            $payment = new Payment(['order_id' => $order->id]);
        }

        $payment->fill([
            'provider' => 'midtrans',
            'snap_token' => $token,
            'gross_amount' => $order->amount_due,
            'transaction_status' => 'pending',
        ])->save();

        return $token;
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
