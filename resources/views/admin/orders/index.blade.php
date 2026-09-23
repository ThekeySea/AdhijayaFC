<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Operasional</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Pesanan</h1>
                <p class="mt-1.5 text-sm text-muted">Pantau dan perbarui status pesanan masuk.</p>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-primary-line bg-primary-soft px-4 py-3 text-sm font-medium text-foreground">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.orders.index') }}"
           class="inline-flex min-h-11 items-center rounded-lg px-3 text-xs font-semibold transition {{ $activeFilter === '' ? 'bg-primary-soft text-primary' : 'border border-border text-muted hover:bg-background hover:text-foreground' }}">
            Semua
        </a>
        @foreach ($statuses as $status)
            <a href="{{ route('admin.orders.index', ['status' => $status->value]) }}"
               class="inline-flex min-h-11 items-center rounded-lg px-3 text-xs font-semibold transition {{ $activeFilter === $status->value ? 'bg-primary-soft text-primary' : 'border border-border text-muted hover:bg-background hover:text-foreground' }}">
                {{ $status->label() }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-border bg-surface">
        @if ($orders->isEmpty())
            <div class="p-10 text-center">
                <p class="font-medium text-foreground">Belum ada pesanan{{ $activeFilter !== '' ? ' dengan status ini' : '' }}.</p>
                <p class="mt-1 text-sm text-muted">Pesanan pelanggan akan tampil di sini.</p>
            </div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-background text-xs font-semibold uppercase tracking-wide text-muted">
                        <tr>
                            <th class="px-4 py-3">Nomor</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Dibuat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($orders as $order)
                            <tr class="transition hover:bg-background/60">
                                <td class="px-4 py-3 font-medium text-foreground">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-muted">
                                    {{ $order->customer?->name ?? '—' }}
                                    <span class="block text-xs">{{ $order->items_count }} item</span>
                                </td>
                                <td class="px-4 py-3 font-medium tabular-nums text-foreground">{{ $order->formattedTotal() }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-lg bg-primary-soft px-2 py-1 text-xs font-semibold text-primary">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="divide-y divide-border md:hidden">
                @foreach ($orders as $order)
                    <li class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-foreground">{{ $order->order_number }}</p>
                                <p class="mt-1 text-sm text-muted">{{ $order->customer?->name ?? '—' }} · {{ $order->items_count }} item</p>
                                <p class="mt-1 text-sm font-medium tabular-nums text-foreground">{{ $order->formattedTotal() }}</p>
                                <span class="mt-2 inline-flex rounded-lg bg-primary-soft px-2 py-1 text-xs font-semibold text-primary">
                                    {{ $order->status->label() }}
                                </span>
                            </div>
                            <a href="{{ route('admin.orders.show', $order) }}"
                               class="inline-flex min-h-11 shrink-0 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground">
                                Detail
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</x-admin-layout>
