<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Admin - '.config('app.name', 'Fotocopy Adhijaya') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-background text-foreground font-sans antialiased">
        <div class="min-h-screen lg:flex">
            <aside class="border-b border-border bg-surface lg:sticky lg:top-0 lg:h-screen lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r">
                <div class="flex h-16 items-center justify-between gap-3 px-4 lg:h-auto lg:border-b lg:border-border lg:px-5 lg:py-5">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary text-white">
                            <x-application-logo class="h-5 w-5" />
                        </span>
                        <span class="min-w-0 leading-tight">
                            <span class="block truncate text-sm font-bold text-foreground">{{ config('app.name') }}</span>
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-primary">Admin</span>
                        </span>
                    </a>
                    <span class="rounded-lg bg-primary-soft px-2 py-1 text-xs font-semibold text-primary lg:hidden">Admin</span>
                </div>

                <nav class="hidden space-y-1 p-3 lg:block">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.services.index') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('admin.services.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground' }}">
                        Layanan
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('profile.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground' }}">
                        Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-muted transition hover:bg-background hover:text-foreground">
                            Keluar
                        </button>
                    </form>
                </nav>

                <nav class="flex gap-1 overflow-x-auto border-t border-border px-3 py-2 lg:hidden">
                    <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition {{ request()->routeIs('admin.dashboard') ? 'bg-primary-soft text-primary' : 'text-muted' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.services.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition {{ request()->routeIs('admin.services.*') ? 'bg-primary-soft text-primary' : 'text-muted' }}">
                        Layanan
                    </a>
                    <a href="{{ route('profile.edit') }}" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition {{ request()->routeIs('profile.*') ? 'bg-primary-soft text-primary' : 'text-muted' }}">
                        Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-muted transition">
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
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
