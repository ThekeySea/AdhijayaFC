<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Layanan</h1>
        <p class="mt-1.5 text-sm leading-relaxed text-slate-700 sm:text-base">Pilih layanan yang Anda butuhkan. Harga adalah harga contoh.</p>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-2" role="navigation" aria-label="Filter kategori">
            <a href="{{ route('services.index') }}"
               class="inline-flex min-h-11 items-center rounded-lg border px-3.5 text-[15px] font-medium transition {{ $activeCategory === '' && $activeType === '' ? 'border-primary/40 bg-primary-soft text-primary' : 'border-border bg-surface text-foreground hover:border-primary/40 hover:bg-primary-soft hover:text-primary' }}">
                Semua
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('services.index', ['category' => $category->slug]) }}"
                   class="inline-flex min-h-11 items-center rounded-lg border px-3.5 text-[15px] font-medium transition {{ $activeCategory === $category->slug ? 'border-primary/40 bg-primary-soft text-primary' : 'border-border bg-surface text-foreground hover:border-primary/40 hover:bg-primary-soft hover:text-primary' }}">
                    {{ $category->name }}
                </a>
            @endforeach
            <a href="{{ route('services.index', ['type' => 'jual']) }}"
               class="inline-flex min-h-11 items-center rounded-lg border px-3.5 text-[15px] font-medium transition {{ $activeType === 'jual' ? 'border-primary/40 bg-primary-soft text-primary' : 'border-border bg-surface text-foreground hover:border-primary/40 hover:bg-primary-soft hover:text-primary' }}">
                ATK
            </a>
        </div>

        @if ($services->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-border bg-surface p-10 text-center">
                <p class="font-medium text-foreground">Belum ada layanan tersedia{{ $activeCategory !== '' ? ' di kategori ini' : ($activeType === 'jual' ? ' untuk ATK' : '') }}.</p>
                <p class="mt-1 text-sm text-muted">Silakan pilih kategori lain atau kembali lagi nanti.</p>
                <a href="{{ route('home') }}" class="mt-5 inline-flex min-h-12 items-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                    Ke beranda
                </a>
            </div>
        @else
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-service-card :service="$service" :line-clamp="3" title-tag="h2">
                        @if ($service->activeOptions->isNotEmpty())
                            <p class="mt-3 text-xs font-medium text-primary">Tersedia opsi: {{ $service->activeOptions->pluck('name')->implode(', ') }}</p>
                        @endif
                    </x-service-card>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
