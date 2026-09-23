<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e($title ?? config('app.name', 'Fotocopy Adhijaya')); ?></title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="min-h-screen w-full max-w-full bg-background text-foreground font-sans antialiased">
        <?php echo $__env->make('layouts.navigation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php if (isset($component)) { $__componentOriginal91530f48093dbfe9d7d6cfefc4ce84c1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal91530f48093dbfe9d7d6cfefc4ce84c1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.bottom-nav','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bottom-nav'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal91530f48093dbfe9d7d6cfefc4ce84c1)): ?>
<?php $attributes = $__attributesOriginal91530f48093dbfe9d7d6cfefc4ce84c1; ?>
<?php unset($__attributesOriginal91530f48093dbfe9d7d6cfefc4ce84c1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal91530f48093dbfe9d7d6cfefc4ce84c1)): ?>
<?php $component = $__componentOriginal91530f48093dbfe9d7d6cfefc4ce84c1; ?>
<?php unset($__componentOriginal91530f48093dbfe9d7d6cfefc4ce84c1); ?>
<?php endif; ?>
        <?php if (isset($component)) { $__componentOriginal52d409fc1fa687ab461fb439e195b906 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal52d409fc1fa687ab461fb439e195b906 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.whatsapp-float','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('whatsapp-float'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal52d409fc1fa687ab461fb439e195b906)): ?>
<?php $attributes = $__attributesOriginal52d409fc1fa687ab461fb439e195b906; ?>
<?php unset($__attributesOriginal52d409fc1fa687ab461fb439e195b906); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal52d409fc1fa687ab461fb439e195b906)): ?>
<?php $component = $__componentOriginal52d409fc1fa687ab461fb439e195b906; ?>
<?php unset($__componentOriginal52d409fc1fa687ab461fb439e195b906); ?>
<?php endif; ?>

        <?php if(isset($header)): ?>
            <header class="border-b border-border bg-surface">
                <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                    <?php echo e($header); ?>

                </div>
            </header>
        <?php endif; ?>

        <main class="w-full max-w-full">
            <?php if(session('status')): ?>
                <div class="mx-auto max-w-6xl px-4 pt-6 sm:px-6 lg:px-8">
                    <div class="rounded-lg border border-primary-line bg-primary-soft px-4 py-3 text-sm font-medium text-primary-dark" role="status">
                        <?php echo e(session('status')); ?>

                    </div>
                </div>
            <?php endif; ?>
            <?php echo e($slot); ?>

        </main>

        <footer class="mt-16 w-full max-w-full bg-linear-to-b from-[#0f172a] to-[#1a2740] pb-[calc(5.5rem+env(safe-area-inset-bottom))] text-white sm:pb-0">
            <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3 lg:px-8">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-white ring-1 ring-white/25">
                            <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'h-5 w-5 text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-white']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                        </span>
                        <span class="text-sm font-bold tracking-tight text-white"><?php echo e(config('app.name')); ?></span>
                    </div>
                    <p class="mt-3 max-w-xs text-sm leading-relaxed text-slate-200/90">
                            <?php echo e(\App\Models\BusinessSetting::current()->tagline
                                ?: 'Jasa fotokopi dan percetakan untuk kebutuhan sekolah, kantor, dan keperluan umum.'); ?>

                        </p>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white">Navigasi</p>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="<?php echo e(route('home')); ?>" class="text-slate-200/90 transition hover:text-white">Beranda</a></li>
                        <li><a href="<?php echo e(route('services.index')); ?>" class="text-slate-200/90 transition hover:text-white">Layanan</a></li>
                        <li><a href="<?php echo e(route('kontak')); ?>" class="text-slate-200/90 transition hover:text-white">Kontak</a></li>
                        <li><a href="<?php echo e(route('cart.index')); ?>" class="text-slate-200/90 transition hover:text-white">Keranjang</a></li>
                        <li><a href="<?php echo e(route('orders.index')); ?>" class="text-slate-200/90 transition hover:text-white">Pesanan</a></li>
                    </ul>
                </div>

                <div>
                    <p class="text-sm font-semibold text-white">Catatan</p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-200/90">
                        Harga di situs ini adalah harga contoh dan dapat diubah oleh admin.
                        Situs contoh untuk keperluan akademik.
                    </p>
                </div>
            </div>
            <div class="border-t border-white/15">
                <div class="mx-auto max-w-6xl px-4 py-4 text-xs text-slate-300 sm:px-6 lg:px-8">
                    &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>.
                </div>
            </div>
        </footer>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/layouts/app.blade.php ENDPATH**/ ?>