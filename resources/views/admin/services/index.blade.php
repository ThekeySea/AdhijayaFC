<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Layanan</h1>
                <p class="mt-1.5 text-sm text-muted">Kelola katalog dan harga layanan.</p>
            </div>
            <a href="{{ route('admin.services.create') }}" class="inline-flex min-h-12 items-center rounded-lg bg-primary px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                Tambah layanan
            </a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-2xl border border-border bg-surface">
        @if ($services->isEmpty())
            <div class="p-10 text-center">
                <p class="font-medium text-foreground">Belum ada layanan.</p>
                <p class="mt-1 text-sm text-muted">Tambahkan layanan pertama Anda.</p>
            </div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-background text-xs font-semibold uppercase tracking-wide text-muted">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($services as $service)
                            <tr class="transition hover:bg-background/60">
                                <td class="px-4 py-3 font-medium text-foreground">{{ $service->name }}</td>
                                <td class="px-4 py-3 text-muted">{{ $service->badgeLabel() }}</td>
                                <td class="px-4 py-3 font-medium tabular-nums text-foreground">
                                    {{ $service->formattedPrice() }}/{{ $service->unit }}
                                    @if ($service->priceTiers->isNotEmpty())
                                        <span class="mt-0.5 block text-[11px] font-medium text-muted">{{ $service->priceTiers->count() }} tier harga</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-lg px-2 py-1 text-xs font-semibold {{ $service->is_active ? 'bg-primary-soft text-primary' : 'bg-background text-muted' }}">
                                        {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.services.toggle', $service) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:bg-background">
                                                {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Hapus layanan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-red-200 px-3 text-xs font-medium text-red-600 transition hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <ul class="divide-y divide-border md:hidden">
                @foreach ($services as $service)
                    <li class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-foreground">{{ $service->name }}</p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-muted">{{ $service->badgeLabel() }}</p>
                                <p class="mt-1 text-sm font-medium tabular-nums text-muted">
                                    {{ $service->formattedPrice() }}/{{ $service->unit }}
                                    @if ($service->priceTiers->isNotEmpty())
                                        · {{ $service->priceTiers->count() }} tier
                                    @endif
                                </p>
                                <span class="mt-2 inline-flex rounded-lg px-2 py-1 text-xs font-semibold {{ $service->is_active ? 'bg-primary-soft text-primary' : 'bg-background text-muted' }}">
                                    {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground">
                                Ubah
                            </a>
                            <form method="POST" action="{{ route('admin.services.toggle', $service) }}">
                                @csrf
                                <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground">
                                    {{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Hapus layanan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-red-200 px-3 text-xs font-medium text-red-600">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-admin-layout>
