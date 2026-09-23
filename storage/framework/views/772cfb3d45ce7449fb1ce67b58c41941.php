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
    <section class="relative overflow-hidden border-b border-border bg-surface">
        <div class="pointer-events-none absolute inset-0 paper-grid opacity-50" aria-hidden="true"></div>
        <div class="pointer-events-none absolute right-0 top-0 h-48 w-48 rounded-full bg-primary-soft sm:h-72 sm:w-72" aria-hidden="true"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/3 h-40 w-40 rounded-full bg-primary/10 sm:h-56 sm:w-56" aria-hidden="true"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20 lg:px-8">
            <div class="inline-flex items-center gap-2 rounded-lg border border-primary-line bg-primary-soft px-3 py-1.5 text-xs font-semibold text-primary">
                <span class="h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true"></span>
                Jasa fotokopi & percetakan
            </div>

            <h1 class="mt-5 max-w-2xl text-balance text-3xl font-bold leading-[1.15] tracking-tight text-foreground sm:text-5xl">
                Pesan layanan fotokopi tanpa chat berulang
            </h1>
            <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-700 sm:text-lg">
                Pilih layanan, isi detail pekerjaan, unggah file, lalu checkout. Status pesanan bisa dipantau langsung dari akun Anda.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="<?php echo e(route('services.index')); ?>" class="inline-flex min-h-12 items-center rounded-lg bg-primary px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                    Lihat layanan
                </a>
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('services.index', ['category' => 'digital-print'])); ?>" class="inline-flex min-h-12 items-center rounded-lg border border-border bg-surface px-6 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                        Mulai cetak
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('register')); ?>" class="inline-flex min-h-12 items-center rounded-lg border border-border bg-surface px-6 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                        Buat akun
                    </a>
                <?php endif; ?>
            </div>

            <dl class="mt-10 grid max-w-2xl gap-3 sm:grid-cols-3">
                <div class="rounded-xl border border-border bg-surface/80 px-4 py-3">
                    <dt class="text-xs font-medium text-muted">1. Pilih layanan</dt>
                    <dd class="mt-1 text-sm font-semibold text-foreground">Katalog jelas</dd>
                </div>
                <div class="rounded-xl border border-border bg-surface/80 px-4 py-3">
                    <dt class="text-xs font-medium text-muted">2. Isi detail</dt>
                    <dd class="mt-1 text-sm font-semibold text-foreground">Instruksi & file</dd>
                </div>
                <div class="rounded-xl border border-border bg-surface/80 px-4 py-3">
                    <dt class="text-xs font-medium text-muted">3. Checkout</dt>
                    <dd class="mt-1 text-sm font-semibold text-foreground">Bayar & pantau</dd>
                </div>
            </dl>
        </div>
    </section>

    <section id="layanan" class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Katalog</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Layanan</h2>
                <p class="mt-2 max-w-xl text-[15px] leading-relaxed text-slate-700 sm:text-base">Harga di bawah ini adalah harga contoh dan dapat diubah oleh admin.</p>
            </div>
            <a href="<?php echo e(route('services.index')); ?>" class="text-sm font-semibold text-primary transition hover:text-primary-dark hover:underline">
                Lihat semua layanan →
            </a>
        </div>

        <?php if($categories->isNotEmpty()): ?>
            <div class="mt-6 flex flex-wrap gap-2">
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('services.index', ['category' => $category->slug])); ?>" class="inline-flex min-h-11 items-center rounded-lg border border-border bg-surface px-3.5 text-[15px] font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                        <?php echo e($category->name); ?>

                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <?php if($services->isEmpty()): ?>
            <div class="mt-8 rounded-2xl border border-dashed border-border bg-surface p-10 text-center">
                <p class="font-medium text-foreground">Belum ada layanan yang ditampilkan.</p>
                <p class="mt-1 text-sm text-muted">Katalog layanan sedang disiapkan.</p>
            </div>
        <?php else: ?>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('services.show', $service)); ?>" class="group flex flex-col rounded-2xl border border-border bg-surface p-5 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex rounded-lg bg-background px-2.5 py-1 text-xs font-medium text-muted transition group-hover:bg-primary-soft group-hover:text-primary">
                                <?php echo e($service->badgeLabel()); ?>

                            </span>
                            <span class="text-[11px] font-semibold uppercase tracking-wide text-primary">Harga contoh</span>
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-foreground transition group-hover:text-primary">
                            <?php echo e($service->name); ?>

                        </h3>
                        <p class="mt-2 line-clamp-2 flex-1 text-[15px] leading-relaxed text-slate-700"><?php echo e($service->description); ?></p>
                        <div class="mt-5 flex items-center justify-between border-t border-border pt-4">
                            <span class="text-sm font-bold tabular-nums text-foreground"><?php echo e($service->formattedPrice()); ?><span class="font-medium text-muted">/<?php echo e($service->unit); ?></span></span>
                            <span class="text-sm font-semibold text-primary">Lihat detail</span>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if($digitalPrintServices->isNotEmpty()): ?>
        <section id="digital-print" class="border-t border-border bg-background">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Digital print</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Print A0–A5 & scan copy</h2>
                        <p class="mt-2 max-w-xl text-[15px] leading-relaxed text-slate-700 sm:text-base">Print ukuran besar hingga A5 dan scan copy untuk kebutuhan kantor, sekolah, dan acara.</p>
                    </div>
                    <a href="<?php echo e(route('services.index', ['category' => 'digital-print'])); ?>" class="text-sm font-semibold text-primary transition hover:text-primary-dark hover:underline">
                        Semua digital print →
                    </a>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = $digitalPrintServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('services.show', $service)); ?>" class="group flex flex-col rounded-2xl border border-border bg-surface p-5 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-sm">
                            <span class="inline-flex w-fit rounded-lg bg-primary-soft px-2.5 py-1 text-xs font-semibold text-primary"><?php echo e($service->badgeLabel()); ?></span>
                            <h3 class="mt-4 text-base font-semibold text-foreground transition group-hover:text-primary"><?php echo e($service->name); ?></h3>
                            <p class="mt-2 line-clamp-2 flex-1 text-[15px] leading-relaxed text-slate-700"><?php echo e($service->description); ?></p>
                            <div class="mt-5 flex items-center justify-between border-t border-border pt-4">
                                <span class="text-sm font-bold tabular-nums text-foreground"><?php echo e($service->formattedPrice()); ?><span class="font-medium text-muted">/<?php echo e($service->unit); ?></span></span>
                                <span class="text-sm font-semibold text-primary">Lihat detail</span>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if($atkServices->isNotEmpty()): ?>
        <section id="atk" class="border-t border-border bg-surface">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Alat tulis</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Pesan ATK</h2>
                        <p class="mt-2 max-w-xl text-[15px] leading-relaxed text-slate-700 sm:text-base">Pulpen, buku, map, dan kebutuhan tulis lainnya — bisa sekalian dengan pesanan print.</p>
                    </div>
                    <a href="<?php echo e(route('services.index', ['type' => 'jual'])); ?>" class="text-sm font-semibold text-primary transition hover:text-primary-dark hover:underline">
                        Semua ATK →
                    </a>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <?php $__currentLoopData = $atkServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('services.show', $service)); ?>" class="group flex flex-col rounded-2xl border border-border bg-background p-5 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-sm">
                            <span class="inline-flex w-fit rounded-lg bg-primary-soft px-2.5 py-1 text-xs font-semibold text-primary">ATK</span>
                            <h3 class="mt-4 text-base font-semibold text-foreground transition group-hover:text-primary"><?php echo e($service->name); ?></h3>
                            <p class="mt-2 line-clamp-2 flex-1 text-[15px] leading-relaxed text-slate-700"><?php echo e($service->description); ?></p>
                            <div class="mt-5 flex items-center justify-between border-t border-border pt-4">
                                <span class="text-sm font-bold tabular-nums text-foreground"><?php echo e($service->formattedPrice()); ?><span class="font-medium text-muted">/<?php echo e($service->unit); ?></span></span>
                                <span class="text-sm font-semibold text-primary">Pesan</span>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section id="kontak" class="border-t border-border bg-surface">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Bantuan</p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Butuh penjelasan?</h2>
                <p class="mt-3 max-w-md text-sm leading-relaxed text-muted">
                    Nomor WhatsApp dan jam operasional tersedia di halaman Kontak. Admin siap membantu kebutuhan khusus.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="<?php echo e(route('kontak')); ?>" class="inline-flex min-h-12 items-center rounded-lg border border-border bg-surface px-5 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                        Buka halaman kontak
                    </a>
                    <?php if($whatsappUrl): ?>
                        <a href="<?php echo e($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-12 items-center rounded-lg border border-border bg-surface px-5 text-sm font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                            Hubungi admin via WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-2xl border border-border bg-background p-6">
                <div class="rounded-xl border border-border bg-surface p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted">Alur pesanan</p>
                    <ol class="mt-4 space-y-4">
                        <li class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-xs font-bold text-primary">1</span>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Pilih layanan</p>
                                <p class="mt-0.5 text-sm text-muted">Lihat harga contoh di katalog.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-xs font-bold text-primary">2</span>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Isi detail pekerjaan</p>
                                <p class="mt-0.5 text-sm text-muted">Cantumkan instruksi dan unggah file bila perlu.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary-soft text-xs font-bold text-primary">3</span>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Checkout & bayar</p>
                                <p class="mt-0.5 text-sm text-muted">Pantau status pesanan dari akun Anda.</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
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
<?php /**PATH C:\laragon\www\AdhijayaFC\resources\views/home.blade.php ENDPATH**/ ?>