<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Ubah layanan</h1>
                <p class="mt-1.5 text-sm text-muted">{{ $service->name }}</p>
            </div>
            <a href="{{ route('admin.services.index') }}" class="text-sm font-medium text-muted transition hover:text-primary">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl rounded-2xl border border-border bg-surface p-5 sm:p-6">
        <form method="POST" action="{{ route('admin.services.update', $service) }}">
            @csrf
            @method('PUT')
            @include('admin.services.form', ['service' => $service])

            <div class="mt-6 flex flex-wrap gap-3">
                <x-primary-button>Simpan perubahan</x-primary-button>
                <a href="{{ route('admin.services.index') }}" class="inline-flex min-h-12 items-center rounded-lg border border-border px-4 text-sm font-medium text-foreground transition hover:bg-background">
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
