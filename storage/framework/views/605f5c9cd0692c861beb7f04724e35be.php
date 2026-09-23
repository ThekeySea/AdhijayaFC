<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e($title ?? config('app.name', 'Fotocopy Adhijaya')); ?></title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="min-h-screen bg-background text-foreground font-sans antialiased">
        <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden px-4 py-10">
            <div class="pointer-events-none absolute inset-0 paper-grid opacity-60" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -top-20 right-0 h-56 w-56 rounded-full bg-primary-soft" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-16 left-0 h-40 w-40 rounded-full bg-primary/10" aria-hidden="true"></div>

            <div class="relative w-full max-w-md">
                <a href="<?php echo e(route('home')); ?>" class="mb-6 flex items-center justify-center gap-2.5">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white shadow-sm">
                        <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5']); ?>
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
                    <span class="leading-tight">
                        <span class="block text-base font-bold tracking-tight text-foreground"><?php echo e(config('app.name')); ?></span>
                        <span class="block text-xs font-medium text-muted">Fotokopi & percetakan</span>
                    </span>
                </a>

                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm sm:p-8">
                    <?php echo e($slot); ?>

                </div>

                <a href="<?php echo e(route('home')); ?>" class="mt-6 block text-center text-sm text-muted transition hover:text-primary">
                    Kembali ke beranda
                </a>
            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/layouts/guest.blade.php ENDPATH**/ ?>