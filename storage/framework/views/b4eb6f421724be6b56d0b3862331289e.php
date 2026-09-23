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
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Layanan</h1>
        <p class="mt-1.5 text-sm leading-relaxed text-slate-700 sm:text-base">Pilih layanan yang Anda butuhkan. Harga adalah harga contoh.</p>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-2" role="navigation" aria-label="Filter kategori">
            <a href="<?php echo e(route('services.index')); ?>"
               class="inline-flex min-h-11 items-center rounded-lg border px-3.5 text-[15px] font-medium transition <?php echo e($activeCategory === '' && $activeType === '' ? 'border-primary/40 bg-primary-soft text-primary' : 'border-border bg-surface text-foreground hover:border-primary/40 hover:bg-primary-soft hover:text-primary'); ?>">
                Semua
            </a>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('services.index', ['category' => $category->slug])); ?>"
                   class="inline-flex min-h-11 items-center rounded-lg border px-3.5 text-[15px] font-medium transition <?php echo e($activeCategory === $category->slug ? 'border-primary/40 bg-primary-soft text-primary' : 'border-border bg-surface text-foreground hover:border-primary/40 hover:bg-primary-soft hover:text-primary'); ?>">
                    <?php echo e($category->name); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('services.index', ['type' => 'jual'])); ?>"
               class="inline-flex min-h-11 items-center rounded-lg border px-3.5 text-[15px] font-medium transition <?php echo e($activeType === 'jual' ? 'border-primary/40 bg-primary-soft text-primary' : 'border-border bg-surface text-foreground hover:border-primary/40 hover:bg-primary-soft hover:text-primary'); ?>">
                ATK
            </a>
        </div>

        <?php if($services->isEmpty()): ?>
            <div class="mt-6 rounded-2xl border border-dashed border-border bg-surface p-10 text-center">
                <p class="font-medium text-foreground">Belum ada layanan tersedia<?php echo e($activeCategory !== '' ? ' di kategori ini' : ($activeType === 'jual' ? ' untuk ATK' : '')); ?>.</p>
                <p class="mt-1 text-sm text-muted">Silakan pilih kategori lain atau kembali lagi nanti.</p>
                <a href="<?php echo e(route('home')); ?>" class="mt-5 inline-flex min-h-12 items-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                    Ke beranda
                </a>
            </div>
        <?php else: ?>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('services.show', $service)); ?>" class="group flex flex-col rounded-2xl border border-border bg-surface p-5 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex rounded-lg bg-background px-2.5 py-1 text-xs font-medium text-muted transition group-hover:bg-primary-soft group-hover:text-primary">
                                <?php echo e($service->badgeLabel()); ?>

                            </span>
                            <span class="text-[11px] font-semibold uppercase tracking-wide text-primary">Harga contoh</span>
                        </div>
                        <h2 class="mt-4 text-base font-semibold text-foreground transition group-hover:text-primary">
                            <?php echo e($service->name); ?>

                        </h2>
                        <p class="mt-2 line-clamp-3 flex-1 text-[15px] leading-relaxed text-slate-700"><?php echo e($service->description); ?></p>
                        <?php if($service->activeOptions->isNotEmpty()): ?>
                            <p class="mt-3 text-xs font-medium text-primary">Tersedia opsi: <?php echo e($service->activeOptions->pluck('name')->implode(', ')); ?></p>
                        <?php endif; ?>
                        <div class="mt-5 flex items-center justify-between border-t border-border pt-4">
                            <span class="text-sm font-bold tabular-nums text-foreground"><?php echo e($service->formattedPrice()); ?><span class="font-medium text-muted">/<?php echo e($service->unit); ?></span></span>
                            <span class="text-sm font-semibold text-primary">Lihat detail</span>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/services/index.blade.php ENDPATH**/ ?>