<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Pesanan</p>
                <h1 class="mt-1 text-balance text-2xl font-bold tracking-tight text-foreground">Riwayat pesanan</h1>
            </div>
            <a href="<?php echo e(route('services.index')); ?>" class="text-sm font-medium text-muted transition hover:text-primary">
                Pesan lagi
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <?php if($orders->isEmpty()): ?>
            <div class="rounded-2xl border border-border bg-surface p-10 text-center">
                <h2 class="text-lg font-semibold text-foreground">Belum ada pesanan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-muted">
                    Pesanan yang kamu buat akan tampil di sini beserta statusnya.
                </p>
                <a href="<?php echo e(route('services.index')); ?>" class="mt-6 inline-flex min-h-12 items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                    Lihat layanan
                </a>
            </div>
        <?php else: ?>
            <ul class="space-y-4">
                <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e(route('orders.show', $order)); ?>" class="block rounded-2xl border border-border bg-surface p-5 transition hover:border-primary/40 hover:shadow-sm sm:p-6">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="text-base font-semibold text-foreground"><?php echo e($order->order_number); ?></p>
                                    <p class="mt-1 text-sm text-muted">
                                        <?php echo e($order->created_at->translatedFormat('d M Y, H:i')); ?>

                                        · <?php echo e($order->items_count); ?> item
                                        <?php if($order->booking): ?>
                                            · Ambil <?php echo e($order->booking->formattedDate()); ?>

                                        <?php endif; ?>
                                    </p>
                                    <p class="mt-1 text-sm text-foreground">
                                        <?php echo e($order->items->pluck('service_name_snapshot')->take(3)->join(', ')); ?>

                                        <?php if($order->items_count > 3): ?>
                                            <span class="text-muted">dan lainnya</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <span class="rounded-lg bg-primary-soft px-2.5 py-1 text-xs font-semibold text-primary">
                                        <?php echo e($order->status->label()); ?>

                                    </span>
                                    <p class="text-base font-bold tabular-nums text-foreground"><?php echo e($order->formattedTotal()); ?></p>
                                </div>
                            </div>
                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>

            <div class="mt-6">
                <?php echo e($orders->links()); ?>

            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/orders/index.blade.php ENDPATH**/ ?>