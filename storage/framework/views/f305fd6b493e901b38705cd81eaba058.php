<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-border/80 bg-surface/95 backdrop-blur-sm">
    <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-8">
            <a href="<?php echo e(route('home')); ?>" class="group flex shrink-0 items-center gap-2.5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-white shadow-sm transition group-hover:bg-primary-dark">
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
                    <span class="block text-sm font-bold tracking-tight text-foreground">Fotocopy Adhijaya</span>
                    <span class="block text-[11px] font-medium text-muted">Fotokopi & percetakan</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 sm:flex">
                <?php
                    $cartCount = \App\Support\Cart::count();
                    $navItems = [
                        ['label' => 'Beranda', 'route' => 'home', 'active' => request()->routeIs('home')],
                        ['label' => 'Layanan', 'route' => 'services.index', 'active' => request()->routeIs('services.*')],
                        ['label' => 'Kontak', 'route' => 'kontak', 'active' => request()->routeIs('kontak')],
                    ];
                ?>
                <?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e(route($item['route'])); ?>"
                        class="rounded-lg px-3 py-2 text-sm font-medium transition <?php echo e($item['active'] ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>"
                    >
                        <?php echo e($item['label']); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if(auth()->guard()->check()): ?>
                    <?php if(auth()->user()->isAdmin()): ?>
                        <a href="<?php echo e(route('admin.dashboard')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium transition <?php echo e(request()->routeIs('admin.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>">
                            Dashboard admin
                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('orders.index')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium transition <?php echo e(request()->routeIs('orders.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>">
                            Pesanan
                        </a>
                        <a href="<?php echo e(route('profile.edit')); ?>" class="rounded-lg px-3 py-2 text-sm font-medium transition <?php echo e(request()->routeIs('profile.*') ? 'bg-primary-soft text-primary' : 'text-muted hover:bg-background hover:text-foreground'); ?>">
                            Profil
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="hidden items-center gap-2 sm:flex">
            <a href="<?php echo e(route('cart.index')); ?>" class="relative inline-flex min-h-11 items-center gap-2 rounded-lg border border-border bg-surface px-4 text-sm font-medium transition <?php echo e(request()->routeIs('cart.*') ? 'border-primary/40 bg-primary-soft text-primary' : 'text-foreground hover:border-primary/40 hover:bg-primary-soft hover:text-primary'); ?>">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
                Keranjang
                <?php if($cartCount > 0): ?>
                    <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold tabular-nums text-white"><?php echo e($cartCount > 99 ? '99+' : $cartCount); ?></span>
                <?php endif; ?>
            </a>
            <?php if(auth()->guard()->check()): ?>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                        Keluar
                    </button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="inline-flex min-h-11 items-center rounded-lg px-3 text-sm font-medium text-muted transition hover:text-foreground">
                    Masuk
                </a>
                <a href="<?php echo e(route('register')); ?>" class="inline-flex min-h-11 items-center rounded-lg bg-primary px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                    Daftar
                </a>
            <?php endif; ?>
        </div>

        <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-border text-foreground transition hover:bg-background sm:hidden" @click="open = ! open" :aria-expanded="open.toString()" aria-label="Buka menu navigasi">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div :class="{ 'block': open, 'hidden': ! open }" class="hidden border-t border-border bg-surface sm:hidden">
        <div class="space-y-1 px-4 py-3">
            <a href="<?php echo e(route('home')); ?>" class="block rounded-lg px-3 py-3 text-sm font-medium text-foreground transition hover:bg-primary-soft hover:text-primary">
                Beranda
            </a>
            <a href="<?php echo e(route('services.index')); ?>" class="block rounded-lg px-3 py-3 text-sm font-medium text-foreground transition hover:bg-primary-soft hover:text-primary">
                Layanan
            </a>
            <a href="<?php echo e(route('kontak')); ?>" class="block rounded-lg px-3 py-3 text-sm font-medium text-foreground transition hover:bg-primary-soft hover:text-primary">
                Kontak
            </a>
            <a href="<?php echo e(route('cart.index')); ?>" class="flex items-center justify-between rounded-lg px-3 py-3 text-sm font-medium text-foreground transition hover:bg-primary-soft hover:text-primary">
                <span>Keranjang</span>
                <?php if($cartCount > 0): ?>
                    <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1 text-[10px] font-bold tabular-nums text-white"><?php echo e($cartCount > 99 ? '99+' : $cartCount); ?></span>
                <?php endif; ?>
            </a>
            <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->isAdmin()): ?>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="block rounded-lg px-3 py-3 text-sm font-medium text-foreground transition hover:bg-primary-soft hover:text-primary">
                        Dashboard admin
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('orders.index')); ?>" class="block rounded-lg px-3 py-3 text-sm font-medium text-foreground transition hover:bg-primary-soft hover:text-primary">
                        Pesanan
                    </a>
                    <a href="<?php echo e(route('profile.edit')); ?>" class="block rounded-lg px-3 py-3 text-sm font-medium text-foreground transition hover:bg-primary-soft hover:text-primary">
                        Profil
                    </a>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="block w-full rounded-lg px-3 py-3 text-left text-sm font-medium text-foreground transition hover:bg-background">
                        Keluar
                    </button>
                </form>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="block rounded-lg px-3 py-3 text-sm font-medium text-foreground transition hover:bg-background">
                    Masuk
                </a>
                <a href="<?php echo e(route('register')); ?>" class="mt-2 block rounded-lg bg-primary px-3 py-3 text-center text-sm font-semibold text-white transition hover:bg-primary-dark">
                    Daftar
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>