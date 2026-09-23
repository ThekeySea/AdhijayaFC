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
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Kategori jasa</h1>
                <p class="mt-1.5 text-sm text-muted">Kelola kategori yang dipakai di katalog dan beranda.</p>
            </div>
            <a href="<?php echo e(route('admin.categories.create')); ?>" class="inline-flex min-h-12 items-center rounded-lg bg-primary px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                Tambah kategori
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <?php if(session('status')): ?>
        <div class="mb-4 rounded-xl border border-primary-line bg-primary-soft px-4 py-3 text-sm font-medium text-foreground">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-2xl border border-border bg-surface">
        <?php if($categories->isEmpty()): ?>
            <div class="p-10 text-center">
                <p class="font-medium text-foreground">Belum ada kategori.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-background text-xs font-semibold uppercase tracking-wide text-muted">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Slug</th>
                            <th class="px-4 py-3">Urutan</th>
                            <th class="px-4 py-3">Layanan aktif</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="transition hover:bg-background/60">
                                <td class="px-4 py-3 font-medium text-foreground"><?php echo e($category->name); ?></td>
                                <td class="px-4 py-3 text-muted"><?php echo e($category->slug); ?></td>
                                <td class="px-4 py-3 tabular-nums text-muted"><?php echo e($category->sort_order); ?></td>
                                <td class="px-4 py-3 tabular-nums text-muted"><?php echo e($category->services_count); ?></td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-lg px-2 py-1 text-xs font-semibold <?php echo e($category->is_active ? 'bg-primary-soft text-primary' : 'bg-background text-muted'); ?>">
                                        <?php echo e($category->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="<?php echo e(route('admin.categories.edit', $category)); ?>" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                                            Ubah
                                        </a>
                                        <form method="POST" action="<?php echo e(route('admin.categories.toggle', $category)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:bg-background">
                                                <?php echo e($category->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                                            </button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route('admin.categories.destroy', $category)); ?>" onsubmit="return confirm('Hapus kategori ini? Layanan di dalamnya menjadi tanpa kategori.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-red-200 px-3 text-xs font-medium text-red-600 transition hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>