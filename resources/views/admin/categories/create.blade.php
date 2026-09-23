<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Tambah kategori</h1>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="text-sm font-medium text-muted transition hover:text-primary">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-xl rounded-2xl border border-border bg-surface p-5 sm:p-6">
        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            @include('admin.categories.form')

            <div class="mt-6 flex flex-wrap gap-3">
                <x-primary-button>Simpan kategori</x-primary-button>
                <a href="{{ route('admin.categories.index') }}" class="inline-flex min-h-12 items-center rounded-lg border border-border px-4 text-sm font-medium text-foreground transition hover:bg-background">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
