<x-admin-layout>
    <x-slot name="header">
        <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Breadcrumb">
            <a href="{{ route('admin.orders.index') }}" class="font-medium text-muted transition hover:text-primary">Pesanan</a>
            <span class="text-border" aria-hidden="true">/</span>
            <span class="font-medium text-foreground">{{ $order->order_number }}</span>
        </nav>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">{{ $order->order_number }}</h1>
        <p class="mt-1 text-sm text-muted">Dibuat {{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-order-tracking :order="$order" />

            <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <h2 class="text-base font-semibold text-foreground">Item</h2>
                    <span class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ $order->status->badgeClass() }}">
                        {{ $order->status->label() }}
                    </span>
                </div>
                <ul class="mt-4 divide-y divide-border">
                    @foreach ($order->items as $item)
                        <li class="flex flex-wrap items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-foreground">{{ $item->service_name_snapshot }}</p>
                                <p class="mt-0.5 text-sm text-muted">
                                    {{ $item->formattedUnitPrice() }} × {{ $item->quantity }}
                                </p>
                                @if ($item->item_note)
                                    <p class="mt-1 text-sm text-foreground">
                                        <span class="font-medium text-muted">Detail:</span> {{ $item->item_note }}
                                    </p>
                                @endif
                            </div>
                            <p class="shrink-0 text-sm font-bold tabular-nums text-foreground">
                                {{ $item->formattedSubtotal() }}
                            </p>
                        </li>
                    @endforeach
                </ul>

                @if ($order->customer_note)
                    <div class="mt-4 rounded-xl bg-background p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Catatan pelanggan</p>
                        <p class="mt-1 text-sm leading-relaxed text-foreground">{{ $order->customer_note }}</p>
                    </div>
                @endif
            </div>

            @if ($order->booking)
                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">
                        {{ $order->isDelivery() && $order->delivery_mode === \App\Enums\DeliveryMode::Scheduled
                            ? 'Jadwal kirim'
                            : ($order->isDelivery() ? 'Info delivery' : 'Jadwal ambil') }}
                    </h2>
                    @if ($order->booking->booking_date && $order->booking->time_slot)
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl bg-background p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Tanggal</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground">{{ $order->booking->formattedDate() }}</dd>
                            </div>
                            <div class="rounded-xl bg-background p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Slot waktu</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground">{{ $order->booking->time_slot }}</dd>
                            </div>
                        </dl>
                    @elseif ($order->isDelivery() && $order->delivery_mode === \App\Enums\DeliveryMode::Asap)
                        <p class="mt-3 text-sm text-muted">Dikirim segera setelah pesanan selesai diproses (READY).</p>
                    @else
                        <p class="mt-3 text-sm text-muted">Tanpa jadwal tetap.</p>
                    @endif
                    @if ($order->booking->note)
                        <p class="mt-3 text-sm text-muted">Catatan booking: {{ $order->booking->note }}</p>
                    @endif
                </div>
            @endif

            <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                <h2 class="text-base font-semibold text-foreground">File pelanggan</h2>

                @if ($order->files->isEmpty())
                    <p class="mt-4 rounded-xl border border-dashed border-border bg-background px-4 py-6 text-center text-sm text-muted">
                        Belum ada file dari pelanggan.
                    </p>
                @else
                    <ul class="mt-4 divide-y divide-border">
                        @foreach ($order->files as $file)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-foreground">{{ $file->file_name }}</p>
                                    <p class="mt-0.5 text-xs text-muted">{{ $file->formattedSize() }} · {{ $file->mime_type }}</p>
                                </div>
                                <a href="{{ route('orders.files.download', [$order, $file]) }}"
                                   class="inline-flex min-h-11 shrink-0 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                                    Unduh
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($order->payments->isNotEmpty())
                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">Pembayaran</h2>
                    <ul class="mt-4 divide-y divide-border">
                        @foreach ($order->payments as $payment)
                            <li class="py-3 first:pt-0 last:pb-0">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-medium text-foreground">
                                        {{ $payment->payment_type ? str_replace('_', ' ', $payment->payment_type) : 'Midtrans' }}
                                        <span class="ml-2 text-xs font-semibold uppercase text-muted">{{ $payment->transaction_status }}</span>
                                    </p>
                                    <p class="text-sm font-bold tabular-nums text-foreground">Rp {{ number_format((float) $payment->gross_amount, 0, ',', '.') }}</p>
                                </div>
                                @if ($payment->provider_transaction_id)
                                    <p class="mt-1 text-xs text-muted">TX: {{ $payment->provider_transaction_id }}</p>
                                @endif
                                @if ($payment->paid_at)
                                    <p class="mt-0.5 text-xs text-muted">Dibayar {{ $payment->paid_at->translatedFormat('d F Y, H:i') }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <aside class="lg:col-span-1">
            <div class="space-y-4 lg:sticky lg:top-24">
                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Pelanggan</p>
                    <p class="mt-3 text-base font-semibold text-foreground">{{ $order->customer?->name ?? '—' }}</p>
                    <p class="mt-1 text-sm text-muted">{{ $order->customer?->email ?? '—' }}</p>

                    <div class="mt-4 rounded-xl border border-border bg-background p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Penerimaan</p>
                        <span class="mt-1 inline-flex rounded-lg px-2.5 py-1 text-sm font-semibold {{ $order->fulfillment_type instanceof \App\Enums\FulfillmentType ? $order->fulfillment_type->badgeClass() : ($order->fulfillment_type === 'delivery' ? 'bg-sky-50 text-sky-700' : 'bg-emerald-50 text-emerald-700') }}">
                            {{ $order->fulfillment_type instanceof \App\Enums\FulfillmentType ? $order->fulfillment_type->label() : ($order->fulfillment_type === 'delivery' ? 'Delivery' : 'Ambil di tempat') }}
                        </span>
                        @if ($order->isDelivery())
                            @if ($order->delivery_mode)
                                <p class="mt-2 text-xs text-muted">
                                    Mode: {{ $order->delivery_mode instanceof \App\Enums\DeliveryMode ? $order->delivery_mode->label() : $order->delivery_mode }}
                                </p>
                            @endif
                            @if ($order->delivery_address)
                                <p class="mt-1 text-sm leading-relaxed text-foreground">{{ $order->delivery_address }}</p>
                            @endif
                            @if ($order->delivery_distance_km !== null)
                                <p class="mt-1 text-xs text-muted tabular-nums">
                                    {{ number_format((float) $order->delivery_distance_km, 1, ',', '.') }} km · Ongkir {{ $order->formattedDeliveryFee() }}
                                </p>
                            @endif
                        @endif
                    </div>

                    @php
                        $customerWa = \App\Lib\WhatsApp::buildWhatsAppUrl(
                            $order->customer?->phone,
                            'Halo '.$order->customer?->name.', pesanan '.$order->order_number.' berstatus: '.$order->status->label().'.'
                        );
                    @endphp
                    @if ($customerWa)
                        <a href="{{ $customerWa }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-primary-line bg-primary-soft px-4 text-sm font-semibold text-primary transition hover:bg-primary/10">
                            Hubungi via WhatsApp
                        </a>
                    @endif
                </div>

                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Ringkasan</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Subtotal</dt>
                            <dd class="font-medium tabular-nums text-foreground">{{ $order->formattedSubtotal() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Biaya tambahan</dt>
                            <dd class="font-medium tabular-nums text-foreground">{{ \App\Support\Cart::formatAmount((float) $order->additional_fee) }}</dd>
                        </div>
                        @if ($order->isDelivery() && (float) $order->delivery_fee > 0)
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-muted">Ongkir</dt>
                                <dd class="font-medium tabular-nums text-foreground">{{ $order->formattedDeliveryFee() }}</dd>
                            </div>
                        @endif
                        <div class="flex items-center justify-between gap-4 border-t border-border pt-3">
                            <dt class="font-semibold text-foreground">Total</dt>
                            <dd class="text-lg font-bold tabular-nums text-foreground">{{ $order->formattedTotal() }}</dd>
                        </div>
                        @if ($order->hasDownPayment())
                            <div class="rounded-xl border border-primary-line bg-primary-soft p-3">
                                <p class="text-xs font-semibold text-primary">Uang muka (DP) 50%</p>
                                <p class="mt-1 text-sm font-bold tabular-nums text-foreground">Tagihan: {{ $order->formattedAmountDue() }}</p>
                                <p class="mt-1 text-xs leading-relaxed text-muted">Sisa {{ $order->formattedRemaining() }} di tempat.</p>
                            </div>
                        @endif
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Pembayaran</dt>
                            <dd class="font-medium text-foreground">{{ $order->payment_status->label() }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Status timeline</p>
                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        Status saat ini:
                        <span class="font-semibold {{ $order->status->badgeClass() }} rounded-md px-1.5 py-0.5">
                            {{ $order->status->label() }}
                        </span>
                    </p>
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wide text-muted">Tentukan status</label>
                        <select id="status" name="status"
                                class="mt-1 block w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/30">
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status->value }}" @selected($order->status === $status)>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                            Simpan status
                        </button>
                    </form>

                    @php
                        $shareUrl = session('wa_share_url') ?: $customerWaUrl;
                        $justUpdated = (bool) session('wa_share_ready');
                    @endphp

                    @if ($shareUrl)
                        @if ($justUpdated)
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Siap dikirim</p>
                                <p class="mt-1 text-sm leading-relaxed text-emerald-900">
                                    Status diperbarui. Buka WhatsApp untuk kirim pesan tracking ke pelanggan.
                                </p>
                                <a href="{{ $shareUrl }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="mt-3 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-[#25D366] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1ebe5b] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                    Kirim update status ke pelanggan
                                </a>
                            </div>
                        @else
                            <a href="{{ $shareUrl }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100">
                                Kirim status tracking via WhatsApp
                            </a>
                        @endif
                    @elseif (! $order->customer?->phone)
                        <p class="mt-4 rounded-xl border border-dashed border-border bg-background px-3 py-3 text-xs leading-relaxed text-muted">
                            Nomor HP pelanggan belum diisi — pesan tracking WhatsApp tidak bisa dibuat.
                        </p>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</x-admin-layout>
