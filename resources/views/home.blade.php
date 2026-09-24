<x-app-layout>
    @php
        $heroSlides = [
            ['src' => 'images/hero/hero-1.jpg', 'height' => 1200, 'priority' => true],
            ['src' => 'images/hero/hero-2.jpg', 'height' => 1200, 'priority' => false],
            ['src' => 'images/hero/hero-3.jpg', 'height' => 1067, 'priority' => false],
        ];
    @endphp

    <x-hero-carousel id="hero" class="relative flex min-h-[36rem] flex-col overflow-hidden border-b border-border bg-foreground text-white sm:min-h-[42rem] lg:min-h-screen">
        <div class="absolute inset-0" aria-hidden="true">
            @foreach ($heroSlides as $i => $slide)
                <img
                    src="{{ asset($slide['src']) }}"
                    alt=""
                    width="1600"
                    height="{{ $slide['height'] }}"
                    class="absolute inset-0 h-full w-full object-cover transition-opacity duration-700"
                    :class="index === {{ $i }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                    @if ($slide['priority']) fetchpriority="high" @endif
                >
            @endforeach
            <div class="absolute inset-0 bg-linear-to-r from-[#0f172a]/95 via-[#1e3a5f]/85 to-[#2563eb]/45"></div>
            <div class="absolute inset-0 bg-linear-to-t from-[#0f172a]/70 via-transparent to-[#0f172a]/30"></div>
        </div>

        <div class="relative z-10 mx-auto flex w-full max-w-6xl flex-1 flex-col justify-center px-4 pb-4 pt-20 sm:px-6 sm:pb-8 sm:pt-24 lg:px-8 lg:pb-10 lg:pt-28">
            <div class="max-w-2xl">
                <h1 class="mt-4 text-balance text-3xl font-bold leading-[1.15] tracking-tight text-white sm:mt-5 sm:text-5xl lg:text-6xl">
                    Pesan layanan fotokopi tanpa chat berulang
                </h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-100/90 sm:mt-5 sm:text-lg">
                    Pilih layanan, isi detail pekerjaan, unggah file, lalu checkout. Status pesanan bisa dipantau langsung dari akun Anda.
                </p>

                <div class="mt-6 flex flex-wrap gap-3 sm:mt-8">
                    <a href="{{ route('services.index') }}" class="inline-flex min-h-12 items-center rounded-lg bg-primary px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                        Lihat layanan
                    </a>
                    @auth
                        <a href="{{ route('services.index', ['category' => 'digital-print']) }}" class="inline-flex min-h-12 items-center rounded-lg border border-white/40 bg-white/10 px-6 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                            Mulai cetak
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center rounded-lg border border-white/40 bg-white/10 px-6 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                            Buat akun
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <button
            type="button"
            class="absolute left-3 top-1/2 z-20 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white ring-1 ring-white/30 backdrop-blur-sm transition hover:bg-white/25"
            @click="prev()"
            aria-label="Slide sebelumnya"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button
            type="button"
            class="absolute right-3 top-1/2 z-20 inline-flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white ring-1 ring-white/30 backdrop-blur-sm transition hover:bg-white/25"
            @click="next()"
            aria-label="Slide berikutnya"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <div class="relative z-20 flex items-center justify-center gap-2 pb-2 sm:pb-3">
            <template x-for="i in [0, 1, 2]" :key="i">
                <button
                    type="button"
                    class="h-2.5 rounded-full transition-all"
                    :class="index === i ? 'w-7 bg-white' : 'w-2.5 bg-white/55'"
                    @click="go(i)"
                    :aria-label="'Buka slide ' + (i + 1)"
                    :aria-current="index === i"
                ></button>
            </template>
        </div>

        <div class="relative z-20">
            <x-business-stats
                :service-count="$serviceCount"
                :transaction-count="$transactionCount"
                class="mx-auto max-w-6xl"
            />
        </div>
    </x-hero-carousel>

    <section id="layanan" class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
        <x-section-header
            eyebrow="Katalog"
            title="Layanan"
            description="Harga di bawah ini adalah harga contoh dan dapat diubah oleh admin."
            :href="route('services.index')"
            link-label="Lihat semua layanan"
        />

        @if ($categories->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <a href="{{ route('services.index', ['category' => $category->slug]) }}" class="inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-3.5 text-[15px] font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($services->isEmpty())
            <div class="mt-8 rounded-2xl border border-dashed border-border bg-surface p-10 text-center">
                <p class="font-medium text-foreground">Belum ada layanan yang ditampilkan.</p>
                <p class="mt-1 text-sm text-muted">Katalog layanan sedang disiapkan.</p>
            </div>
        @else
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <x-service-card :service="$service" />
                @endforeach
            </div>
        @endif
    </section>

    @if ($digitalPrintServices->isNotEmpty())
        <section id="digital-print" class="border-t border-border bg-background">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
                <x-section-header
                    eyebrow="Digital print"
                    title="Print A0–A5 & scan copy"
                    description="Print ukuran besar hingga A5 dan scan copy untuk kebutuhan kantor, sekolah, dan acara."
                    :href="route('services.index', ['category' => 'digital-print'])"
                    link-label="Semua digital print"
                />
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($digitalPrintServices as $service)
                        <x-service-card :service="$service" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($atkServices->isNotEmpty())
        <section id="atk" class="border-t border-border bg-surface">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
                <x-section-header
                    eyebrow="Alat tulis"
                    title="Pesan ATK"
                    description="Pulpen, buku, map, dan kebutuhan tulis lainnya — bisa sekalian dengan pesanan print."
                    :href="route('services.index', ['type' => 'jual'])"
                    link-label="Semua ATK"
                />
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($atkServices as $service)
                        <x-service-card :service="$service" cta="Pesan" surface="bg-background" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section id="kontak" class="border-t border-border bg-background">
        <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-white/10 bg-linear-to-br from-[#0f172a] via-[#1e3a5f] to-[#2563eb] p-6 shadow-xl sm:p-10">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between sm:gap-10">
                    <div class="max-w-xl">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-200">Bantuan</p>
                        <h2 class="mt-2 text-balance text-2xl font-bold tracking-tight text-white sm:text-3xl">Butuh penjelasan?</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-200/90 sm:text-base">
                            Nomor WhatsApp dan jam operasional tersedia di halaman Kontak. Admin siap membantu kebutuhan khusus.
                        </p>
                    </div>
                    <div class="flex w-full flex-col gap-3 sm:w-auto sm:min-w-[15rem] sm:shrink-0">
                        <a
                            href="{{ route('kontak') }}"
                            class="inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-white px-5 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f172a] active:scale-[0.98] active:bg-slate-200 sm:w-auto"
                        >
                            Buka halaman kontak
                        </a>
                        @if ($whatsappUrl)
                            <a
                                href="{{ $whatsappUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex min-h-12 w-full items-center justify-center rounded-lg border border-white/40 bg-white/10 px-5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#0f172a] active:scale-[0.98] active:bg-white/25 sm:w-auto"
                            >
                                Hubungi admin via WhatsApp
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
