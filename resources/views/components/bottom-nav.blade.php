@php
    $cartCount = \App\Support\Cart::count();
    $printUrl = route('services.index', ['category' => 'digital-print']);
    $printActive = request()->routeIs('services.index') && request()->query('category') === 'digital-print';

    $leftItems = [
        [
            'label' => 'Beranda',
            'href' => route('home'),
            'active' => request()->routeIs('home'),
            'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.59 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75',
        ],
        [
            'label' => 'Layanan',
            'href' => route('services.index'),
            'active' => request()->routeIs('services.*') && ! $printActive,
            'icon' => 'M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 15l4.179-2.25m11.142 0l-5.571 3-5.571-3',
        ],
    ];

    $rightItems = [
        [
            'label' => 'Keranjang',
            'href' => route('cart.index'),
            'active' => request()->routeIs('cart.*'),
            'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
            'badge' => $cartCount > 0 ? ($cartCount > 99 ? '99+' : (string) $cartCount) : null,
        ],
    ];

    if (auth()->guest()) {
        $rightItems[] = [
            'label' => 'Masuk',
            'href' => route('login'),
            'active' => request()->routeIs('login'),
            'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        ];
    } else {
        $account = auth()->user()->isAdmin()
            ? [
                'label' => 'Admin',
                'href' => route('admin.dashboard'),
                'active' => request()->routeIs('admin.*'),
            ]
            : [
                'label' => 'Pesanan',
                'href' => route('orders.index'),
                'active' => request()->routeIs('orders.*') || request()->routeIs('profile.*'),
            ];

        $rightItems[] = [
            'label' => $account['label'],
            'href' => $account['href'],
            'active' => $account['active'],
            'icon' => 'M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z',
        ];
    }
@endphp

<nav
    class="fixed inset-x-0 bottom-0 z-40 w-full max-w-full sm:hidden"
    aria-label="Navigasi bawah"
>
    <div class="mx-auto w-full max-w-lg px-2 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-2">
        <div
            class="flex items-end rounded-xl border border-border/80 bg-surface/90 px-2 py-2 shadow-[inset_0_1px_0_rgba(255,255,255,0.95),0_-1px_0_rgba(15,23,42,0.04),0_16px_32px_-18px_rgba(15,23,42,0.28)] backdrop-blur-md"
        >
            {{-- Left group: 2 items, flex-1 — Print always sits at true center --}}
            <div class="flex min-w-0 flex-1 items-end gap-0.5">
                @foreach ($leftItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        class="flex min-h-14 min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 transition {{ $item['active'] ? 'bg-primary-soft text-primary ring-1 ring-inset ring-primary/25' : 'text-slate-700 hover:bg-background hover:text-foreground' }}"
                        @if ($item['active']) aria-current="page" @endif
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        <span class="text-[11px] font-bold leading-none tracking-tight">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Center (posisi ke-3): persegi 1:1, pas di tengah --}}
            <a
                href="{{ $printUrl }}"
                class="relative -mt-7 flex h-16 w-16 shrink-0 flex-col items-center justify-center gap-1 rounded-2xl border-2 border-white/90 bg-primary text-white shadow-[0_12px_24px_-8px_rgba(37,99,235,0.6),inset_0_1px_0_rgba(255,255,255,0.3)] transition hover:bg-primary-dark focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary {{ $printActive ? 'ring-2 ring-primary/40 ring-offset-2 ring-offset-surface' : 'ring-1 ring-primary/20' }}"
                @if ($printActive) aria-current="page" @endif
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096M17.28 13.829L18.66 18m-2.34-4.171a42.41 42.41 0 011.39 4.171M6.72 13.829L6.34 18m11.72-4.171L17.657 18M6.72 9.657V6.72A2.25 2.25 0 018.97 4.47h6.06a2.25 2.25 0 012.25 2.25v2.937M17.28 13.829V6.72A2.25 2.25 0 0015.03 4.47H8.97A2.25 2.25 0 006.72 6.72v7.109" />
                </svg>
                <span class="text-[11px] leading-none tracking-tight transition {{ $printActive ? 'font-extrabold [text-shadow:0_0_8px_rgba(255,255,255,0.85),0_0_16px_rgba(255,255,255,0.45)]' : 'font-semibold' }}">Print</span>
            </a>

            {{-- Right group: 2 items, flex-1 — mirrors left so center stays locked --}}
            <div class="flex min-w-0 flex-1 items-end gap-0.5">
                @foreach ($rightItems as $item)
                    <a
                        href="{{ $item['href'] }}"
                        class="relative flex min-h-14 min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-2xl px-1 py-2 transition {{ $item['active'] ? 'bg-primary-soft text-primary ring-1 ring-inset ring-primary/25' : 'text-slate-700 hover:bg-background hover:text-foreground' }}"
                        @if ($item['active']) aria-current="page" @endif
                    >
                        <span class="relative">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                            </svg>
                            @if (! empty($item['badge']))
                                <span class="absolute -right-2.5 -top-2 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[9px] font-bold tabular-nums leading-none text-white ring-2 ring-surface">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </span>
                        <span class="text-[11px] font-bold leading-none tracking-tight">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>
