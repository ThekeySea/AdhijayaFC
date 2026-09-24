<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Kategori jasa</h1>
                <p class="mt-1.5 text-sm text-muted">Kelola kategori yang dipakai di katalog dan beranda.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="inline-flex min-h-12 items-center rounded-lg bg-primary px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                Tambah kategori
            </a>
        </div>
    </x-slot>

    <div class="overflow-hidden rounded-2xl border border-border bg-surface">
        @if ($categories->isEmpty())
            <div class="p-10 text-center">
                <p class="font-medium text-foreground">Belum ada kategori.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-background text-xs font-semibold uppercase tracking-wide text-muted">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Slug</th>
                            <th class="px-4 py-3">Urutan</th>
                            <th class="px-4 py-3">Layanan aktif</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($categories as $category)
                            <tr class="transition hover:bg-background/60">
                                <td class="px-4 py-3 font-medium text-foreground">{{ $category->name }}</td>
                                <td class="px-4 py-3 text-muted">{{ $category->slug }}</td>
                                <td class="px-4 py-3 tabular-nums text-muted">{{ $category->sort_order }}</td>
                                <td class="px-4 py-3 tabular-nums text-muted">{{ $category->services_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-lg px-2 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-primary-soft text-primary' : 'bg-background text-muted' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                                            Ubah
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:bg-background">
                                                {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori ini? Layanan di dalamnya menjadi tanpa kategori.')">
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
        @endif
    </div>
</x-admin-layout>
