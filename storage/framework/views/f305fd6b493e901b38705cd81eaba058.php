<?php
    $cartCount = \App\Support\Cart::count();
?>

<nav class="fixed inset-x-0 top-0 z-40 w-full max-w-full border-b border-border/60 bg-background/90 backdrop-blur-sm">
    <div class="mx-auto max-w-6xl px-3 pb-2.5 pt-3 sm:px-6 lg:px-8">
        
        <div class="flex items-center justify-between gap-2">
            <div class="flex min-w-0 flex-1 items-center gap-0.5 rounded-full border border-border bg-surface px-1.5 py-1.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.95),0_10px_24px_-16px_rgba(15,23,42,0.25)] sm:gap-1 sm:px-2">
                <a
                    href="<?php echo e(route('home')); ?>"
                    class="group inline-flex min-h-11 shrink-0 items-center gap-2 rounded-full px-1.5 transition hover:bg-background sm:gap-2.5 sm:px-2"
                >
                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary text-white shadow-sm transition group-hover:bg-primary-dark">
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
                        <span class="block truncate text-[13px] font-bold tracking-tight text-foreground sm:text-sm">Fotocopy Adhijaya</span>
                        <span class="hidden truncate text-[11px] font-medium text-muted lg:block">Fotokopi & percetakan</span>
                    </span>
                </a>

                
                <div class="hidden min-w-0 items-center sm:flex">
                    <span class="mx-1 h-6 w-px bg-border" aria-hidden="true"></span>

                    <a
                        href="<?php echo e(route('home')); ?>"
                        class="rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 <?php echo e(request()->routeIs('home') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>"
                    >
                        Beranda
                    </a>
                    <a
                        href="<?php echo e(route('services.index')); ?>"
                        class="rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 <?php echo e(request()->routeIs('services.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>"
                    >
                        Layanan
                    </a>
                    <a
                        href="<?php echo e(route('cart.index')); ?>"
                        class="relative inline-flex items-center gap-2 rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 <?php echo e(request()->routeIs('cart.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>"
                    >
                        Keranjang
                        <?php if($cartCount > 0): ?>
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold tabular-nums text-white"><?php echo e($cartCount > 99 ? '99+' : $cartCount); ?></span>
                        <?php endif; ?>
                    </a>

                    <span class="mx-1 h-6 w-px bg-border" aria-hidden="true"></span>

                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->isAdmin()): ?>
                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 <?php echo e(request()->routeIs('admin.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>">
                                Dashboard admin
                            </a>
                        <?php else: ?>
                            <a href="<?php echo e(route('orders.index')); ?>" class="rounded-full px-2.5 py-2 text-sm font-semibold transition lg:px-3.5 <?php echo e(request()->routeIs('orders.*') || request()->routeIs('profile.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>">
                                Pesanan
                            </a>
                        <?php endif; ?>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-full px-2.5 py-2 text-sm font-semibold text-muted transition hover:bg-background hover:text-foreground lg:px-3.5">
                                Keluar
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="rounded-full px-2.5 py-2 text-sm font-semibold text-muted transition hover:bg-background hover:text-foreground lg:px-3.5">
                            Masuk
                        </a>
                        <a href="<?php echo e(route('register')); ?>" class="ms-0.5 inline-flex min-h-11 items-center rounded-full bg-primary px-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark lg:ms-1 lg:px-4">
                            Daftar
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <a
                href="<?php echo e(route('kontak')); ?>"
                class="inline-flex min-h-12 shrink-0 items-center gap-1.5 rounded-full border border-border bg-surface px-3 text-[13px] font-bold text-foreground shadow-[inset_0_1px_0_rgba(255,255,255,0.95),0_10px_24px_-16px_rgba(15,23,42,0.25)] transition hover:border-primary/35 hover:bg-primary-soft hover:text-primary sm:gap-2 sm:px-4 sm:text-sm <?php echo e(request()->routeIs('kontak') ? 'border-primary/35 bg-primary-soft text-primary' : ''); ?>"
                <?php if(request()->routeIs('kontak')): ?> aria-current="page" <?php endif; ?>
            >
                <svg class="h-4 w-4 shrink-0 sm:h-4.5 sm:w-4.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
                Kontak
            </a>
        </div>
    </div>
</nav>


<div class="h-[76px]" aria-hidden="true"></div>
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>