<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Fotocopy Adhijaya') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen w-full max-w-full bg-background text-foreground font-sans antialiased">
        @include('layouts.navigation')
        <x-bottom-nav />
        <x-whatsapp-float />
        <x-back-to-top />

        @isset($header)
            <header class="border-b border-border bg-surface">
                <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="w-full max-w-full">
            @if (session('status'))
                <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6 lg:px-8">
                    <div class="rounded-lg border border-primary-line bg-primary-soft px-4 py-3 text-sm font-medium text-primary-dark" role="status">
                        {{ session('status') }}
                    </div>
                </div>
            @endif
            {{ $slot }}
        </main>

        <footer class="mt-16 w-full max-w-full bg-linear-to-b from-[#0f172a] to-[#1a2740] pb-[calc(5.5rem+env(safe-area-inset-bottom))] text-white sm:pb-0">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3 lg:px-8">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-white ring-1 ring-white/25">
                            <x-application-logo class="h-5 w-5 text-white" />
                        </span>
                        <span class="text-sm font-bold tracking-tight text-white">{{ config('app.name') }}</span>
                    </div>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-slate-200/90">
                            {{ \App\Models\BusinessSetting::current()->tagline
                                ?: 'Jasa fotokopi dan percetakan untuk kebutuhan sekolah, kantor, dan keperluan umum.' }}
                        </p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white">Navigasi</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="text-slate-200/90 transition hover:text-white">Beranda</a></li>
                        <li><a href="{{ route('services.index') }}" class="text-slate-200/90 transition hover:text-white">Layanan</a></li>
                        <li><a href="{{ route('kontak') }}" class="text-slate-200/90 transition hover:text-white">Kontak</a></li>
                        <li><a href="{{ route('cart.index') }}" class="text-slate-200/90 transition hover:text-white">Keranjang</a></li>
                        <li><a href="{{ route('orders.index') }}" class="text-slate-200/90 transition hover:text-white">Pesanan</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white">Catatan</p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-200/90">
                        Harga di situs ini adalah harga contoh dan dapat diubah oleh admin.
                        Situs contoh untuk keperluan akademik.
                    </p>
                </div>
            </div>
            <div class="border-t border-white/15">
                <div class="mx-auto max-w-6xl px-4 py-4 text-xs text-slate-300 sm:px-6 lg:px-8">
                    &copy; {{ date('Y') }} {{ config('app.name') }}.
                </div>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
