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
        <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Breadcrumb">
            <a href="<?php echo e(route('orders.index')); ?>" class="font-medium text-muted transition hover:text-primary">Pesanan</a>
            <span class="text-border" aria-hidden="true">/</span>
            <span class="font-medium text-foreground"><?php echo e($order->order_number); ?></span>
        </nav>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Detail pesanan</p>
                <h1 class="mt-1 text-balance text-2xl font-bold tracking-tight text-foreground"><?php echo e($order->order_number); ?></h1>
                <p class="mt-1 text-sm text-muted">Dibuat <?php echo e($order->created_at->translatedFormat('d F Y, H:i')); ?></p>
            </div>
            <span class="rounded-lg bg-primary-soft px-3 py-1.5 text-sm font-semibold text-primary">
                <?php echo e($order->status->label()); ?>

            </span>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">Item</h2>
                    <ul class="mt-4 divide-y divide-border">
                        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex flex-wrap items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-foreground"><?php echo e($item->service_name_snapshot); ?></p>
                                    <p class="mt-0.5 text-sm text-muted">
                                        <?php echo e($item->formattedUnitPrice()); ?> × <?php echo e($item->quantity); ?>

                                    </p>
                                    <?php if($item->item_note): ?>
                                        <p class="mt-1 text-sm text-foreground">
                                            <span class="font-medium text-muted">Detail:</span> <?php echo e($item->item_note); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                                <p class="shrink-0 text-sm font-bold tabular-nums text-foreground">
                                    <?php echo e($item->formattedSubtotal()); ?>

                                </p>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>

                    <?php if($order->customer_note): ?>
                        <div class="mt-4 rounded-xl bg-background p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Catatan</p>
                            <p class="mt-1 text-sm leading-relaxed text-foreground"><?php echo e($order->customer_note); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($order->booking): ?>
                    <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                        <h2 class="text-base font-semibold text-foreground">Jadwal</h2>
                        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl bg-background p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Tanggal</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground"><?php echo e($order->booking->formattedDate()); ?></dd>
                            </div>
                            <div class="rounded-xl bg-background p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Slot waktu</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground"><?php echo e($order->booking->time_slot); ?></dd>
                            </div>
                        </dl>
                    </div>
                <?php endif; ?>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm lg:sticky lg:top-24">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Ringkasan</p>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Subtotal</dt>
                            <dd class="font-medium tabular-nums text-foreground"><?php echo e($order->formattedSubtotal()); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Biaya tambahan</dt>
                            <dd class="font-medium tabular-nums text-foreground"><?php echo e(\App\Support\Cart::formatAmount((float) $order->additional_fee)); ?></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-border pt-3">
                            <dt class="font-semibold text-foreground">Total</dt>
                            <dd class="text-lg font-bold tabular-nums text-foreground"><?php echo e($order->formattedTotal()); ?></dd>
                        </div>

                        <?php if($order->hasDownPayment()): ?>
                            <div class="rounded-xl border border-primary-line bg-primary-soft p-3">
                                <p class="text-xs font-semibold text-primary">Uang muka (DP) 50%</p>
                                <p class="mt-1 text-sm font-bold tabular-nums text-foreground">
                                    Tagihan: <?php echo e($order->formattedAmountDue()); ?>

                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-muted">
                                    Sisa <?php echo e($order->formattedRemaining()); ?> dibayar saat ambil di tempat.
                                </p>
                            </div>
                        <?php elseif(in_array($order->status->value, ['PENDING_PAYMENT', 'PAYMENT_FAILED'], true)): ?>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-muted">Tagihan</dt>
                                <dd class="font-bold tabular-nums text-foreground"><?php echo e($order->formattedAmountDue()); ?></dd>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Pembayaran</dt>
                            <dd class="font-medium text-foreground"><?php echo e($order->payment_status->label()); ?></dd>
                        </div>
                    </dl>

                    <?php if(
                        ! auth()->user()->isAdmin()
                        && in_array($order->status->value, ['PENDING_PAYMENT', 'PAYMENT_FAILED'], true)
                        && $order->payment_status->value !== 'PAID'
                    ): ?>
                        <button
                            type="button"
                            id="pay-now"
                            data-order="<?php echo e($order->id); ?>"
                            data-pay-url="<?php echo e(route('orders.pay', $order)); ?>"
                            data-amount-label="<?php echo e($order->formattedAmountDue()); ?>"
                            class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background"
                        >
                            Bayar sekarang — <?php echo e($order->formattedAmountDue()); ?>

                        </button>
                        <p class="mt-2 text-center text-xs leading-relaxed text-muted">
                            Metode: QRIS atau transfer bank. Setelah bayar, status diperbarui otomatis dari server.
                        </p>
                    <?php endif; ?>

                    <?php if($order->status->canBeCancelled() && ! auth()->user()->isAdmin()): ?>
                        <form method="POST" action="<?php echo e(route('orders.cancel', $order)); ?>" class="mt-4" data-confirm="Batalkan pesanan ini?">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="inline-flex min-h-12 w-full items-center justify-center rounded-lg border border-red-200 bg-surface px-5 text-sm font-semibold text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                Batalkan pesanan
                            </button>
                        </form>
                    <?php endif; ?>

                    <p class="mt-4 border-t border-border pt-4 text-xs leading-relaxed text-muted">
                        Pembayaran diproses Midtrans (sandbox). Hubungi admin bila ada perubahan kebutuhan.
                    </p>
                </div>
            </aside>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script src="<?php echo e(config('midtrans.snap_js')); ?>" data-client-key="<?php echo e(config('midtrans.client_key')); ?>" defer></script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/orders/show.blade.php ENDPATH**/ ?>