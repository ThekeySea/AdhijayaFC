<x-app-layout>
    <x-slot name="header">
        <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Breadcrumb">
            <a href="{{ route('services.index') }}" class="font-medium text-muted transition hover:text-primary">Layanan</a>
            <span class="text-border" aria-hidden="true">/</span>
            <span class="font-medium text-foreground">{{ $service->name }}</span>
        </nav>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-border bg-surface p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($service->category)
                            <span class="rounded-lg bg-background px-2.5 py-1 text-xs font-medium text-muted">{{ $service->category }}</span>
                        @endif
                        <span class="rounded-lg bg-primary-soft px-2.5 py-1 text-xs font-semibold text-primary">Harga contoh</span>
                        @if ($service->is_active)
                            <span class="rounded-lg border border-primary-line px-2.5 py-1 text-xs font-semibold text-primary">Tersedia</span>
                        @endif
                    </div>

                    <h1 class="mt-4 text-balance text-2xl font-bold tracking-tight text-foreground sm:text-3xl">{{ $service->name }}</h1>

                    @if ($service->description)
                        <p class="mt-4 text-base leading-relaxed text-muted">{{ $service->description }}</p>
                    @endif

                    <dl class="mt-6 grid gap-4 border-t border-border pt-6 sm:grid-cols-2">
                        <div class="rounded-xl bg-background p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Satuan</dt>
                            <dd class="mt-1 text-sm font-semibold text-foreground">per {{ $service->unit }}</dd>
                        </div>
                        <div class="rounded-xl bg-background p-4">
                            <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Status</dt>
                            <dd class="mt-1 text-sm font-semibold {{ $service->is_active ? 'text-primary' : 'text-muted' }}">
                                {{ $service->is_active ? 'Tersedia' : 'Tidak tersedia' }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm lg:sticky lg:top-24">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Harga contoh</p>
                    <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-foreground">{{ $service->formattedPrice() }}</p>
                    <p class="mt-1 text-sm text-muted">per {{ $service->unit }}</p>

                    @guest
                        <form method="POST" action="{{ route('cart.store') }}" class="mt-6">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <div>
                                <x-input-label for="quantity" value="Jumlah ({{ $service->unit }})" />
                                <x-text-input id="quantity" name="quantity" type="number" min="1" max="9999" value="1" class="mt-1 w-28" />
                                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                            </div>
                            <div class="mt-3">
                                <x-input-label for="detail" value="Detail pengerjaan (opsional)" />
                                <x-text-input id="detail" name="detail" type="text" maxlength="500" placeholder="misal: A4, 2 sisi, jilid kiri" class="mt-1 w-full" />
                                <x-input-error :messages="$errors->get('detail')" class="mt-2" />
                            </div>
                            <x-primary-button class="mt-4 w-full">Tambah ke keranjang</x-primary-button>
                        </form>
                        <p class="mt-3 text-center text-xs leading-relaxed text-muted">
                            Bisa dulu ditambahkan. <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Masuk</a> saat checkout untuk menyelesaikan pesanan.
                        </p>
                    @else
                        @if (auth()->user()->isAdmin())
                            <a href="{{ route('admin.services.edit', $service) }}" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                                Ubah layanan
                            </a>
                        @else
                            <form method="POST" action="{{ route('cart.store') }}" class="mt-6">
                                @csrf
                                <input type="hidden" name="service_id" value="{{ $service->id }}">
                                <div>
                                    <x-input-label for="quantity" value="Jumlah ({{ $service->unit }})" />
                                    <x-text-input id="quantity" name="quantity" type="number" min="1" max="9999" value="1" class="mt-1 w-28" />
                                    <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                                </div>
                                <div class="mt-3">
                                    <x-input-label for="detail" value="Detail pengerjaan (opsional)" />
                                    <x-text-input id="detail" name="detail" type="text" maxlength="500" placeholder="misal: A4, 2 sisi, jilid kiri" class="mt-1 w-full" />
                                    <x-input-error :messages="$errors->get('detail')" class="mt-2" />
                                </div>
                                <x-primary-button class="mt-4 w-full">Tambah ke keranjang</x-primary-button>
                            </form>
                            <a href="{{ route('cart.index') }}" class="mt-3 inline-flex w-full items-center justify-center text-sm font-medium text-muted transition hover:text-primary">
                                Lihat keranjang
                            </a>
                        @endif
                    @endauth

                    <p class="mt-4 border-t border-border pt-4 text-xs leading-relaxed text-muted">
                        Harga bersifat contoh dan dapat berubah. Hubungi admin untuk kebutuhan khusus.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</x-app-layout>
