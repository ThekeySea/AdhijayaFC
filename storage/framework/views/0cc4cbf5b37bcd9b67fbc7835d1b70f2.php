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
        <p class="mt-1.5 text-sm text-muted">Ringkasan pesanan akan tampil di sini.</p>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Pesanan masuk</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">0</p>
            <p class="mt-1 text-sm text-muted">Belum ada data.</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Sedang diproses</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-foreground">0</p>
            <p class="mt-1 text-sm text-muted">Belum ada data.</p>
        </div>
        <div class="rounded-2xl border border-border bg-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Layanan aktif</p>
            <p class="mt-2 text-2xl font-bold tabular-nums text-primary"><?php echo e(\App\Models\Service::query()->active()->count()); ?></p>
            <p class="mt-1 text-sm text-muted">Dari katalog saat ini.</p>
        </div>
    </div>

    <div class="mt-6 rounded-2xl border border-dashed border-border bg-surface p-8 text-center">
        <p class="font-medium text-foreground">Belum ada pesanan masuk.</p>
        <p class="mt-1 text-sm text-muted">Kelola pesanan akan tersedia pada tahap berikutnya.</p>
        <a href="<?php echo e(route('admin.services.index')); ?>" class="mt-5 inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-4 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
            Kelola layanan
        </a>
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