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
        <p class="mt-1.5 text-sm leading-relaxed text-slate-700 sm:text-base">Hubungi kami untuk pertanyaan dan kebutuhan khusus.</p>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <section class="mb-6 rounded-2xl border border-border bg-surface p-6">
            <p class="text-xs font-semibold uppercase tracking-wider text-primary">Tentang kami</p>
            <h2 class="mt-2 text-xl font-bold tracking-tight text-foreground sm:text-2xl">Cerita singkat usaha</h2>
            <div class="mt-4 space-y-3 text-[15px] leading-relaxed text-slate-700 sm:text-base">
                <?php if($settings->about): ?>
                    <p><?php echo nl2br(e($settings->about)); ?></p>
                <?php else: ?>
                    <p>
                        Fotocopy Adhijaya adalah usaha lokal di bidang fotokopi dan percetakan.
                        Awalnya dibuka untuk membantu kebutuhan cetak harian pelajar, kantor, dan warga sekitar —
                        dari fotokopi sederhana sampai jilid, laminating, dan print dokumen.
                    </p>
                    <p>
                        Lewat situs ini, pelanggan bisa memilih layanan, mengirim file, dan memantau pesanan
                        tanpa harus chat berulang. Harga di katalog masih harga contoh dan bisa diubah oleh admin.
                    </p>
                    <p class="rounded-xl border border-dashed border-border bg-background px-4 py-3 text-xs text-muted">
                        Cerita usaha belum diisi. Buka <strong>Info Usaha</strong> di dashboard admin untuk mengisi.
                    </p>
                <?php endif; ?>
            </div>
        </section>

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
                        <dd class="font-semibold tabular-nums text-foreground"><?php echo e($settings->hours_weekday ?: '08.00 – 20.00'); ?></dd>
                    </div>
                    <div class="flex justify-between gap-4 rounded-lg bg-background px-3 py-2.5">
                        <dt class="text-muted">Minggu</dt>
                        <dd class="font-semibold <?php echo e($settings->hours_sunday ? 'tabular-nums text-foreground' : 'text-muted'); ?>"><?php echo e($settings->hours_sunday ?: 'Tutup'); ?></dd>
                    </div>
                </dl>
                <p class="mt-4 text-xs text-muted"><?php echo e($settings->hours_weekday ? 'Jam mengikuti pengaturan admin.' : 'Jam di atas bersifat contoh sampai dikonfirmasi.'); ?></p>
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
                    <?php if($settings->address): ?>
                        <p class="mt-1 text-sm leading-relaxed text-muted"><?php echo e($settings->address); ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-sm text-muted">Alamat akan ditampilkan setelah dikonfirmasi pemilik usaha.</p>
                    <?php endif; ?>
                    <?php if($settings->phone): ?>
                        <p class="mt-2 text-sm text-muted">Telepon: <a href="tel:<?php echo e($settings->phone); ?>" class="font-medium text-primary hover:underline"><?php echo e($settings->phone); ?></a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <section class="mt-4 rounded-2xl border border-border bg-surface p-6">
            <div class="flex items-center gap-3">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-primary-soft text-primary">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-semibold text-foreground">Lokasi toko</h2>
                    <p class="text-sm text-muted">Peta posisi usaha di Google Maps / OpenStreetMap.</p>
                </div>
            </div>

            <?php if($settings->latitude !== null && $settings->longitude !== null): ?>
                <div id="kontak-map" class="mt-4 h-72 w-full overflow-hidden rounded-xl border border-border bg-background sm:h-80" aria-label="Peta lokasi toko"></div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-muted"><?php echo e($settings->address ?: 'Lihat pin lokasi pada peta.'); ?></p>
                    <a
                        href="https://www.google.com/maps/search/?api=1&query=<?php echo e(rawurlencode($settings->latitude.','.$settings->longitude)); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-10 items-center justify-center rounded-lg border border-border bg-background px-4 text-sm font-semibold text-foreground transition hover:border-primary hover:text-primary"
                    >
                        Buka di Google Maps
                    </a>
                </div>
            <?php else: ?>
                <div class="mt-4 rounded-xl border border-dashed border-border bg-background p-6 text-center">
                    <p class="text-sm font-medium text-foreground">Peta lokasi belum diatur.</p>
                    <p class="mt-1 text-xs leading-relaxed text-muted">
                        Pemilik usaha dapat mengatur posisi pin di dashboard admin → <strong>Info Usaha</strong>.
                    </p>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <?php if($settings->latitude !== null && $settings->longitude !== null): ?>
        <?php $__env->startPush('scripts'); ?>
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    function initKontakMap() {
                        if (typeof L === 'undefined') {
                            window.setTimeout(initKontakMap, 50);

                            return;
                        }

                        var lat = parseFloat(<?php echo json_encode($settings->latitude, 15, 512) ?>);
                        var lng = parseFloat(<?php echo json_encode($settings->longitude, 15, 512) ?>);
                        var map = L.map('kontak-map').setView([lat, lng], 16);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(map);

                        L.marker([lat, lng]).addTo(map);
                    }

                    initKontakMap();
                });
            </script>
        <?php $__env->stopPush(); ?>
    <?php endif; ?>
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