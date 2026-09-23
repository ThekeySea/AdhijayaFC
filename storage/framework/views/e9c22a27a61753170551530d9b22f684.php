<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e($title ?? 'Admin - '.config('app.name', 'Fotocopy Adhijaya')); ?></title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    </head>
    <body class="min-h-screen bg-background text-foreground font-sans antialiased">
        <div class="min-h-screen lg:flex">
            <aside class="border-b border-slate-800 bg-foreground lg:sticky lg:top-0 lg:h-screen lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r lg:border-slate-800">
                <div class="flex h-16 items-center justify-between gap-3 px-4 lg:h-auto lg:border-b lg:border-white/10 lg:px-5 lg:py-5">
                    <a href="<?php echo e(route('home')); ?>" class="flex min-w-0 items-center gap-2.5">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary text-white">
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
                        <span class="min-w-0 leading-tight">
                            <span class="block truncate text-sm font-bold text-white"><?php echo e(config('app.name')); ?></span>
                            <span class="block text-[11px] font-semibold uppercase tracking-wide text-blue-300">Admin</span>
                        </span>
                    </a>
                    <span class="rounded-lg bg-white/10 px-2 py-1 text-xs font-semibold text-white lg:hidden">Admin</span>
                </div>

                <nav class="hidden space-y-1 p-3 lg:block">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-white transition <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Dashboard
                    </a>
                    <a href="<?php echo e(route('admin.reports.index')); ?>" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-white transition <?php echo e(request()->routeIs('admin.reports.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Laporan
                    </a>
                    <a href="<?php echo e(route('admin.orders.index')); ?>" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-white transition <?php echo e(request()->routeIs('admin.orders.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Pesanan
                    </a>
                    <a href="<?php echo e(route('admin.services.index')); ?>" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-white transition <?php echo e(request()->routeIs('admin.services.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Layanan
                    </a>
                    <a href="<?php echo e(route('admin.categories.index')); ?>" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-white transition <?php echo e(request()->routeIs('admin.categories.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Kategori
                    </a>
                    <a href="<?php echo e(route('admin.business-settings.edit')); ?>" class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium text-white transition <?php echo e(request()->routeIs('admin.business-settings.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Info Usaha
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-white transition hover:bg-white/10">
                            Keluar
                        </button>
                    </form>
                </nav>

                <nav class="flex gap-1 overflow-x-auto border-t border-white/10 px-3 py-2 lg:hidden">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition <?php echo e(request()->routeIs('admin.dashboard') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Dashboard
                    </a>
                    <a href="<?php echo e(route('admin.reports.index')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition <?php echo e(request()->routeIs('admin.reports.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Laporan
                    </a>
                    <a href="<?php echo e(route('admin.orders.index')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition <?php echo e(request()->routeIs('admin.orders.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Pesanan
                    </a>
                    <a href="<?php echo e(route('admin.services.index')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition <?php echo e(request()->routeIs('admin.services.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Layanan
                    </a>
                    <a href="<?php echo e(route('admin.categories.index')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition <?php echo e(request()->routeIs('admin.categories.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Kategori
                    </a>
                    <a href="<?php echo e(route('admin.business-settings.edit')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition <?php echo e(request()->routeIs('admin.business-settings.*') ? 'bg-white/20' : 'hover:bg-white/10'); ?>">
                        Info Usaha
                    </a>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap text-white transition hover:bg-white/10">
                            Keluar
                        </button>
                    </form>
                </nav>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="border-b border-border bg-surface">
                    <div class="px-4 py-5 sm:px-6 lg:px-8">
                        <?php echo e($header ?? ''); ?>

                    </div>
                </header>

                <main class="px-4 py-6 sm:px-6 lg:px-8">
                    <?php echo e($slot); ?>

                </main>
            </div>
        </div>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/layouts/admin.blade.php ENDPATH**/ ?>