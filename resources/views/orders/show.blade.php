<x-app-layout>
    <x-slot name="header">
        <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Breadcrumb">
            <a href="{{ route('orders.index') }}" class="font-medium text-muted transition hover:text-primary">Pesanan</a>
            <span class="text-border" aria-hidden="true">/</span>
            <span class="font-medium text-foreground">{{ $order->order_number }}</span>
        </nav>
    </x-slot>

    <div
        x-data="paymentOverlay({
            payUrl: @js(route('orders.pay', $order)),
            statusUrl: @js(route('orders.payment.status', $order)),
            skipUrl: @js(route('orders.payment.skip', $order)),
            amountLabel: @js($order->formattedAmountDue()),
        })"
        class="contents"
    >
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Detail pesanan</p>
                <h1 class="mt-1 text-balance text-2xl font-bold tracking-tight text-foreground">{{ $order->order_number }}</h1>
                <p class="mt-1 text-sm text-muted">Dibuat {{ $order->created_at->translatedFormat('d F Y, H:i') }}</p>
            </div>
            <span data-status-badge data-order-id="{{ $order->id }}" class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ $order->status->badgeClass() }}">
                {{ $order->status->label() }}
            </span>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div id="order-tracking-wrap">
                    <x-order-tracking :order="$order" />
                </div>

                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">Item</h2>
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
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Catatan</p>
                            <p class="mt-1 text-sm leading-relaxed text-foreground">{{ $order->customer_note }}</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-base font-semibold text-foreground">Penerimaan</h2>
                        <span class="rounded-lg px-3 py-1.5 text-sm font-semibold {{ $order->fulfillment_type instanceof \App\Enums\FulfillmentType ? $order->fulfillment_type->badgeClass() : ($order->fulfillment_type === 'delivery' ? 'bg-sky-50 text-sky-700' : 'bg-emerald-50 text-emerald-700') }}">
                            {{ $order->fulfillment_type instanceof \App\Enums\FulfillmentType ? $order->fulfillment_type->label() : ($order->fulfillment_type === 'delivery' ? 'Delivery' : 'Ambil di tempat') }}
                        </span>
                    </div>
                    @if ($order->isDelivery())
                        @if ($order->delivery_mode)
                            <p class="mt-3 text-sm text-muted">
                                Mode: {{ $order->delivery_mode instanceof \App\Enums\DeliveryMode ? $order->delivery_mode->label() : $order->delivery_mode }}
                            </p>
                        @endif
                        @if ($order->delivery_address)
                            <p class="mt-2 text-sm leading-relaxed text-foreground">{{ $order->delivery_address }}</p>
                        @endif
                        @if ($order->delivery_distance_km !== null)
                            <p class="mt-2 text-xs text-muted tabular-nums">
                                Jarak {{ number_format((float) $order->delivery_distance_km, 1, ',', '.') }} km · Ongkir {{ $order->formattedDeliveryFee() }}
                            </p>
                        @endif
                    @else
                        <p class="mt-3 text-sm text-muted">Ambil sendiri di toko sesuai jadwal di bawah.</p>
                    @endif
                </div>

                @if ($order->booking)
                    <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                        <h2 class="text-base font-semibold text-foreground">
                            {{ $order->isDelivery() && $order->delivery_mode === \App\Enums\DeliveryMode::Scheduled
                                ? 'Jadwal kirim'
                                : ($order->isDelivery() ? 'Info delivery' : 'Jadwal') }}
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
                            <p class="mt-3 text-sm text-muted">Dikirim segera setelah pesanan selesai diproses.</p>
                        @else
                            <p class="mt-3 text-sm text-muted">Tanpa jadwal tetap.</p>
                        @endif
                    </div>
                @endif

                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">File pekerjaan</h2>
                    <p class="mt-1 text-sm text-muted">Unggah dokumen yang perlu dicetak atau diproses.</p>

                    @if ($order->files->isEmpty())
                        <p class="mt-4 rounded-xl border border-dashed border-border bg-background px-4 py-6 text-center text-sm text-muted">
                            Belum ada file diunggah.
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

                    @if (! auth()->user()->isAdmin() && in_array($order->status->value, ['PENDING_PAYMENT', 'PAYMENT_FAILED', 'PAID', 'PROCESSING'], true))
                        <form method="POST" action="{{ route('orders.files.store', $order) }}" enctype="multipart/form-data" class="mt-4 border-t border-border pt-4">
                            @csrf
                            <x-input-label for="order-files" value="Tambah file" />
                            <input
                                id="order-files"
                                name="files[]"
                                type="file"
                                multiple
                                required
                                accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.txt,.zip"
                                class="mt-1 block w-full text-sm text-foreground file:mr-3 file:rounded-lg file:border-0 file:bg-primary-soft file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary hover:file:bg-primary/10"
                            >
                            <p class="mt-1 text-xs leading-relaxed text-muted">
                                Maksimal 5 file per unggahan, tiap file maks 5 MB. PDF, JPG, PNG, WEBP, DOC, DOCX, TXT, ZIP.
                            </p>
                            <x-input-error :messages="$errors->get('files')" class="mt-2" />
                            <x-input-error :messages="$errors->get('files.0')" class="mt-2" />
                            <x-primary-button class="mt-3">Unggah file</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm lg:sticky lg:top-24">
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
                                <p class="mt-1 text-sm font-bold tabular-nums text-foreground">
                                    Tagihan: {{ $order->formattedAmountDue() }}
                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-muted">
                                    Sisa {{ $order->formattedRemaining() }} dibayar saat ambil di tempat.
                                </p>
                            </div>
                        @elseif (in_array($order->status->value, ['PENDING_PAYMENT', 'PAYMENT_FAILED'], true))
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-muted">Tagihan</dt>
                                <dd class="font-bold tabular-nums text-foreground">{{ $order->formattedAmountDue() }}</dd>
                            </div>
                        @endif

                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Pembayaran</dt>
                            <dd data-order-payment-label class="font-medium text-foreground">{{ $order->payment_status->label() }}</dd>
                        </div>
                    </dl>

                    @if (
                        ! auth()->user()->isAdmin()
                        && in_array($order->status->value, ['PENDING_PAYMENT', 'PAYMENT_FAILED'], true)
                        && $order->payment_status->value !== 'PAID'
                    )
                        <div id="payment-overlay-root">
                            <button
                                type="button"
                                id="pay-now"
                                data-order="{{ $order->id }}"
                                data-pay-url="{{ route('orders.pay', $order) }}"
                                data-amount-label="{{ $order->formattedAmountDue() }}"
                                x-on:click="openOverlay()"
                                class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                            >
                                Bayar sekarang — {{ $order->formattedAmountDue() }}
                            </button>
                            <p id="pay-status" class="mt-2 text-center text-xs leading-relaxed text-muted" x-text="statusMessage" x-show="statusMessage" hidden></p>
                            <p class="mt-2 text-center text-xs leading-relaxed text-muted">
                                Bayar via <strong class="text-foreground">QRIS</strong> / transfer bank.
                            </p>
                        </div>
                    @endif

                    @if ($order->status->canBeCancelled() && ! auth()->user()->isAdmin())
                        <button
                            type="button"
                            data-cancel-button
                            class="mt-4 inline-flex min-h-12 w-full items-center justify-center rounded-lg border border-red-200 bg-surface px-5 text-sm font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-cancel-order')"
                        >
                            Batalkan pesanan
                        </button>

                        <x-modal name="confirm-cancel-order" focusable>
                            <form method="POST" action="{{ route('orders.cancel', $order) }}" class="p-6">
                                @csrf
                                <h2 class="text-lg font-medium text-foreground">Batalkan pesanan {{ $order->order_number }}?</h2>
                                <p class="mt-1 text-sm leading-relaxed text-muted">
                                    Pesanan yang dibatalkan tidak bisa diproses lagi. Pastikan Anda yakin sebelum melanjutkan.
                                </p>

                                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                                    <x-secondary-button x-on:click="$dispatch('close')">
                                        Kembali
                                    </x-secondary-button>
                                    <x-danger-button>
                                        Ya, batalkan pesanan
                                    </x-danger-button>
                                </div>
                            </form>
                        </x-modal>
                    @endif

                    @php
                        $adminWa = \App\Lib\WhatsApp::url(
                            'Halo Admin, saya ingin menanyakan pesanan '.$order->order_number.' (status: '.$order->status->label().').'
                        );
                    @endphp
                    @if ($adminWa)
                        <a href="{{ $adminWa }}"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="mt-4 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                            Hubungi admin
                        </a>
                    @endif

                    <p class="mt-4 border-t border-border pt-4 text-xs leading-relaxed text-muted">
                        Pembayaran diproses Midtrans (sandbox) lewat overlay custom.
                    </p>
                </div>
            </aside>
        </div>
    </div>

    {{-- Overlay pembayaran di root layout — di atas navbar (bukan di dalam sticky aside) --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[100] flex items-end justify-center sm:items-center"
        role="dialog"
        aria-modal="true"
        aria-labelledby="payment-overlay-title"
        x-cloak
    >
        <div class="absolute inset-0 bg-foreground/50" x-on:click="skipPayment()"></div>

        <div class="relative w-full max-w-md rounded-t-2xl border border-border bg-surface shadow-2xl sm:rounded-2xl">
            <div class="flex items-start justify-between gap-3 border-b border-border bg-foreground px-5 py-4 text-white sm:rounded-t-2xl">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-200">Pembayaran</p>
                    <h2 id="payment-overlay-title" class="mt-0.5 text-lg font-bold tracking-tight">
                        {{ config('app.name') }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-200">
                        Tagihan <strong class="text-white tabular-nums">{{ $order->formattedAmountDue() }}</strong>
                    </p>
                </div>
                <button
                    type="button"
                    x-on:click="skipPayment()"
                    x-bind:disabled="skipping"
                    aria-label="Lewati pembayaran"
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-slate-200 transition hover:bg-white/10 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-300 disabled:opacity-50"
                >
                    <span aria-hidden="true" class="text-lg leading-none">✕</span>
                </button>
            </div>

            <div class="px-5 py-5">
                <div class="grid grid-cols-2 gap-2 rounded-lg bg-background p-1" role="tablist" aria-label="Metode pembayaran">
                    <button
                        type="button"
                        role="tab"
                        x-on:click="selectMethod('qris')"
                        x-bind:aria-selected="method === 'qris'"
                        x-bind:class="method === 'qris' ? 'bg-primary text-white shadow-sm' : 'text-muted hover:text-foreground'"
                        class="min-h-11 rounded-md px-3 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                        QRIS
                    </button>
                    <button
                        type="button"
                        role="tab"
                        x-on:click="selectMethod('bank_transfer')"
                        x-bind:aria-selected="method === 'bank_transfer'"
                        x-bind:class="method === 'bank_transfer' ? 'bg-primary text-white shadow-sm' : 'text-muted hover:text-foreground'"
                        class="min-h-11 rounded-md px-3 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    >
                        Transfer bank
                    </button>
                </div>

                <div x-show="loading" class="mt-5 rounded-xl border border-border bg-background px-4 py-6 text-center text-sm text-muted" x-cloak>
                    Menyiapkan pembayaran...
                </div>

                <div x-show="error" class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" x-cloak x-text="error"></div>

                {{-- QRIS --}}
                <div x-show="!loading && !error && method === 'qris' && charge" class="mt-5 text-center" x-cloak>
                    <p class="text-sm font-medium text-foreground">Scan QRIS dengan e-wallet / m-banking</p>
                    <p class="mt-1 text-xs text-muted">GoPay, OVO, DANA, ShopeePay, LinkAja, dan bank digital</p>
                    <div class="mx-auto mt-4 w-fit rounded-xl border border-border bg-white p-3 shadow-sm">
                        <img
                            x-bind:src="qrImageSrc()"
                            x-bind:alt="'QRIS ' + (charge ? charge.order_number : '')"
                            width="220"
                            height="220"
                            class="h-52 w-52 sm:h-56 sm:w-56"
                            x-cloak
                        />
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-muted">
                        Bayar sesuai tagihan.
                    </p>
                </div>

                {{-- Transfer bank --}}
                <div x-show="!loading && !error && method === 'bank_transfer' && charge" class="mt-5" x-cloak>
                    <p class="text-sm font-medium text-foreground">Pilih bank tujuan VA</p>
                    <div class="mt-2 grid grid-cols-4 gap-2">
                        <template x-for="b in ['bca', 'bni', 'bri', 'mandiri']" :key="b">
                            <button
                                type="button"
                                x-on:click="selectBank(b)"
                                x-bind:class="bank === b ? 'border-primary bg-primary-soft text-primary' : 'border-border bg-surface text-muted hover:border-primary/40'"
                                class="min-h-11 rounded-lg border px-2 text-xs font-bold uppercase transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                                x-text="b"
                            ></button>
                        </template>
                    </div>

                    <div class="mt-4 rounded-xl border border-border bg-background p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Nomor Virtual Account</p>
                        <p class="mt-1 break-all font-mono text-base font-bold tabular-nums text-foreground" x-text="charge ? charge.va_number : '—'"></p>
                        <button
                            type="button"
                            x-on:click="copyVa()"
                            x-bind:disabled="!charge || !charge.va_number"
                            class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-border bg-surface px-4 text-sm font-semibold text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary focus:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-50"
                        >
                            <span x-show="!copied">Salin nomor VA</span>
                            <span x-show="copied" x-cloak>Tersalin ✓</span>
                        </button>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-muted">
                        Bayar sesuai tagihan.
                    </p>
                </div>

                <div x-show="statusMessage && !loading" class="mt-4 rounded-lg bg-primary-soft px-3 py-2 text-center text-xs font-medium text-primary-dark" x-cloak x-text="statusMessage"></div>

                <button
                    type="button"
                    x-on:click="skipPayment()"
                    x-bind:disabled="skipping"
                    class="mt-5 inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-border bg-surface px-4 text-sm font-semibold text-foreground transition hover:bg-background focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-50"
                >
                    <span x-show="!skipping">Lewati pembayaran</span>
                    <span x-show="skipping" x-cloak>Memproses...</span>
                </button>
            </div>
        </div>
    </div>
</x-app-layout>
