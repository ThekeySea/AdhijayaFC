<x-admin-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Admin</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Dashboard</h1>
        <p class="mt-1.5 text-sm text-muted">Ringkasan operasional hari ini.</p>
    </x-slot>

    @php
        $stats = [
            ['label' => 'Menunggu pembayaran', 'value' => $pendingCount, 'hint' => 'Pesanan dibuat', 'hintClass' => 'text-slate-700', 'status' => 'PENDING_PAYMENT', 'attr' => 'data-pending-count'],
            ['label' => 'Perlu diproses', 'value' => $paidCount, 'hint' => 'Pembayaran', 'hintClass' => 'text-emerald-700', 'status' => 'PAID', 'attr' => ''],
            ['label' => 'Sedang diproses', 'value' => $processingCount, 'hint' => 'Diproses', 'hintClass' => 'text-sky-700', 'status' => 'PROCESSING', 'attr' => ''],
            ['label' => 'Siap diambil', 'value' => $readyCount, 'hint' => 'Siap diambil', 'hintClass' => 'text-amber-700', 'status' => 'READY', 'attr' => ''],
        ];
        $kelolaClass = 'inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary';
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="rounded-2xl border border-border bg-surface p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $stat['label'] }}</p>
                <p class="mt-2 text-2xl font-bold tabular-nums text-foreground" {!! $stat['attr'] !!}>{{ $stat['value'] }}</p>
                <p class="mt-1 text-sm font-semibold {{ $stat['hintClass'] }}">{{ $stat['hint'] }}</p>
                <a href="{{ route('admin.orders.index', ['status' => $stat['status']]) }}" class="mt-2 inline-block text-xs font-medium text-primary transition hover:underline">Lihat pesanan</a>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-foreground">Pesanan terbaru</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-primary transition hover:underline">Semua pesanan</a>
            </div>

            @if ($recentOrders->isEmpty())
                <div class="mt-6 rounded-xl border border-dashed border-border p-6 text-center" data-recent-orders-empty>
                    <p class="font-medium text-foreground">Belum ada pesanan masuk.</p>
                    <p class="mt-1 text-sm text-muted">Pesanan pelanggan akan tampil di sini.</p>
                </div>
            @else
                <ul class="mt-4 divide-y divide-border" data-recent-orders>
                    @foreach ($recentOrders as $order)
                        <li data-order-id="{{ $order->id }}">
                            <a href="{{ route('admin.orders.show', $order) }}" class="flex flex-wrap items-center justify-between gap-3 py-3 transition hover:bg-background/60">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-foreground">{{ $order->order_number }}</p>
                                    <p class="mt-0.5 text-sm text-muted">
                                        {{ $order->customer?->name ?? '—' }} · {{ $order->items_count }} item
                                    </p>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-1">
                                    <span class="rounded-lg px-2 py-1 text-xs font-semibold {{ $order->status->badgeClass() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                    <span class="text-sm font-bold tabular-nums text-foreground">{{ $order->formattedTotal() }}</span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-foreground">Booking mendatang</h2>
                <p class="text-xs font-semibold uppercase tracking-wide text-muted">{{ $activeServices }} layanan aktif</p>
            </div>

            @if ($upcomingBookings->isEmpty())
                <div class="mt-6 rounded-xl border border-dashed border-border p-6 text-center">
                    <p class="font-medium text-foreground">Belum ada booking mendatang.</p>
                    <p class="mt-1 text-sm text-muted">Jadwal ambil pelanggan akan tampil di sini.</p>
                </div>
            @else
                <ul class="mt-4 divide-y divide-border">
                    @foreach ($upcomingBookings as $booking)
                        <li class="py-3 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-foreground">{{ $booking->formattedDate() }} · {{ $booking->time_slot }}</p>
                                    <p class="mt-0.5 text-sm text-muted">{{ $booking->customer?->name ?? '—' }}</p>
                                    @if ($booking->orders->first())
                                        <a href="{{ route('admin.orders.show', $booking->orders->first()) }}" class="mt-1 inline-block text-xs font-medium text-primary transition hover:underline">
                                            {{ $booking->orders->first()->order_number }}
                                        </a>
                                    @endif
                                </div>
                                @if ($booking->note)
                                    <p class="max-w-xs text-xs leading-relaxed text-muted">{{ $booking->note }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-border bg-surface p-5 sm:p-6">
        <h2 class="text-base font-semibold text-foreground">Kelola</h2>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('admin.orders.index') }}" class="{{ $kelolaClass }}">Kelola pesanan</a>
            <a href="{{ route('admin.services.index') }}" class="{{ $kelolaClass }}">Kelola layanan</a>
            <a href="{{ route('admin.categories.index') }}" class="{{ $kelolaClass }}">Kelola kategori</a>
        </div>
    </div>
</x-admin-layout>
