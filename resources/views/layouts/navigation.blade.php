@php
    $cartCount = \App\Support\Cart::count();
@endphp

<nav class="fixed inset-x-0 top-0 z-40 w-full max-w-full">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-2 px-3 pt-4 sm:px-6 lg:px-8">
        {{-- Pill utama: logo + nav + auth --}}
        <div class="flex min-w-0 flex-1 items-center gap-0.5 rounded-full border border-white/70 bg-white/95 px-1.5 py-1.5 shadow-[0_12px_32px_-12px_rgba(15,23,42,0.35),inset_0_1px_0_rgba(255,255,255,0.95)] backdrop-blur-md sm:gap-1 sm:px-2">
            <a
                href="{{ route('home') }}"
                class="group inline-flex min-h-11 shrink-0 items-center gap-2 rounded-full px-1.5 transition hover:bg-background/80 sm:gap-2.5 sm:px-2"
            >
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary text-white shadow-sm transition group-hover:bg-primary-dark">
                    <x-application-logo class="h-5 w-5" />
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-[13px] font-bold tracking-tight text-foreground sm:text-sm">Fotocopy Adhijaya</span>
                    <span class="hidden truncate text-[11px] font-medium text-muted lg:block">Fotokopi & percetakan</span>
                </span>
            </a>

            {{-- Desktop only: link utama di dalam pill --}}
            <div class="hidden min-w-0 items-center sm:flex">
                <span class="mx-1 h-6 w-px bg-border/80" aria-hidden="true"></span>

                <a
                    href="{{ route('home') }}"
                    class="rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 {{ request()->routeIs('home') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground' }}"
                >
                    Beranda
                </a>
                <a
                    href="{{ route('services.index') }}"
                    class="rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 {{ request()->routeIs('services.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground' }}"
                >
                    Layanan
                </a>
                <a
                    href="{{ route('cart.index') }}"
                    class="relative inline-flex items-center gap-2 rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 {{ request()->routeIs('cart.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground' }}"
                >
                    Keranjang
                    @if ($cartCount > 0)
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold tabular-nums text-white">{{ $cartCount > 99 ? '99+' : $cartCount }}</span>
                    @endif
                </a>

                <span class="mx-1 h-6 w-px bg-border/80" aria-hidden="true"></span>

                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 {{ request()->routeIs('admin.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground' }}">
                            Dashboard admin
                        </a>
                    @else
                        <a href="{{ route('orders.index') }}" class="rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 {{ request()->routeIs('orders.*') || request()->routeIs('profile.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground' }}">
                            Pesanan
                        </a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-full px-2.5 py-2 text-sm font-semibold text-muted transition hover:bg-background hover:text-foreground lg:px-3.5">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-full px-2.5 py-2 text-sm font-semibold text-muted transition hover:bg-background hover:text-foreground lg:px-3.5">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="ms-0.5 inline-flex min-h-11 items-center rounded-full bg-primary px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark lg:ms-1 lg:px-4">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>

        {{-- Pill Kontak (terpisah, mengambang) --}}
        <a
            href="{{ route('kontak') }}"
            class="inline-flex min-h-12 shrink-0 items-center gap-1.5 rounded-full border border-white/70 bg-white/95 px-3 text-[13px] font-bold text-foreground shadow-[0_12px_32px_-12px_rgba(15,23,42,0.35),inset_0_1px_0_rgba(255,255,255,0.95)] backdrop-blur-md transition hover:border-primary/35 hover:bg-primary-soft hover:text-primary sm:gap-2 sm:px-4 sm:text-sm {{ request()->routeIs('kontak') ? 'border-primary/35 bg-primary-soft text-primary' : '' }}"
            @if (request()->routeIs('kontak')) aria-current="page" @endif
        >
            <svg class="h-4 w-4 shrink-0 sm:h-4.5 sm:w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
            Kontak
        </a>
    </div>
</nav>

{{-- Spacer hanya di luar beranda; di beranda hero mulai dari atas, navbar menumpuk di atasnya --}}
@if (! request()->routeIs('home'))
    <div class="h-[84px]" aria-hidden="true"></div>
@endif
