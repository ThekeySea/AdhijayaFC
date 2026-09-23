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
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Hubungi kami</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Kontak</h1>
        <p class="mt-1.5 text-sm text-muted">Hubungi kami untuk pertanyaan dan kebutuhan khusus.</p>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-2xl border border-border bg-surface p-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary-soft text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            <circle cx="12" cy="12" r="9" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-foreground">Jam operasional</h2>
                        <p class="text-sm text-muted">Dikonfirmasi oleh pemilik usaha.</p>
                    </div>
                </div>
                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between gap-4 rounded-lg bg-background px-3 py-2.5">
                        <dt class="text-muted">Senin – Sabtu</dt>
                        <dd class="font-semibold tabular-nums text-foreground">08.00 – 20.00</dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-lg bg-background px-3 py-2.5">
                        <dt class="text-muted">Minggu</dt>
                        <dd class="font-semibold text-muted">Tutup</dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs text-muted">Jam di atas bersifat contoh sampai dikonfirmasi.</p>
            </div>

            <div class="rounded-2xl border border-border bg-surface p-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary-soft text-primary">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 0 1-4-.84L3 20l1.05-3.15A7.96 7.96 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-foreground">WhatsApp</h2>
                        <p class="text-sm text-muted">Pesan terbuka di WhatsApp.</p>
                    </div>
                </div>

                <?php if($whatsappUrl): ?>
                    <a href="<?php echo e($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex min-h-12 items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                        Hubungi admin
                    </a>
                <?php else: ?>
                    <div class="mt-5 rounded-xl border border-dashed border-border bg-background p-4">
                        <p class="text-sm text-muted">Nomor WhatsApp belum dikonfigurasi. Hubungi admin melalui kanal resmi toko.</p>
                    </div>
                <?php endif; ?>

                <div class="mt-6 border-t border-border pt-4">
                    <h3 class="text-sm font-semibold text-foreground">Alamat toko</h3>
                    <p class="mt-1 text-sm text-muted">Alamat akan ditampilkan setelah dikonfirmasi pemilik usaha.</p>
                </div>
            </div>
        </div>
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
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/kontak.blade.php ENDPATH**/ ?>