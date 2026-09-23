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
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Keranjang</p>
                <h1 class="mt-1 text-balance text-2xl font-bold tracking-tight text-foreground">Keranjang belanja</h1>
            </div>
            <a href="<?php echo e(route('services.index')); ?>" class="text-sm font-medium text-muted transition hover:text-primary">
                Tambah layanan lain
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <?php if($lines->isEmpty()): ?>
            <div class="rounded-2xl border border-border bg-surface p-10 text-center">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-soft text-primary">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>
                </span>
                <h2 class="mt-4 text-lg font-semibold text-foreground">Keranjang masih kosong</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-muted">
                    Pilih layanan yang kamu butuhkan, lalu tambahkan ke keranjang sebelum lanjut checkout.
                </p>
                <a href="<?php echo e(route('services.index')); ?>" class="mt-6 inline-flex min-h-12 items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                    Lihat layanan
                </a>
            </div>
        <?php else: ?>
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-4 lg:col-span-2">
                    <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6" x-data>
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="<?php echo e(route('services.show', $line['service'])); ?>" class="text-base font-semibold text-foreground transition hover:text-primary">
                                        <?php echo e($line['service']->name); ?>

                                    </a>
                                    <p class="mt-1 text-sm text-muted">
                                        <?php echo e(\App\Support\Cart::formatAmount($line['price'])); ?> per <?php echo e($line['service']->unit); ?>

                                    </p>
                                    <?php if($line['detail'] !== ''): ?>
                                        <p class="mt-2 rounded-lg bg-background px-3 py-2 text-sm text-foreground">
                                            <span class="font-medium text-muted">Detail:</span> <?php echo e($line['detail']); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                                <p class="shrink-0 text-base font-bold tabular-nums text-foreground">
                                    <?php echo e(\App\Support\Cart::formatAmount((float) $line['subtotal'])); ?>

                                </p>
                            </div>

                            <div class="mt-4 flex flex-wrap items-end gap-3 border-t border-border pt-4">
                                <form method="POST" action="<?php echo e(route('cart.update', $line['service'])); ?>" class="flex items-end gap-3">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PATCH'); ?>
                                    <div>
                                        <label for="quantity-<?php echo e($line['service']->id); ?>" class="block text-xs font-semibold uppercase tracking-wide text-muted">Jumlah (<?php echo e($line['service']->unit); ?>)</label>
                                        <input
                                            id="quantity-<?php echo e($line['service']->id); ?>"
                                            type="number"
                                            name="quantity"
                                            min="1"
                                            max="9999"
                                            value="<?php echo e($line['quantity']); ?>"
                                            class="mt-1 w-24 rounded-lg border border-border bg-surface px-3 py-2 text-sm text-foreground transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        >
                                    </div>
                                    <div class="flex-1 min-w-40">
                                        <label for="detail-<?php echo e($line['service']->id); ?>" class="block text-xs font-semibold uppercase tracking-wide text-muted">Detail pengerjaan</label>
                                        <input
                                            id="detail-<?php echo e($line['service']->id); ?>"
                                            type="text"
                                            name="detail"
                                            maxlength="500"
                                            value="<?php echo e($line['detail']); ?>"
                                            placeholder="misal: A4, 2 sisi, jilid kiri"
                                            class="mt-1 w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-foreground transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                        >
                                    </div>
                                    <?php if (isset($component)) { $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.secondary-button','data' => ['type' => 'submit','class' => 'mb-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('secondary-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'mb-0.5']); ?>Simpan <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $attributes = $__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__attributesOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af)): ?>
<?php $component = $__componentOriginal3b0e04e43cf890250cc4d85cff4d94af; ?>
<?php unset($__componentOriginal3b0e04e43cf890250cc4d85cff4d94af); ?>
<?php endif; ?>
                                </form>

                                <form method="POST" action="<?php echo e(route('cart.destroy', $line['service'])); ?>">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-medium text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                        Hapus
                                    </button>
                                </form>
                            </div>

                            <?php if($errors->any() && $errors->first() !== ''): ?>
                                <?php if (isset($component)) { $__componentOriginalf94ed9c5393ef72725d159fe01139746 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf94ed9c5393ef72725d159fe01139746 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-error','data' => ['messages' => $errors->all(),'class' => 'mt-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['messages' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->all()),'class' => 'mt-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $attributes = $__attributesOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__attributesOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf94ed9c5393ef72725d159fe01139746)): ?>
<?php $component = $__componentOriginalf94ed9c5393ef72725d159fe01139746; ?>
<?php unset($__componentOriginalf94ed9c5393ef72725d159fe01139746); ?>
<?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <aside class="lg:col-span-1">
                    <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm lg:sticky lg:top-24">
                        <p class="text-xs font-semibold uppercase tracking-wide text-primary">Ringkasan</p>

                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-muted">Jumlah item</dt>
                                <dd class="font-medium tabular-nums text-foreground"><?php echo e($lines->sum('quantity')); ?></dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 border-t border-border pt-3">
                                <dt class="font-semibold text-foreground">Subtotal</dt>
                                <dd class="text-lg font-bold tabular-nums text-foreground"><?php echo e(\App\Support\Cart::formatAmount($subtotal)); ?></dd>
                            </div>
                        </dl>

                        <?php if(auth()->guard()->check()): ?>
                            <?php if(auth()->user()->isAdmin()): ?>
                                <p class="mt-6 text-center text-xs text-muted">Akun admin tidak melakukan checkout.</p>
                            <?php else: ?>
                                <a href="<?php echo e(route('checkout.create')); ?>" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                    Lanjut checkout
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo e(route('login')); ?>" class="mt-6 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                                Masuk untuk checkout
                            </a>
                            <p class="mt-3 text-center text-xs text-muted">
                                Belum punya akun? <a href="<?php echo e(route('register')); ?>" class="font-semibold text-primary hover:underline">Daftar</a>
                            </p>
                        <?php endif; ?>

                        <p class="mt-4 border-t border-border pt-4 text-xs leading-relaxed text-muted">
                            Total dihitung ulang dari harga terbaru di sistem. Harga bersifat contoh dan dapat berubah.
                        </p>
                    </div>
                </aside>
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
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/cart/index.blade.php ENDPATH**/ ?>