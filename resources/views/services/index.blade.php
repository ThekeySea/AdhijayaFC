<x-app-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Layanan</h1>
        <p class="mt-1.5 text-sm text-muted">Pilih layanan yang Anda butuhkan. Harga adalah harga contoh.</p>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @if ($services->isEmpty())
            <div class="rounded-2xl border border-dashed border-border bg-surface p-10 text-center">
                <p class="font-medium text-foreground">Belum ada layanan tersedia.</p>
                <p class="mt-1 text-sm text-muted">Silakan kembali lagi nanti.</p>
                <a href="{{ route('home') }}" class="mt-5 inline-flex min-h-12 items-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                    Ke beranda
                </a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <a href="{{ route('services.show', $service) }}" class="group flex flex-col rounded-2xl border border-border bg-surface p-5 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex rounded-lg bg-background px-2.5 py-1 text-xs font-medium text-muted transition group-hover:bg-primary-soft group-hover:text-primary">
                                {{ $service->category ?? 'Layanan' }}
                            </span>
                            <span class="text-[11px] font-semibold uppercase tracking-wide text-primary">Harga contoh</span>
                        </div>
                        <h2 class="mt-4 text-base font-semibold text-foreground transition group-hover:text-primary">
                            {{ $service->name }}
                        </h2>
                        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-muted">{{ $service->description }}</p>
                        <div class="mt-5 flex items-center justify-between border-t border-border pt-4">
                            <span class="text-sm font-bold tabular-nums text-foreground">{{ $service->formattedPrice() }}<span class="font-medium text-muted">/{{ $service->unit }}</span></span>
                            <span class="text-sm font-semibold text-primary">Lihat detail</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
