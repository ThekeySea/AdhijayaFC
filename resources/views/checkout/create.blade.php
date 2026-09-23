<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Checkout</p>
                <h1 class="mt-1 text-balance text-2xl font-bold tracking-tight text-foreground">Selesaikan pesanan</h1>
            </div>
            <a href="{{ route('cart.index') }}" class="text-sm font-medium text-muted transition hover:text-primary">
                Kembali ke keranjang
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3">
            @csrf

            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">Item pesanan</h2>
                    <ul class="mt-4 divide-y divide-border">
                        @foreach ($lines as $line)
                            <li class="flex flex-wrap items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-foreground">{{ $line['service']->name }}</p>
                                    <p class="mt-0.5 text-sm text-muted">
                                        {{ \App\Support\Cart::formatAmount((float) ($line['unit_price'] ?? $line['price'])) }} × {{ $line['quantity'] }} {{ $line['service']->unit }}
                                    </p>
                                    @if (! empty($line['options']) && $line['options']->isNotEmpty())
                                        <p class="mt-1 text-sm text-primary">
                                            Opsi: {{ $line['options']->map(fn ($option) => $option->name)->implode(', ') }}
                                        </p>
                                    @endif
                                    @if ($line['detail'] !== '')
                                        <p class="mt-1 text-sm text-foreground">
                                            <span class="font-medium text-muted">Detail:</span> {{ $line['detail'] }}
                                        </p>
                                    @endif
                                </div>
                                <p class="shrink-0 text-sm font-bold tabular-nums text-foreground">
                                    {{ \App\Support\Cart::formatAmount((float) $line['subtotal']) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">Kontak & jadwal</h2>
                    <p class="mt-1 text-sm text-muted">Nomor WhatsApp dipakai admin untuk update status tracking pesanan.</p>

                    <div class="mt-4">
                        <x-input-label for="phone" value="Nomor WhatsApp" />
                        <x-text-input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone', auth()->user()?->phone) }}"
                            placeholder="628xxxxxxxxxx"
                            autocomplete="tel"
                            class="mt-1 w-full"
                            required
                        />
                        <p class="mt-1 text-xs leading-relaxed text-muted">
                            Format internasional tanpa +, spasi, atau tanda baca. Contoh: 6281234567890
                        </p>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid gap-4 border-t border-border pt-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="pickup_date" value="Tanggal" />
                            <x-text-input
                                id="pickup_date"
                                name="pickup_date"
                                type="date"
                                min="{{ now()->toDateString() }}"
                                value="{{ old('pickup_date', now()->addDay()->toDateString()) }}"
                                class="mt-1 w-full"
                                required
                            />
                            <x-input-error :messages="$errors->get('pickup_date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="time_slot" value="Slot waktu" />
                            <select
                                id="time_slot"
                                name="time_slot"
                                class="mt-1 w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                required
                            >
                                @foreach ($timeSlots as $slot)
                                    <option value="{{ $slot }}" @selected(old('time_slot') === $slot)>{{ $slot }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('time_slot')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="customer_note" value="Catatan pesanan (opsional)" />
                        <textarea
                            id="customer_note"
                            name="customer_note"
                            rows="3"
                            maxlength="1000"
                            placeholder="misal: tolong rangkum hasil fotokopi per map"
                            class="mt-1 w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        >{{ old('customer_note') }}</textarea>
                        <x-input-error :messages="$errors->get('customer_note')" class="mt-2" />
                    </div>

                    <div class="mt-4 border-t border-border pt-4">
                        <x-input-label for="files" value="Upload file (opsional)" />
                        <input
                            id="files"
                            name="files[]"
                            type="file"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.txt,.zip"
                            class="mt-1 block w-full text-sm text-foreground file:mr-3 file:rounded-lg file:border-0 file:bg-primary-soft file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary hover:file:bg-primary/10"
                        >
                        <p class="mt-1 text-xs leading-relaxed text-muted">
                            Maksimal 5 file, tiap file maks 5 MB. Format: PDF, JPG, PNG, WEBP, DOC, DOCX, TXT, ZIP.
                        </p>
                        <x-input-error :messages="$errors->get('files')" class="mt-2" />
                        <x-input-error :messages="$errors->get('files.0')" class="mt-2" />
                    </div>
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm lg:sticky lg:top-24">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Ringkasan</p>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Jumlah item</dt>
                            <dd class="font-medium tabular-nums text-foreground">{{ $lines->sum('quantity') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Subtotal</dt>
                            <dd class="font-medium tabular-nums text-foreground">{{ \App\Support\Cart::formatAmount($subtotal) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-border pt-3">
                            <dt class="font-semibold text-foreground">Total</dt>
                            <dd class="text-lg font-bold tabular-nums text-foreground">{{ \App\Support\Cart::formatAmount($subtotal) }}</dd>
                        </div>

                        @if ($isDownPayment)
                            <div class="rounded-xl border border-primary-line bg-primary-soft p-3">
                                <p class="text-xs font-semibold text-primary">Uang muka (DP) 50%</p>
                                <p class="mt-1 text-sm font-bold tabular-nums text-foreground">
                                    Bayar sekarang: {{ \App\Support\Cart::formatAmount($amountDue) }}
                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-muted">
                                    Sisa {{ \App\Support\Cart::formatAmount($remaining) }} dibayar saat ambil di tempat.
                                </p>
                            </div>
                        @else
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-muted">Bayar sekarang</dt>
                                <dd class="font-bold tabular-nums text-foreground">{{ \App\Support\Cart::formatAmount($amountDue) }}</dd>
                            </div>
                        @endif
                    </dl>

                    <x-primary-button class="mt-6 w-full">Buat pesanan</x-primary-button>

                    <p class="mt-3 text-center text-xs leading-relaxed text-muted">
                        Bayar via QRIS atau transfer bank setelah pesanan dibuat. Harga dicek ulang oleh server.
                    </p>
                </div>
            </aside>
        </form>
    </div>
</x-app-layout>
