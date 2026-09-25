<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Pesanan</p>
                <h1 class="mt-1 text-balance text-2xl font-bold tracking-tight text-foreground">Riwayat pesanan</h1>
            </div>
            <a href="{{ route('services.index') }}" class="text-sm font-medium text-muted transition hover:text-primary">
                Pesan lagi
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        @if ($orders->isEmpty())
            <div class="rounded-2xl border border-border bg-surface p-10 text-center">
                <h2 class="text-lg font-semibold text-foreground">Belum ada pesanan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-muted">
                    Pesanan yang kamu buat akan tampil di sini beserta statusnya.
                </p>
                <a href="{{ route('services.index') }}" class="mt-6 inline-flex min-h-12 items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                    Lihat layanan
                </a>
            </div>
        @else
            <ul class="space-y-4">
                @foreach ($orders as $order)
                    <li>
                        <a href="{{ route('orders.show', $order) }}" class="block rounded-2xl border border-border bg-surface p-5 transition hover:border-primary/40 hover:shadow-sm sm:p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-base font-semibold text-foreground">{{ $order->order_number }}</p>
                                    <p class="mt-1 text-sm text-muted">
                                        {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                                        · {{ $order->items_count }} item
                                        @if ($order->booking)
                                            · Ambil {{ $order->booking->formattedDate() }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-sm text-foreground">
                                        {{ $order->items->pluck('service_name_snapshot')->take(3)->join(', ') }}
                                        @if ($order->items_count > 3)
                                            <span class="text-muted">dan lainnya</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span data-status-badge data-order-id="{{ $order->id }}" class="rounded-lg px-2.5 py-1 text-xs font-semibold {{ $order->status->badgeClass() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                    <p class="text-base font-bold tabular-nums text-foreground">{{ $order->formattedTotal() }}</p>
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
