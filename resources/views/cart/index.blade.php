<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Keranjang</p>
                <h1 class="mt-1 text-balance text-2xl font-bold tracking-tight text-foreground">Keranjang belanja</h1>
            </div>
            <a href="{{ route('services.index') }}" class="text-sm font-medium text-muted transition hover:text-primary">
                Tambah layanan lain
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-6 pb-[calc(5.5rem+env(safe-area-inset-bottom))] sm:px-6 sm:py-8 sm:pb-8 lg:px-8">
        @if ($lines->isEmpty())
            <div class="rounded-2xl border border-border bg-surface p-10 text-center">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-soft text-primary">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-foreground">Keranjang masih kosong</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-muted">
                    Pilih layanan yang kamu butuhkan, lalu tambahkan ke keranjang sebelum lanjut checkout.
                </p>
                <a href="{{ route('services.index') }}" class="mt-6 inline-flex min-h-12 items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                    Lihat layanan
                </a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    @foreach ($lines as $line)
                        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-6" x-data>
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('services.show', $line['service']) }}" class="break-words text-base font-semibold text-foreground transition hover:text-primary">
                                        {{ $line['service']->name }}
                                    </a>
                                    <p class="mt-1 text-sm text-muted">
                                        @if ((float) ($line['option_surcharge'] ?? 0) > 0 || (float) ($line['unit_price'] ?? $line['price']) !== (float) $line['price'])
                                            {{ \App\Support\Cart::formatAmount((float) $line['unit_price']) }} per {{ $line['service']->unit }}
                                            <span class="text-xs">(base {{ \App\Support\Cart::formatAmount((float) $line['price']) }})</span>
                                        @else
                                            {{ \App\Support\Cart::formatAmount($line['price']) }} per {{ $line['service']->unit }}
                                        @endif
                                    </p>
                                    @if (! empty($line['options']) && $line['options']->isNotEmpty())
                                        <ul class="mt-2 space-y-1">
                                            @foreach ($line['options'] as $option)
                                                <li class="rounded-lg bg-primary-soft px-3 py-1.5 text-xs font-medium text-primary">
                                                    Opsi: {{ $option->name }} +{{ $option->formattedPrice() }}{{ $option->pricing === 'per_unit' ? '/'.$line['service']->unit : ' / pesanan' }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if ($line['detail'] !== '')
                                        <p class="mt-2 break-words rounded-lg bg-background px-3 py-2 text-sm text-foreground">
                                            <span class="font-medium text-muted">Pesan:</span> {{ $line['detail'] }}
                                        </p>
                                    @endif
                                    @if (! empty($line['files']) && count($line['files']) > 0)
                                        <ul class="mt-2 space-y-1">
                                            @foreach ($line['files'] as $file)
                                                <li class="break-words rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                                                    File: {{ $file['name'] ?? basename($file['path'] ?? '') }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <p class="shrink-0 text-base font-bold tabular-nums text-foreground">
                                    {{ \App\Support\Cart::formatAmount((float) $line['subtotal']) }}
                                </p>
                            </div>

                            <div class="mt-4 flex flex-wrap items-end gap-3 border-t border-border pt-4">
                                <form method="POST" action="{{ route('cart.update', $line['service']) }}" class="flex w-full flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="quantity-{{ $line['service']->id }}" class="block text-xs font-semibold uppercase tracking-wide text-muted">Jumlah ({{ $line['service']->unit }})</label>
                                        <input
                                            id="quantity-{{ $line['service']->id }}"
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            max="9999"
                                            value="{{ $line['quantity'] }}"
                                            class="mt-1 w-24 rounded-lg border border-border bg-surface px-3 py-2 text-sm text-foreground transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        >
                                    </div>
                                    <div class="min-w-0 basis-full sm:basis-auto sm:min-w-40 sm:flex-1">
                                        <label for="detail-{{ $line['service']->id }}" class="block text-xs font-semibold uppercase tracking-wide text-muted">Pesan</label>
                                        <input
                                            id="detail-{{ $line['service']->id }}"
                                            type="text"
                                            name="detail"
                                            maxlength="500"
                                            value="{{ $line['detail'] }}"
                                            placeholder="misal: A4, 2 sisi, jilid kiri"
                                            class="mt-1 w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-foreground transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        >
                                    </div>
                                    <x-secondary-button type="submit" class="mb-0.5">Simpan</x-secondary-button>
                                </form>

                                @if ($line['service']->activeOptions->isNotEmpty())
                                    <form method="POST" action="{{ route('cart.update', $line['service']) }}" class="mt-3 w-full">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="quantity" value="{{ $line['quantity'] }}">
                                        <input type="hidden" name="detail" value="{{ $line['detail'] }}">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-muted">Opsi tambahan</p>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach ($line['service']->activeOptions as $option)
                                                <label class="inline-flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground transition hover:border-primary/40">
                                                    <input type="checkbox" name="options[]" value="{{ $option->id }}" class="rounded border-border text-primary focus:ring-primary"
                                                        @checked(in_array($option->id, $line['options']->pluck('id')->all()))>
                                                    <span>{{ $option->name }} <span class="text-xs text-muted">+{{ $option->formattedPrice() }}</span></span>
                                                </label>
                                            @endforeach
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                                                Simpan opsi
                                            </button>
                                        </div>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('cart.destroy', $line['service']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-medium text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                        Hapus
                                    </button>
                                </form>
                            </div>

                            @if ($errors->any() && $errors->first() !== '')
                                <x-input-error :messages="$errors->all()" class="mt-3" />
                            @endif
                        </div>
                    @endforeach
                </div>

                <aside class="lg:col-span-1">
                    <div class="rounded-2xl border border-border bg-surface p-4 shadow-sm sm:p-6 lg:sticky lg:top-24">
                        <p class="text-xs font-semibold uppercase tracking-wide text-primary">Ringkasan</p>

                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-muted">Jumlah item</dt>
                                <dd class="font-medium tabular-nums text-foreground">{{ $lines->sum('quantity') }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 border-t border-border pt-3">
                                <dt class="font-semibold text-foreground">Subtotal</dt>
                                <dd class="text-lg font-bold tabular-nums text-foreground">{{ \App\Support\Cart::formatAmount($subtotal) }}</dd>
                            </div>
                        </dl>

                        @auth
                            @if (auth()->user()->isAdmin())
                                <p class="mt-6 text-center text-xs text-muted">Akun admin tidak melakukan checkout.</p>
                            @else
                                <a href="{{ route('checkout.create') }}" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                    Lanjut checkout
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                Masuk untuk checkout
                            </a>
                            <p class="mt-3 text-center text-xs text-muted">
                                Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">Daftar</a>
                            </p>
                        @endauth

                        <p class="mt-4 border-t border-border pt-4 text-xs leading-relaxed text-muted">
                            Total dihitung ulang dari harga terbaru di sistem. Harga bersifat contoh dan dapat berubah.
                        </p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</x-app-layout>
