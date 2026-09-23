<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Analitik</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Laporan</h1>
                <p class="mt-1.5 text-sm text-muted">Periode {{ $rangeLabel }}</p>
            </div>
            <div class="no-print flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-muted transition hover:text-primary">
                    Dashboard
                </a>
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex min-h-11 items-center justify-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary"
                >
                    Cetak / PDF
                </button>
            </div>
        </div>
    </x-slot>

    {{-- Header cetak (hanya tampil di print) --}}
    <div class="mb-4 hidden print:block">
        <p class="text-lg font-bold text-foreground">Laporan Fotocopy Adhijaya</p>
        <p class="text-sm text-muted">Periode {{ $rangeLabel }} ·Dicetak {{ now()->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</p>
    </div>

    {{-- KPI strip --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-border bg-foreground p-4 text-white sm:p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-white/70">Pendapatan</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums tracking-tight sm:text-3xl">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-white/70">Order aktif–selesai</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Total pesanan</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-foreground sm:text-3xl">{{ $totalOrders }}</p>
            <p class="mt-1 text-xs text-muted">Sepanjang waktu</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Pelanggan</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-foreground sm:text-3xl">{{ $customerCount }}</p>
            <p class="mt-1 text-xs text-muted">{{ $activeServices }} layanan aktif</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Rata-rata / selesai</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-foreground sm:text-3xl">Rp {{ number_format($avgOrder, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-muted">{{ $completedCount }} pesanan selesai</p>
        </div>
    </div>

    {{-- Chart utama full width + filter periode --}}
    <div class="mt-4 rounded-2xl border border-border bg-surface p-4 sm:p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-foreground">Grafik pesanan</h2>
                <p class="mt-0.5 text-xs text-muted">{{ $days }} hari terakhir · jumlah order per hari</p>
            </div>
            <div class="no-print flex rounded-lg border border-border bg-background p-0.5" role="group" aria-label="Filter periode">
                @foreach ($periodOptions as $option)
                    <a
                        href="{{ route('admin.reports.index', ['periode' => $option]) }}"
                        class="rounded-md px-3 py-1.5 text-xs font-semibold transition {{ $days === $option ? 'bg-primary text-white shadow-sm' : 'text-muted hover:text-foreground' }}"
                    >
                        {{ $option }}h
                    </a>
                @endforeach
            </div>
        </div>

        @php
            $hasChartValues = collect($chartValues)->sum() > 0;
            $plotPx = 176;
            $gridLines = [0.25, 0.5, 0.75, 1.0];
        @endphp

        @if (! $hasChartValues)
            <div class="mt-5 rounded-xl border border-dashed border-border p-8 text-center">
                <p class="text-sm font-medium text-foreground">Belum ada pesanan pada periode ini.</p>
                <p class="mt-1 text-xs leading-relaxed text-muted">
                    Grafik akan terisi otomatis begitu ada order masuk dalam {{ $days }} hari terakhir.
                </p>
            </div>
        @else
            <div class="mt-5" role="img" aria-label="Grafik batang jumlah pesanan {{ $days }} hari terakhir">
                {{-- Plot area + grid --}}
                <div class="relative border-b border-border" style="height: {{ $plotPx }}px">
                    @foreach ($gridLines as $ratio)
                        <div
                            class="pointer-events-none absolute inset-x-0 border-t border-dashed border-border/70"
                            style="bottom: {{ $ratio * 100 }}%"
                            aria-hidden="true"
                        ></div>
                    @endforeach

                    <div class="absolute inset-0 flex items-end gap-[3px] sm:gap-1">
                        @foreach ($chartValues as $index => $value)
                            @php
                                $barPx = $chartMax > 0 && $value > 0
                                    ? max(8, (int) round(($value / $chartMax) * ($plotPx - 8)))
                                    : 0;
                                $showLabel = $days <= 14 || $index % 2 === 0 || $index === $days - 1;
                            @endphp
                            <div class="group relative flex h-full min-w-0 flex-1 items-end justify-center">
                                @if ($value > 0)
                                    <span class="pointer-events-none absolute -top-1 left-1/2 z-10 -translate-x-1/2 rounded bg-foreground px-1.5 py-0.5 text-[10px] font-semibold tabular-nums text-white opacity-0 shadow-sm transition group-hover:opacity-100">
                                        {{ $value }}
                                    </span>
                                @endif
                                <div
                                    class="{{ $value > 0 ? 'w-full rounded-t-sm bg-primary transition group-hover:bg-primary-dark' : 'w-full rounded-t-sm bg-border/60' }}"
                                    style="height: {{ $value > 0 ? $barPx : 3 }}px"
                                    title="{{ $chartLabels[$index] }}: {{ $value }} pesanan"
                                ></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Labels --}}
                <div class="mt-2 flex gap-[3px] sm:gap-1">
                    @foreach ($chartLabels as $index => $label)
                        @php
                            $showLabel = $days <= 14 || $index % 2 === 0 || $index === $days - 1;
                        @endphp
                        <span class="min-w-0 flex-1 truncate text-center text-[9px] leading-tight text-muted sm:text-[10px] {{ $showLabel ? '' : 'lg:invisible' }}">
                            {{ $showLabel ? $label : '·' }}
                        </span>
                    @endforeach
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-muted">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-primary" aria-hidden="true"></span>
                        Jumlah order
                    </span>
                    <span>Maks {{ $chartMax }} order/hari</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Donut + Top layanan --}}
    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <h2 class="text-base font-semibold text-foreground">Status pesanan</h2>
            <p class="mt-0.5 text-xs text-muted">Distribusi seluruh order</p>

            @if ($pieTotal > 0)
                @php
                    $colors = [
                        '#f59e0b',
                        '#2563eb',
                        '#7c3aed',
                        '#0d9488',
                        '#16a34a',
                        '#64748b',
                        '#dc2626',
                    ];
                    $circumference = 2 * pi() * 40;
                    $offset = 0;
                @endphp

                <div class="mt-4 flex flex-col items-center gap-5 sm:flex-row sm:items-center">
                    <svg viewBox="0 0 100 100" class="h-36 w-36 shrink-0 -rotate-90" role="img" aria-label="Diagram lingkaran status pesanan">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="14" />
                        @foreach ($pieSlices as $index => $slice)
                            @php
                                $fraction = $slice['value'] / $pieTotal;
                                $length = $fraction * $circumference;
                                $color = $colors[$index % count($colors)];
                            @endphp
                            <circle
                                cx="50"
                                cy="50"
                                r="40"
                                fill="none"
                                stroke="{{ $color }}"
                                stroke-width="14"
                                stroke-dasharray="{{ $length }} {{ $circumference - $length }}"
                                stroke-dashoffset="{{ -$offset }}"
                            />
                            @php
                                $offset += $length;
                            @endphp
                        @endforeach
                    </svg>

                    <ul class="w-full min-w-0 space-y-1.5">
                        @foreach ($pieSlices as $index => $slice)
                            <li class="flex items-center justify-between gap-2 text-sm">
                                <span class="flex min-w-0 items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: {{ $colors[$index % count($colors)] }}"></span>
                                    <span class="truncate text-muted">{{ $slice['label'] }}</span>
                                </span>
                                <span class="shrink-0 font-semibold tabular-nums text-foreground">{{ $slice['value'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="mt-6 rounded-xl border border-dashed border-border p-6 text-center">
                    <p class="text-sm text-muted">Belum ada pesanan untuk ditampilkan.</p>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-foreground">Laris manis</h2>
                    <p class="mt-0.5 text-xs text-muted">Top 5 item terjual</p>
                </div>
                <a href="{{ route('admin.services.index') }}" class="no-print text-xs font-medium text-primary transition hover:underline">Kelola layanan</a>
            </div>

            @if ($topServices->isEmpty())
                <div class="mt-5 rounded-xl border border-dashed border-border p-6 text-center">
                    <p class="text-sm text-muted">Belum ada item pesanan.</p>
                </div>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($topServices as $item)
                        @php
                            $maxQty = max((int) $topServices->first()->qty, 1);
                            $width = max(8, (int) round(((int) $item->qty / $maxQty) * 100));
                        @endphp
                        <li>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="min-w-0 truncate font-medium text-foreground">{{ $item->name }}</span>
                                <span class="shrink-0 tabular-nums text-muted">{{ $item->qty }} item</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-background">
                                <div class="h-full rounded-full bg-primary" style="width: {{ $width }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- Tabel ringkasan (untuk cetak & scan cepat) --}}
    <div class="mt-4 rounded-2xl border border-border bg-surface p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold text-foreground">Ringkasan status</h2>
                <p class="mt-0.5 text-xs text-muted">Jumlah pesanan &amp; total nilai per status</p>
            </div>
            <span class="rounded-lg bg-primary-soft px-2 py-1 text-xs font-semibold text-primary">{{ $totalOrders }} total</span>
        </div>

        @if ($statusRows->isEmpty())
            <div class="mt-5 rounded-xl border border-dashed border-border p-6 text-center">
                <p class="text-sm text-muted">Belum ada data pesanan.</p>
            </div>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[420px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-border text-xs uppercase tracking-wide text-muted">
                            <th scope="col" class="py-2 pr-4 font-semibold">Status</th>
                            <th scope="col" class="py-2 pr-4 text-right font-semibold">Jumlah</th>
                            <th scope="col" class="py-2 text-right font-semibold">Total nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($statusRows as $row)
                            <tr>
                                <td class="py-2.5 pr-4 font-medium text-foreground">{{ $row['label'] }}</td>
                                <td class="py-2.5 pr-4 text-right tabular-nums text-foreground">{{ $row['count'] }}</td>
                                <td class="py-2.5 text-right tabular-nums text-foreground">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-border text-base font-bold">
                            <td class="py-3 pr-4 text-foreground">Total</td>
                            <td class="py-3 pr-4 text-right tabular-nums text-foreground">{{ $totalOrders }}</td>
                            <td class="py-3 text-right tabular-nums text-foreground">Rp {{ number_format($statusRows->sum('total'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif

        <p class="mt-4 text-xs leading-relaxed text-muted">
            Grafik menghitung order dalam {{ $days }} hari terakhir.
            Pendapatan KPI dijumlahkan dari status <strong class="text-foreground">Dibayar</strong> sampai
            <strong class="text-foreground">Selesai</strong> (belum termasuk yang dibatalkan).
        </p>
    </div>
</x-admin-layout>
