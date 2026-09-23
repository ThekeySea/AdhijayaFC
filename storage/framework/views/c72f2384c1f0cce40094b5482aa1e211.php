<?php if (isset($component)) { $__componentOriginal91fdd17964e43374ae18c674f95cdaa3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3 = $attributes; } ?>
<?php $component = App\View\Components\AdminLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AdminLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Analitik</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Laporan</h1>
                <p class="mt-1.5 text-sm text-muted">Periode <?php echo e($rangeLabel); ?></p>
            </div>
            <div class="no-print flex flex-wrap items-center gap-2">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-sm font-medium text-muted transition hover:text-primary">
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
     <?php $__env->endSlot(); ?>

    
    <div class="mb-4 hidden print:block">
        <p class="text-lg font-bold text-foreground">Laporan Fotocopy Adhijaya</p>
        <p class="text-sm text-muted">Periode <?php echo e($rangeLabel); ?> ·Dicetak <?php echo e(now()->locale('id')->isoFormat('D MMM YYYY, HH:mm')); ?></p>
    </div>

    
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-2xl border border-border bg-foreground p-4 text-white sm:p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-white/70">Pendapatan</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums tracking-tight sm:text-3xl">Rp <?php echo e(number_format($revenue, 0, ',', '.')); ?></p>
            <p class="mt-1 text-xs text-white/70">Order aktif–selesai</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Total pesanan</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-foreground sm:text-3xl"><?php echo e($totalOrders); ?></p>
            <p class="mt-1 text-xs text-muted">Sepanjang waktu</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Pelanggan</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-foreground sm:text-3xl"><?php echo e($customerCount); ?></p>
            <p class="mt-1 text-xs text-muted"><?php echo e($activeServices); ?> layanan aktif</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Rata-rata / selesai</p>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-foreground sm:text-3xl">Rp <?php echo e(number_format($avgOrder, 0, ',', '.')); ?></p>
            <p class="mt-1 text-xs text-muted"><?php echo e($completedCount); ?> pesanan selesai</p>
        </div>
    </div>

    
    <div class="mt-4 rounded-2xl border border-border bg-surface p-4 sm:p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-foreground">Grafik pesanan</h2>
                <p class="mt-0.5 text-xs text-muted"><?php echo e($days); ?> hari terakhir · jumlah order per hari</p>
            </div>
            <div class="no-print flex rounded-lg border border-border bg-background p-0.5" role="group" aria-label="Filter periode">
                <?php $__currentLoopData = $periodOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e(route('admin.reports.index', ['periode' => $option])); ?>"
                        class="rounded-md px-3 py-1.5 text-xs font-semibold transition <?php echo e($days === $option ? 'bg-primary text-white shadow-sm' : 'text-muted hover:text-foreground'); ?>"
                    >
                        <?php echo e($option); ?>h
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <?php
            $hasChartValues = collect($chartValues)->sum() > 0;
            $plotPx = 176;
            $gridLines = [0.25, 0.5, 0.75, 1.0];
        ?>

        <?php if(! $hasChartValues): ?>
            <div class="mt-5 rounded-xl border border-dashed border-border p-8 text-center">
                <p class="text-sm font-medium text-foreground">Belum ada pesanan pada periode ini.</p>
                <p class="mt-1 text-xs leading-relaxed text-muted">
                    Grafik akan terisi otomatis begitu ada order masuk dalam <?php echo e($days); ?> hari terakhir.
                </p>
            </div>
        <?php else: ?>
            <div class="mt-5" role="img" aria-label="Grafik batang jumlah pesanan <?php echo e($days); ?> hari terakhir">
                
                <div class="relative border-b border-border" style="height: <?php echo e($plotPx); ?>px">
                    <?php $__currentLoopData = $gridLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ratio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="pointer-events-none absolute inset-x-0 border-t border-dashed border-border/70"
                            style="bottom: <?php echo e($ratio * 100); ?>%"
                            aria-hidden="true"
                        ></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    <div class="absolute inset-0 flex items-end gap-[3px] sm:gap-1">
                        <?php $__currentLoopData = $chartValues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $barPx = $chartMax > 0 && $value > 0
                                    ? max(8, (int) round(($value / $chartMax) * ($plotPx - 8)))
                                    : 0;
                                $showLabel = $days <= 14 || $index % 2 === 0 || $index === $days - 1;
                            ?>
                            <div class="group relative flex h-full min-w-0 flex-1 items-end justify-center">
                                <?php if($value > 0): ?>
                                    <span class="pointer-events-none absolute -top-1 left-1/2 z-10 -translate-x-1/2 rounded bg-foreground px-1.5 py-0.5 text-[10px] font-semibold tabular-nums text-white opacity-0 shadow-sm transition group-hover:opacity-100">
                                        <?php echo e($value); ?>

                                    </span>
                                <?php endif; ?>
                                <div
                                    class="<?php echo e($value > 0 ? 'w-full rounded-t-sm bg-primary transition group-hover:bg-primary-dark' : 'w-full rounded-t-sm bg-border/60'); ?>"
                                    style="height: <?php echo e($value > 0 ? $barPx : 3); ?>px"
                                    title="<?php echo e($chartLabels[$index]); ?>: <?php echo e($value); ?> pesanan"
                                ></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                
                <div class="mt-2 flex gap-[3px] sm:gap-1">
                    <?php $__currentLoopData = $chartLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $showLabel = $days <= 14 || $index % 2 === 0 || $index === $days - 1;
                        ?>
                        <span class="min-w-0 flex-1 truncate text-center text-[9px] leading-tight text-muted sm:text-[10px] <?php echo e($showLabel ? '' : 'lg:invisible'); ?>">
                            <?php echo e($showLabel ? $label : '·'); ?>

                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-muted">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-primary" aria-hidden="true"></span>
                        Jumlah order
                    </span>
                    <span>Maks <?php echo e($chartMax); ?> order/hari</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    
    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <h2 class="text-base font-semibold text-foreground">Status pesanan</h2>
            <p class="mt-0.5 text-xs text-muted">Distribusi seluruh order</p>

            <?php if($pieTotal > 0): ?>
                <?php
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
                ?>

                <div class="mt-4 flex flex-col items-center gap-5 sm:flex-row sm:items-center">
                    <svg viewBox="0 0 100 100" class="h-36 w-36 shrink-0 -rotate-90" role="img" aria-label="Diagram lingkaran status pesanan">
                        <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="14" />
                        <?php $__currentLoopData = $pieSlices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $fraction = $slice['value'] / $pieTotal;
                                $length = $fraction * $circumference;
                                $color = $colors[$index % count($colors)];
                            ?>
                            <circle
                                cx="50"
                                cy="50"
                                r="40"
                                fill="none"
                                stroke="<?php echo e($color); ?>"
                                stroke-width="14"
                                stroke-dasharray="<?php echo e($length); ?> <?php echo e($circumference - $length); ?>"
                                stroke-dashoffset="<?php echo e(-$offset); ?>"
                            />
                            <?php
                                $offset += $length;
                            ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </svg>

                    <ul class="w-full min-w-0 space-y-1.5">
                        <?php $__currentLoopData = $pieSlices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $slice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between gap-2 text-sm">
                                <span class="flex min-w-0 items-center gap-1.5">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background: <?php echo e($colors[$index % count($colors)]); ?>"></span>
                                    <span class="truncate text-muted"><?php echo e($slice['label']); ?></span>
                                </span>
                                <span class="shrink-0 font-semibold tabular-nums text-foreground"><?php echo e($slice['value']); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php else: ?>
                <div class="mt-6 rounded-xl border border-dashed border-border p-6 text-center">
                    <p class="text-sm text-muted">Belum ada pesanan untuk ditampilkan.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-border bg-surface p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-semibold text-foreground">Laris manis</h2>
                    <p class="mt-0.5 text-xs text-muted">Top 5 item terjual</p>
                </div>
                <a href="<?php echo e(route('admin.services.index')); ?>" class="no-print text-xs font-medium text-primary transition hover:underline">Kelola layanan</a>
            </div>

            <?php if($topServices->isEmpty()): ?>
                <div class="mt-5 rounded-xl border border-dashed border-border p-6 text-center">
                    <p class="text-sm text-muted">Belum ada item pesanan.</p>
                </div>
            <?php else: ?>
                <ul class="mt-4 space-y-3">
                    <?php $__currentLoopData = $topServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $maxQty = max((int) $topServices->first()->qty, 1);
                            $width = max(8, (int) round(((int) $item->qty / $maxQty) * 100));
                        ?>
                        <li>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="min-w-0 truncate font-medium text-foreground"><?php echo e($item->name); ?></span>
                                <span class="shrink-0 tabular-nums text-muted"><?php echo e($item->qty); ?> item</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-background">
                                <div class="h-full rounded-full bg-primary" style="width: <?php echo e($width); ?>%"></div>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="mt-4 rounded-2xl border border-border bg-surface p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-semibold text-foreground">Ringkasan status</h2>
                <p class="mt-0.5 text-xs text-muted">Jumlah pesanan &amp; total nilai per status</p>
            </div>
            <span class="rounded-lg bg-primary-soft px-2 py-1 text-xs font-semibold text-primary"><?php echo e($totalOrders); ?> total</span>
        </div>

        <?php if($statusRows->isEmpty()): ?>
            <div class="mt-5 rounded-xl border border-dashed border-border p-6 text-center">
                <p class="text-sm text-muted">Belum ada data pesanan.</p>
            </div>
        <?php else: ?>
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
                        <?php $__currentLoopData = $statusRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="py-2.5 pr-4 font-medium text-foreground"><?php echo e($row['label']); ?></td>
                                <td class="py-2.5 pr-4 text-right tabular-nums text-foreground"><?php echo e($row['count']); ?></td>
                                <td class="py-2.5 text-right tabular-nums text-foreground">Rp <?php echo e(number_format($row['total'], 0, ',', '.')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-border text-base font-bold">
                            <td class="py-3 pr-4 text-foreground">Total</td>
                            <td class="py-3 pr-4 text-right tabular-nums text-foreground"><?php echo e($totalOrders); ?></td>
                            <td class="py-3 text-right tabular-nums text-foreground">Rp <?php echo e(number_format($statusRows->sum('total'), 0, ',', '.')); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>

        <p class="mt-4 text-xs leading-relaxed text-muted">
            Grafik menghitung order dalam <?php echo e($days); ?> hari terakhir.
            Pendapatan KPI dijumlahkan dari status <strong class="text-foreground">Dibayar</strong> sampai
            <strong class="text-foreground">Selesai</strong> (belum termasuk yang dibatalkan).
        </p>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $attributes = $__attributesOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__attributesOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3)): ?>
<?php $component = $__componentOriginal91fdd17964e43374ae18c674f95cdaa3; ?>
<?php unset($__componentOriginal91fdd17964e43374ae18c674f95cdaa3); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/admin/reports/index.blade.php ENDPATH**/ ?>