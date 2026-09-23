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
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Layanan</h1>
                <p class="mt-1.5 text-sm text-muted">Kelola katalog dan harga layanan.</p>
            </div>
            <a href="<?php echo e(route('admin.services.create')); ?>" class="inline-flex min-h-12 items-center rounded-lg bg-primary px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                Tambah layanan
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <?php if(session('status')): ?>
        <div class="mb-4 rounded-xl border border-primary-line bg-primary-soft px-4 py-3 text-sm font-medium text-foreground">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-2xl border border-border bg-surface">
        <?php if($services->isEmpty()): ?>
            <div class="p-10 text-center">
                <p class="font-medium text-foreground">Belum ada layanan.</p>
                <p class="mt-1 text-sm text-muted">Tambahkan layanan pertama Anda.</p>
            </div>
        <?php else: ?>
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-background text-xs font-semibold uppercase tracking-wide text-muted">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Harga</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="transition hover:bg-background/60">
                                <td class="px-4 py-3 font-medium text-foreground"><?php echo e($service->name); ?></td>
                                <td class="px-4 py-3 text-muted"><?php echo e($service->badgeLabel()); ?></td>
                                <td class="px-4 py-3 font-medium tabular-nums text-foreground">
                                    <?php echo e($service->formattedPrice()); ?>/<?php echo e($service->unit); ?>

                                    <?php if($service->priceTiers->isNotEmpty()): ?>
                                        <span class="mt-0.5 block text-[11px] font-medium text-muted"><?php echo e($service->priceTiers->count()); ?> tier harga</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-lg px-2 py-1 text-xs font-semibold <?php echo e($service->is_active ? 'bg-primary-soft text-primary' : 'bg-background text-muted'); ?>">
                                        <?php echo e($service->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="<?php echo e(route('admin.services.edit', $service)); ?>" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                                            Ubah
                                        </a>
                                        <form method="POST" action="<?php echo e(route('admin.services.toggle', $service)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:bg-background">
                                                <?php echo e($service->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                                            </button>
                                        </form>
                                        <form method="POST" action="<?php echo e(route('admin.services.destroy', $service)); ?>" onsubmit="return confirm('Hapus layanan ini?')">
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

            <ul class="divide-y divide-border md:hidden">
                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-foreground"><?php echo e($service->name); ?></p>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-muted"><?php echo e($service->badgeLabel()); ?></p>
                                <p class="mt-1 text-sm font-medium tabular-nums text-muted">
                                    <?php echo e($service->formattedPrice()); ?>/<?php echo e($service->unit); ?>

                                    <?php if($service->priceTiers->isNotEmpty()): ?>
                                        · <?php echo e($service->priceTiers->count()); ?> tier
                                    <?php endif; ?>
                                </p>
                                <span class="mt-2 inline-flex rounded-lg px-2 py-1 text-xs font-semibold <?php echo e($service->is_active ? 'bg-primary-soft text-primary' : 'bg-background text-muted'); ?>">
                                    <?php echo e($service->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                </span>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="<?php echo e(route('admin.services.edit', $service)); ?>" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground">
                                Ubah
                            </a>
                            <form method="POST" action="<?php echo e(route('admin.services.toggle', $service)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground">
                                    <?php echo e($service->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('admin.services.destroy', $service)); ?>" onsubmit="return confirm('Hapus layanan ini?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="inline-flex min-h-11 items-center rounded-lg border border-red-200 px-3 text-xs font-medium text-red-600">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
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
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/admin/services/index.blade.php ENDPATH**/ ?>