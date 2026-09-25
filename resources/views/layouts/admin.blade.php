<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-reverb="true" data-reverb-scope="admin">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Admin - '.config('app.name', 'Fotocopy Adhijaya') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-background text-foreground font-sans antialiased">
        <div class="min-h-screen lg:flex">
            <aside class="border-b border-slate-800 bg-foreground lg:sticky lg:top-0 lg:h-screen lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r lg:border-slate-800">
                <div class="flex h-16 items-center justify-between gap-3 px-4 lg:h-auto lg:border-b lg:border-white/10 lg:px-5 lg:py-5">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary text-white">
                            <x-application-logo class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 leading-tight">
                            <span class="block truncate text-sm font-bold text-white">{{ config('app.name') }}</span>
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-blue-300">Admin</span>
                        </span>
                    </a>
                    <span class="rounded-lg bg-white/10 px-2 py-1 text-xs font-semibold text-white lg:hidden">Admin</span>
                </div>

                @php
                    $adminLinks = [
                        ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Dashboard'],
                        ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Laporan'],
                        ['route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'label' => 'Pesanan'],
                        ['route' => 'admin.services.index', 'pattern' => 'admin.services.*', 'label' => 'Layanan'],
                        ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Kategori'],
                        ['route' => 'admin.business-settings.edit', 'pattern' => 'admin.business-settings.*', 'label' => 'Info Usaha'],
                    ];
                @endphp

                <nav class="hidden space-y-1 p-3 lg:block">
                    @foreach ($adminLinks as $link)
                        <a href="{{ route($link['route']) }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-white transition {{ request()->routeIs($link['pattern']) ? 'bg-white/20' : 'hover:bg-white/10' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-white transition hover:bg-white/10">
                            Keluar
                        </button>
                    </form>
                </nav>

                <nav class="flex gap-1 overflow-x-auto border-t border-white/10 px-3 py-2 lg:hidden">
                    @foreach ($adminLinks as $link)
                        <a href="{{ route($link['route']) }}" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition {{ request()->routeIs($link['pattern']) ? 'bg-white/20' : 'hover:bg-white/10' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition hover:bg-white/10">
                            Keluar
                        </button>
                    </form>
                </nav>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="border-b border-border bg-surface">
                    <div class="px-4 py-5 sm:px-6 lg:px-8">
                        {{ $header ?? '' }}
                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <div class="mb-4 rounded-xl border border-primary-line bg-primary-soft px-4 py-3 text-sm font-medium text-foreground" role="status">
                            {{ session('status') }}
                        </div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
