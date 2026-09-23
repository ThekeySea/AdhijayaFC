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
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Admin</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Dashboard</h1>
        <p class="mt-1.5 text-sm text-muted">Ringkasan operasional hari ini.</p>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Menunggu pembayaran</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground"><?php echo e($pendingCount); ?></p>
            <a href="<?php echo e(route('admin.orders.index', ['status' => 'PENDING_PAYMENT'])); ?>" class="mt-2 inline-block text-xs font-medium text-primary transition hover:underline">Lihat pesanan</a>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Perlu diproses</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground"><?php echo e($paidCount); ?></p>
            <a href="<?php echo e(route('admin.orders.index', ['status' => 'PAID'])); ?>" class="mt-2 inline-block text-xs font-medium text-primary transition hover:underline">Lihat pesanan</a>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Sedang diproses</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground"><?php echo e($processingCount); ?></p>
            <a href="<?php echo e(route('admin.orders.index', ['status' => 'PROCESSING'])); ?>" class="mt-2 inline-block text-xs font-medium text-primary transition hover:underline">Lihat pesanan</a>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Siap diambil</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground"><?php echo e($readyCount); ?></p>
            <a href="<?php echo e(route('admin.orders.index', ['status' => 'READY'])); ?>" class="mt-2 inline-block text-xs font-medium text-primary transition hover:underline">Lihat pesanan</a>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-foreground">Pesanan terbaru</h2>
                <a href="<?php echo e(route('admin.orders.index')); ?>" class="text-sm font-medium text-primary transition hover:underline">Semua pesanan</a>
            </div>

            <?php if($recentOrders->isEmpty()): ?>
                <div class="mt-6 rounded-xl border border-dashed border-border p-6 text-center">
                    <p class="font-medium text-foreground">Belum ada pesanan masuk.</p>
                    <p class="mt-1 text-sm text-muted">Pesanan pelanggan akan tampil di sini.</p>
                </div>
            <?php else: ?>
                <ul class="mt-4 divide-y divide-border">
                    <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>
                            <a href="<?php echo e(route('admin.orders.show', $order)); ?>" class="flex flex-wrap items-center justify-between gap-3 py-3 transition hover:bg-background/60">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-foreground"><?php echo e($order->order_number); ?></p>
                                    <p class="mt-0.5 text-sm text-muted">
                                        <?php echo e($order->customer?->name ?? '—'); ?> · <?php echo e($order->items_count); ?> item
                                    </p>
                                </div>
                                <div class="flex shrink-0 flex-col items-end gap-1">
                                    <span class="rounded-lg bg-primary-soft px-2 py-1 text-xs font-semibold text-primary">
                                        <?php echo e($order->status->label()); ?>

                                    </span>
                                    <span class="text-sm font-bold tabular-nums text-foreground"><?php echo e($order->formattedTotal()); ?></span>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-base font-semibold text-foreground">Booking mendatang</h2>
                <p class="text-xs font-semibold uppercase tracking-wide text-muted"><?php echo e($activeServices); ?> layanan aktif</p>
            </div>

            <?php if($upcomingBookings->isEmpty()): ?>
                <div class="mt-6 rounded-xl border border-dashed border-border p-6 text-center">
                    <p class="font-medium text-foreground">Belum ada booking mendatang.</p>
                    <p class="mt-1 text-sm text-muted">Jadwal ambil pelanggan akan tampil di sini.</p>
                </div>
            <?php else: ?>
                <ul class="mt-4 divide-y divide-border">
                    <?php $__currentLoopData = $upcomingBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="py-3 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-foreground"><?php echo e($booking->formattedDate()); ?> · <?php echo e($booking->time_slot); ?></p>
                                    <p class="mt-0.5 text-sm text-muted"><?php echo e($booking->customer?->name ?? '—'); ?></p>
                                    <?php if($booking->orders->first()): ?>
                                        <a href="<?php echo e(route('admin.orders.show', $booking->orders->first())); ?>" class="mt-1 inline-block text-xs font-medium text-primary transition hover:underline">
                                            <?php echo e($booking->orders->first()->order_number); ?>

                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php if($booking->note): ?>
                                    <p class="max-w-xs text-xs leading-relaxed text-muted"><?php echo e($booking->note); ?></p>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-border bg-surface p-5 sm:p-6">
        <h2 class="text-base font-semibold text-foreground">Kelola</h2>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="<?php echo e(route('admin.orders.index')); ?>" class="inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                Kelola pesanan
            </a>
            <a href="<?php echo e(route('admin.services.index')); ?>" class="inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                Kelola layanan
            </a>
            <a href="<?php echo e(route('admin.categories.index')); ?>" class="inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                Kelola kategori
            </a>
        </div>
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
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>