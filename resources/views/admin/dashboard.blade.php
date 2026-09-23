<x-admin-layout>
    <x-slot name="header">
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Admin</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Dashboard</h1>
        <p class="mt-1.5 text-sm text-muted">Ringkasan pesanan akan tampil di sini.</p>
    </x-slot>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Pesanan masuk</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">0</p>
            <p class="mt-1 text-sm text-muted">Belum ada data.</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Sedang diproses</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">0</p>
            <p class="mt-1 text-sm text-muted">Belum ada data.</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Layanan aktif</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-primary">{{ \App\Models\Service::query()->active()->count() }}</p>
            <p class="mt-1 text-sm text-muted">Dari katalog saat ini.</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-dashed border-border bg-surface p-8 text-center">
        <p class="font-medium text-foreground">Belum ada pesanan masuk.</p>
        <p class="mt-1 text-sm text-muted">Kelola pesanan akan tersedia pada tahap berikutnya.</p>
        <a href="{{ route('admin.services.index') }}" class="mt-5 inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
            Kelola layanan
        </a>
    </div>
</x-admin-layout>
