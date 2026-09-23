<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Order;
use App\Models\OrderFile;
use App\Services\MidtransService;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        if ($request->user()?->isAdmin()) {
            abort(403);
        }

        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Keranjang masih kosong. Tambah layanan dulu sebelum checkout.');
        }

        $subtotal = (float) $lines->sum('subtotal');
        $amountDue = $this->midtrans->amountDueFor($subtotal);

        return view('checkout.create', [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'amountDue' => $amountDue,
            'remaining' => $subtotal - $amountDue,
            'isDownPayment' => $this->midtrans->isDownPayment($subtotal),
            'timeSlots' => Booking::TIME_SLOTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_if($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'pickup_date' => ['required', 'date', 'after_or_equal:today'],
            'time_slot' => ['required', Rule::in(Booking::TIME_SLOTS)],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'files' => ['nullable', 'array', 'max:'.OrderFileController::MAX_FILES],
            'files.*' => [
                'file',
                'max:'.OrderFileController::MAX_FILE_KB,
                'mimes:'.implode(',', OrderFileController::ALLOWED_EXTENSIONS),
            ],
        ]);

        $request->user()->update(['phone' => $validated['phone']]);

        $lines = Cart::lines();

        if ($lines->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('status', 'Keranjang masih kosong. Tambah layanan dulu sebelum checkout.');
        }

        foreach ($lines as $line) {
            if ($line['service']->requiresFile() && empty($line['files'])) {
                return back()
                    ->withInput()
                    ->withErrors(['files' => 'Unggah file untuk layanan '.$line['service']->name.' terlebih dahulu di keranjang.']);
            }
        }

        $order = DB::transaction(function () use ($request, $validated, $lines) {
            $subtotal = (float) $lines->sum('subtotal');
            $fixedOptionTotal = (float) $lines->sum('fixed_option_total');
            $amountDue = $this->midtrans->amountDueFor($subtotal);

            $booking = Booking::create([
                'customer_id' => $request->user()->id,
                'booking_date' => $validated['pickup_date'],
                'time_slot' => $validated['time_slot'],
                'note' => $validated['customer_note'] ?? null,
                'status' => 'SCHEDULED',
            ]);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'customer_id' => $request->user()->id,
                'booking_id' => $booking->id,
                'status' => OrderStatus::PendingPayment,
                'payment_status' => PaymentStatus::Unpaid,
                'subtotal' => $subtotal - $fixedOptionTotal,
                'additional_fee' => $fixedOptionTotal,
                'total' => $subtotal,
                'amount_due' => $amountDue,
                'remaining_amount' => $subtotal - $amountDue,
                'customer_note' => $validated['customer_note'] ?? null,
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
                $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-');
                $extension = strtolower($file->getClientOriginalExtension());
                $stored = $file->storeAs(
                    'orders/'.$order->id,
                    Str::uuid().'-'.$safeName.'.'.$extension,
                    'local'
                );

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

                    if ($path !== '' && Storage::disk('local')->exists($path)) {
                        Storage::disk('local')->move($path, $target);

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

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Pesanan '.$order->order_number.' berhasil dibuat.');
    }
}
