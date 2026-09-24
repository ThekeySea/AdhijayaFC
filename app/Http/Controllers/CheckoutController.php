<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryMode;
use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderCreated;
use App\Models\Booking;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\Service;
use App\Services\DeliveryPricing;
use App\Services\MidtransService;
use App\Services\OpeningHours;
use App\Support\Cart;
use App\Support\OrderFileStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly DeliveryPricing $deliveryPricing,
        private readonly OrderFileStorage $storage,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isAdmin()) {
            abort(403);
        }

        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return $this->emptyCartRedirect();
        }

        $settings = BusinessSetting::current();
        $subtotal = (float) $lines->sum('subtotal');
        $minReady = $this->minReadyMinutes($lines);
        $bookableDates = OpeningHours::bookableDates();
        $defaultDate = $bookableDates[0] ?? now()->toDateString();

        return view('checkout.create', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'amountDue' => $this->midtrans->amountDueFor($subtotal),
            'remaining' => $subtotal - $this->midtrans->amountDueFor($subtotal),
            'isDownPayment' => $this->midtrans->isDownPayment($subtotal),
            'timeSlots' => OpeningHours::slotsForDate($defaultDate, $minReady),
            'bookableDates' => $bookableDates,
            'minReadyMinutes' => $minReady,
            'storeLatitude' => $settings->latitude,
            'storeLongitude' => $settings->longitude,
            'deliveryEnabled' => $settings->latitude !== null && $settings->longitude !== null,
        ]);
    }

    /**
     * Estimasi ongkir live (Alpine fetch saat pin maps berubah).
     */
    public function deliveryQuote(Request $request): JsonResponse
    {
        abort_if($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
        ]);

        $subtotal = (float) ($validated['subtotal'] ?? Cart::lines()->sum('subtotal'));

        try {
            $quote = $this->deliveryPricing->quote(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                $subtotal,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'distance_km' => $quote['distance_km'],
            'fee' => $quote['fee'],
            'discount' => $quote['discount'],
            'within_radius' => $quote['within_radius'],
            'max_radius_km' => $quote['max_radius_km'],
            'formatted_fee' => Cart::formatAmount((float) $quote['fee']),
            'formatted_discount' => Cart::formatAmount((float) $quote['discount']),
        ]);
    }

    public function slots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $minReady = $this->minReadyMinutes(Cart::lines());

        return response()->json([
            'slots' => OpeningHours::slotsForDate($validated['date'], $minReady),
            'open' => OpeningHours::isOpenOn($validated['date']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isAdmin(), 403);

        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return $this->emptyCartRedirect();
        }

        $minReady = $this->minReadyMinutes($lines);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'fulfillment_type' => ['nullable', Rule::in([FulfillmentType::Pickup->value, FulfillmentType::Delivery->value])],
            'pickup_date' => ['nullable', 'date', 'after_or_equal:today'],
            'time_slot' => ['nullable', 'string', 'max:30'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'delivery_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'delivery_mode' => ['nullable', Rule::in([DeliveryMode::Asap->value, DeliveryMode::Scheduled->value])],
            'files' => ['nullable', 'array', 'max:'.OrderFileController::MAX_FILES],
            'files.*' => [
                'file',
                'max:'.OrderFileController::MAX_FILE_KB,
                'mimes:'.implode(',', OrderFileController::ALLOWED_EXTENSIONS),
            ],
        ]);

        $fulfillment = FulfillmentType::from($validated['fulfillment_type'] ?? FulfillmentType::Pickup->value);
        $isDelivery = $fulfillment === FulfillmentType::Delivery;
        $deliveryMode = null;
        $quote = null;

        if ($isDelivery) {
            $this->validateDelivery($request, $validated);
            $deliveryMode = DeliveryMode::from($validated['delivery_mode'] ?? DeliveryMode::Asap->value);

            try {
                $quote = $this->deliveryPricing->quote(
                    (float) $validated['delivery_latitude'],
                    (float) $validated['delivery_longitude'],
                    (float) $lines->sum('subtotal'),
                );
            } catch (InvalidArgumentException $e) {
                throw ValidationException::withMessages([
                    'delivery_address' => $e->getMessage(),
                ]);
            }

            if (! $quote['within_radius']) {
                throw ValidationException::withMessages([
                    'delivery_address' => 'Jarak delivery maksimal '.$quote['max_radius_km'].' km dari toko.',
                ]);
            }

            if ($deliveryMode === DeliveryMode::Scheduled) {
                $this->validateSchedule($validated, $minReady);
            }
        } else {
            $this->validateSchedule($validated, $minReady);
        }

        $request->user()->update(['phone' => $validated['phone']]);

        foreach ($lines as $line) {
            if ($line['service']->requiresFile() && empty($line['files'])) {
                return back()
                    ->withInput()
                    ->withErrors(['files' => 'Unggah file untuk layanan '.$line['service']->name.' terlebih dahulu di keranjang.']);
            }
        }

        $order = DB::transaction(function () use ($request, $validated, $lines, $fulfillment, $isDelivery, $deliveryMode, $quote) {
            $subtotal = (float) $lines->sum('subtotal');
            $fixedOptionTotal = (float) $lines->sum('fixed_option_total');
            $deliveryFee = $isDelivery && $quote !== null ? (float) $quote['fee'] : 0.0;
            $payable = $subtotal + $deliveryFee;
            $amountDue = $this->midtrans->amountDueFor($payable);

            $bookingDate = null;
            $timeSlot = null;

            if (! $isDelivery || $deliveryMode === DeliveryMode::Scheduled) {
                $bookingDate = $validated['pickup_date'];
                $timeSlot = $validated['time_slot'];
            }

            $booking = Booking::create([
                'customer_id' => $request->user()->id,
                'booking_date' => $bookingDate,
                'time_slot' => $timeSlot,
                'note' => $validated['customer_note'] ?? null,
                'status' => $isDelivery && $deliveryMode === DeliveryMode::Asap ? 'ASAP' : 'SCHEDULED',
            ]);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $request->user()->id,
                'booking_id' => $booking->id,
                'status' => OrderStatus::PendingPayment,
                'payment_status' => PaymentStatus::Unpaid,
                'subtotal' => $subtotal - $fixedOptionTotal,
                'additional_fee' => $fixedOptionTotal,
                'total' => $payable,
                'amount_due' => $amountDue,
                'remaining_amount' => $payable - $amountDue,
                'customer_note' => $validated['customer_note'] ?? null,
                'fulfillment_type' => $fulfillment->value,
                'delivery_address' => $isDelivery ? $validated['delivery_address'] : null,
                'delivery_latitude' => $isDelivery ? (float) $validated['delivery_latitude'] : null,
                'delivery_longitude' => $isDelivery ? (float) $validated['delivery_longitude'] : null,
                'delivery_distance_km' => $isDelivery && $quote !== null ? $quote['distance_km'] : null,
                'delivery_fee' => $deliveryFee,
                'delivery_mode' => $isDelivery && $deliveryMode !== null ? $deliveryMode->value : null,
            ]);

            foreach ($lines as $line) {
                $notes = [];

                if ($line['detail'] !== '') {
                    $notes[] = $line['detail'];
                }

                if ($line['options']->isNotEmpty()) {
                    $notes[] = 'Opsi: '.$line['options']->map(
                        static fn ($option) => $option->name.' ('.$option->formattedPrice().')'
                    )->implode(', ');
                }

                $order->items()->create([
                    'service_id' => $line['service']->id,
                    'service_name_snapshot' => $line['service']->name,
                    'unit_price_snapshot' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'item_note' => $notes !== [] ? implode(' — ', $notes) : null,
                ]);
            }

            foreach ($request->file('files', []) as $file) {
                $stored = $this->storage->storeUploadedFile($file, 'orders/'.$order->id);

                if ($stored === null) {
                    continue;
                }

                OrderFile::create([
                    'order_id' => $order->id,
                    'file_name' => $file->getClientOriginalName(),
                    'storage_path' => $stored,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize(),
                ]);
            }

            foreach ($lines as $line) {
                foreach ($line['files'] ?? [] as $cartFile) {
                    $path = $cartFile['path'] ?? '';
                    $target = 'orders/'.$order->id.'/'.basename($path);

                    if ($path !== '' && $this->storage->exists($path) && $this->storage->move($path, $target)) {
                        OrderFile::create([
                            'order_id' => $order->id,
                            'file_name' => $cartFile['name'] ?? basename($path),
                            'storage_path' => $target,
                            'mime_type' => $cartFile['mime'] ?? 'application/octet-stream',
                            'file_size' => (int) ($cartFile['size'] ?? 0),
                        ]);
                    }
                }
            }

            Cart::clear();

            return $order;
        });

        try {
            OrderCreated::dispatch($order);
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Pesanan '.$order->order_number.' berhasil dibuat.');
    }

    private function emptyCartRedirect(): RedirectResponse
    {
        return redirect()
            ->route('cart.index')
            ->with('status', 'Keranjang masih kosong. Tambah layanan dulu sebelum checkout.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validateDelivery(Request $request, array $validated): void
    {
        $rules = [
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_latitude' => ['required', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['required', 'numeric', 'between:-180,180'],
            'delivery_mode' => ['required', Rule::in([DeliveryMode::Asap->value, DeliveryMode::Scheduled->value])],
        ];

        $request->validate($rules);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function validateSchedule(array $validated, int $minReady): void
    {
        if (($validated['pickup_date'] ?? null) === null || ($validated['time_slot'] ?? null) === null) {
            throw ValidationException::withMessages([
                'pickup_date' => 'Tanggal jadwal wajib diisi.',
                'time_slot' => 'Slot waktu wajib dipilih.',
            ]);
        }

        if (! OpeningHours::isOpenOn($validated['pickup_date'])) {
            throw ValidationException::withMessages([
                'pickup_date' => 'Toko tutup pada tanggal tersebut. Pilih tanggal lain.',
            ]);
        }

        if (! OpeningHours::isValidSlot($validated['pickup_date'], (string) $validated['time_slot'], $minReady)) {
            throw ValidationException::withMessages([
                'time_slot' => 'Slot waktu tidak tersedia (di luar jam buka atau belum siap).',
            ]);
        }
    }

    /**
     * @param  Collection<int, array{service: Service}>  $lines
     */
    private function minReadyMinutes($lines): int
    {
        if ($lines->isEmpty()) {
            return 0;
        }

        return (int) $lines
            ->map(fn (array $line) => $line['service']->minReadyMinutes())
            ->max();
    }
}
