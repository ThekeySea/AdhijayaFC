<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Fotocopy Adhijaya') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-background text-foreground font-sans antialiased">
        @include('layouts.navigation')

        @isset($header)
            <header class="border-b border-border bg-surface">
                <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>
            @if (session('status'))
                <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6 lg:px-8">
                    <div class="rounded-lg border border-primary-line bg-primary-soft px-4 py-3 text-sm font-medium text-primary-dark" role="status">
                        {{ session('status') }}
                    </div>
                </div>
            @endif
            {{ $slot }}
        </main>

        <footer class="mt-16 border-t border-border bg-surface">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3 lg:px-8">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-white">
                            <x-application-logo class="h-5 w-5" />
                        </span>
                        <span class="text-sm font-bold tracking-tight text-foreground">{{ config('app.name') }}</span>
                    </div>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-muted">
                        Jasa fotokopi dan percetakan untuk kebutuhan sekolah, kantor, dan keperluan umum.
                    </p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-foreground">Navigasi</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('home') }}" class="text-muted transition hover:text-primary">Beranda</a></li>
                        <li><a href="{{ route('services.index') }}" class="text-muted transition hover:text-primary">Layanan</a></li>
                        <li><a href="{{ route('kontak') }}" class="text-muted transition hover:text-primary">Kontak</a></li>
                        <li><a href="{{ route('cart.index') }}" class="text-muted transition hover:text-primary">Keranjang</a></li>
                        <li><a href="{{ route('orders.index') }}" class="text-muted transition hover:text-primary">Pesanan</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold text-foreground">Catatan</p>
                    <p class="mt-3 text-sm leading-relaxed text-muted">
                        Harga di situs ini adalah harga contoh dan dapat diubah oleh admin.
                        Situs contoh untuk keperluan akademik.
                    </p>
                </div>
            </div>
            <div class="border-t border-border">
                <div class="mx-auto max-w-6xl px-4 py-4 text-xs text-muted sm:px-6 lg:px-8">
                    &copy; {{ date('Y') }} {{ config('app.name') }}.
                </div>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
