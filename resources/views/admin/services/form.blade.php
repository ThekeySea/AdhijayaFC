@props(['service' => null, 'categories' => collect(), 'tiers' => [], 'optionGroups' => []])

@php
    $tierRows = old('price_tiers');
    if (! is_array($tierRows)) {
        $tierRows = $tiers;
    }

    $groupRows = old('option_groups');
    if (! is_array($groupRows)) {
        $groupRows = $optionGroups;
    }
@endphp

<div class="space-y-4" x-data="{
    tiers: @js($tierRows),
    groups: @js($groupRows),
    addTier() {
        this.tiers.push({ min_qty: '', max_qty: '', unit_price: '' });
    },
    removeTier(index) {
        this.tiers.splice(index, 1);
    },
    addGroup() {
        this.groups.push({
            name: '',
            selection_type: 'single',
            is_required: false,
            options: [{ name: '', price: '', pricing: 'per_unit' }],
        });
    },
    removeGroup(index) {
        this.groups.splice(index, 1);
    },
    addGroupOption(gi) {
        this.groups[gi].options.push({ name: '', price: '', pricing: 'per_unit' });
    },
    removeGroupOption(gi, oi) {
        this.groups[gi].options.splice(oi, 1);
    },
}">
    <div>
        <x-input-label for="name" value="Nama layanan" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service?->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="type" value="Jenis" />
            <select id="type" name="type" class="mt-1 block w-full rounded-lg border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="jasa" @selected(old('type', $service?->type ?? 'jasa') === 'jasa')>Jasa (kategori katalog)</option>
                <option value="jual" @selected(old('type', $service?->type) === 'jual')>Jual (ATK & barang)</option>
            </select>
            <p class="mt-1 text-xs text-muted">Jasa memakai kategori. Jual tampil di section ATK.</p>
        </div>

        <div>
            <x-input-label for="category_id" value="Kategori" />
            <select id="category_id" name="category_id" class="mt-1 block w-full rounded-lg border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option value="">— Pilih kategori —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $service?->category_id) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
            <p class="mt-1 text-xs text-muted">Wajib untuk jenis jasa. Menentukan filter katalog & section beranda.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div>
            <x-input-label for="price" value="Harga dasar (Rp)" />
            <x-text-input id="price" name="price" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('price', $service?->price)" required />
            <x-input-error class="mt-2" :messages="$errors->get('price')" />
            <p class="mt-1 text-xs text-muted">Dipakai bila jumlah di luar rentang tier.</p>
        </div>

        <div>
            <x-input-label for="unit" value="Satuan" />
            <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full" :value="old('unit', $service?->unit ?? 'lembar')" required />
            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
        </div>

        <div>
            <x-input-label for="min_quantity" value="Min. pembelian" />
            <x-text-input id="min_quantity" name="min_quantity" type="number" min="1" class="mt-1 block w-full" :value="old('min_quantity', $service?->min_quantity)" placeholder="tanpa minimal" />
            <x-input-error class="mt-2" :messages="$errors->get('min_quantity')" />
            <p class="mt-1 text-xs text-muted">Kosongkan bila tanpa minimal. Digital Print sebaiknya kosong.</p>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="min_ready_minutes" value="Durasi minimal siap (menit)" />
            <x-text-input id="min_ready_minutes" name="min_ready_minutes" type="number" min="0" max="10080" class="mt-1 block w-full" :value="old('min_ready_minutes', $service?->min_ready_minutes ?? 30)" />
            <x-input-error class="mt-2" :messages="$errors->get('min_ready_minutes')" />
            <p class="mt-1 text-xs text-muted">Slot checkout paling cepat = sekarang + nilai ini (max semua item di keranjang).</p>
        </div>
    </div>

    <div>
        <x-input-label for="file_requirement" value="File dari customer" />
        <select id="file_requirement" name="file_requirement" class="mt-1 block w-full rounded-lg border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
            <option value="none" @selected(old('file_requirement', $service?->file_requirement ?? 'none') === 'none')>Tidak perlu file</option>
            <option value="optional" @selected(old('file_requirement', $service?->file_requirement) === 'optional')>File opsional</option>
            <option value="required" @selected(old('file_requirement', $service?->file_requirement) === 'required')>File wajib</option>
        </select>
        <p class="mt-1 text-xs text-muted">Mengatur apakah customer harus mengunggah file desain saat memesan.</p>
        <x-input-error class="mt-2" :messages="$errors->get('file_requirement')" />
    </div>

    <div>
        <div class="flex items-center justify-between gap-3">
            <x-input-label for="price_tiers" value="Harga per jumlah (tier)" />
            <button type="button" @click="addTier()" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                + Tambah tier
            </button>
        </div>
        <p class="mt-1 text-xs text-muted">Contoh: 1–10 → Rp 5.000, 11–50 → Rp 4.000, 51–100 → Rp 3.500, 101+ → kosongkan batas akhir.</p>

        <div class="mt-3 space-y-2">
            <template x-for="(tier, index) in tiers" :key="index">
                <div class="grid grid-cols-3 gap-2 rounded-xl border border-border bg-background p-3">
                    <div>
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-muted" :for="'tier_min_' + index">Min</label>
                        <input type="number" min="1" :id="'tier_min_' + index" :name="'price_tiers[' + index + '][min_qty]'" x-model="tier.min_qty" class="mt-1 block w-full rounded-lg border-border bg-surface px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold uppercase tracking-wide text-muted" :for="'tier_max_' + index">Max (opsional)</label>
                        <input type="number" min="1" :id="'tier_max_' + index" :name="'price_tiers[' + index + '][max_qty]'" x-model="tier.max_qty" placeholder="tanpa batas" class="mt-1 block w-full rounded-lg border-border bg-surface px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                    </div>
                    <div class="flex items-end gap-2">
                        <div class="min-w-0 flex-1">
                            <label class="text-[11px] font-semibold uppercase tracking-wide text-muted" :for="'tier_price_' + index">Harga</label>
                            <input type="number" min="0" step="1" :id="'tier_price_' + index" :name="'price_tiers[' + index + '][unit_price]'" x-model="tier.unit_price" class="mt-1 block w-full rounded-lg border-border bg-surface px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <button type="button" @click="removeTier(index)" class="mb-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-200 text-sm font-bold text-red-600 transition hover:bg-red-50" aria-label="Hapus tier">
                            ×
                        </button>
                    </div>
                </div>
            </template>
        </div>

        @php
            $tierErrors = collect($errors->messages())
                ->filter(fn (array $messages, string $key) => str_starts_with($key, 'price_tiers'))
                ->flatten();
        @endphp
        @if ($tierErrors->isNotEmpty())
            <div class="mt-2 space-y-1">
                @foreach ($tierErrors as $message)
                    <p class="text-sm font-medium text-red-600">{{ $message }}</p>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <div class="flex items-center justify-between gap-3">
            <x-input-label value="Group opsi (pilihan customer)" />
            <button type="button" @click="addGroup()" class="inline-flex min-h-11 items-center rounded-lg border border-border px-3 text-xs font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                + Tambah group
            </button>
        </div>
        <p class="mt-1 text-xs text-muted">Group “single” = pilih satu (radio). “multiple” = boleh lebih dari satu (checkbox). Harga opsi menambah harga satuan (per_unit) atau tetap per pesanan (per_order).</p>

        <div class="mt-3 space-y-3">
            <template x-for="(group, gi) in groups" :key="'grp-' + gi">
                <div class="rounded-xl border border-border bg-background p-3">
                    <div class="flex flex-wrap items-end gap-2">
                        <div class="min-w-40 flex-1">
                            <label class="text-[11px] font-semibold uppercase tracking-wide text-muted" :for="'group_name_' + gi">Nama group</label>
                            <input type="text" :id="'group_name_' + gi" :name="'option_groups[' + gi + '][name]'" x-model="group.name" placeholder="misal: Produk & Bahan" class="mt-1 block w-full rounded-lg border-border bg-surface px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="text-[11px] font-semibold uppercase tracking-wide text-muted" :for="'group_sel_' + gi">Tipe</label>
                            <select :id="'group_sel_' + gi" :name="'option_groups[' + gi + '][selection_type]'" x-model="group.selection_type" class="mt-1 block rounded-lg border-border bg-surface px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                <option value="single">Pilih satu</option>
                                <option value="multiple">Boleh banyak</option>
                            </select>
                        </div>
                        <label class="mb-1.5 flex items-center gap-2 text-sm text-foreground">
                            <input type="checkbox" :name="'option_groups[' + gi + '][is_required]'" value="1" class="rounded border-border text-primary focus:ring-primary" :checked="group.is_required" @change="group.is_required = $event.target.checked">
                            Wajib
                        </label>
                        <button type="button" @click="removeGroup(gi)" class="mb-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-200 text-sm font-bold text-red-600 transition hover:bg-red-50" aria-label="Hapus group">×</button>
                    </div>

                    <div class="mt-3 space-y-2 border-t border-border pt-3">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-muted">Opsi dalam group</p>
                            <button type="button" @click="addGroupOption(gi)" class="inline-flex min-h-8 items-center rounded-lg border border-border px-2 text-[11px] font-medium text-foreground transition hover:border-primary/40 hover:bg-primary-soft hover:text-primary">
                                + Opsi
                            </button>
                        </div>
                        <template x-for="(option, oi) in group.options" :key="'opt-' + gi + '-' + oi">
                            <div class="grid grid-cols-1 gap-2 rounded-lg border border-border bg-surface p-2 sm:grid-cols-12">
                                <div class="sm:col-span-5">
                                    <label class="text-[11px] font-semibold uppercase tracking-wide text-muted" :for="'opt_name_' + gi + '_' + oi">Nama</label>
                                    <input type="text" :id="'opt_name_' + gi + '_' + oi" :name="'option_groups[' + gi + '][options][' + oi + '][name]'" x-model="option.name" class="mt-1 block w-full rounded-lg border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="text-[11px] font-semibold uppercase tracking-wide text-muted" :for="'opt_price_' + gi + '_' + oi">Harga (Rp)</label>
                                    <input type="number" step="1" :id="'opt_price_' + gi + '_' + oi" :name="'option_groups[' + gi + '][options][' + oi + '][price]'" x-model="option.price" class="mt-1 block w-full rounded-lg border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="text-[11px] font-semibold uppercase tracking-wide text-muted" :for="'opt_pricing_' + gi + '_' + oi">Cara hitung</label>
                                    <select :id="'opt_pricing_' + gi + '_' + oi" :name="'option_groups[' + gi + '][options][' + oi + '][pricing]'" x-model="option.pricing" class="mt-1 block w-full rounded-lg border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                                        <option value="per_unit">Per satuan</option>
                                        <option value="per_order">Per pesanan</option>
                                    </select>
                                </div>
                                <div class="flex items-end sm:col-span-1">
                                    <button type="button" @click="removeGroupOption(gi, oi)" class="mb-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-200 text-sm font-bold text-red-600 transition hover:bg-red-50" aria-label="Hapus opsi">×</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        @php
            $groupErrors = collect($errors->messages())
                ->filter(fn (array $messages, string $key) => str_starts_with($key, 'option_groups'))
                ->flatten();
        @endphp
        @if ($groupErrors->isNotEmpty())
            <div class="mt-2 space-y-1">
                @foreach ($groupErrors as $message)
                    <p class="text-sm font-medium text-red-600">{{ $message }}</p>
                @endforeach
            </div>
        @endif
    </div>

    <div>
        <x-input-label for="description" value="Deskripsi" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-lg border-border bg-surface px-3.5 py-2.5 text-sm text-foreground transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="Jelaskan layanan secara singkat">{{ old('description', $service?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div>
        <x-input-label for="image_url" value="URL gambar (opsional)" />
        <x-text-input id="image_url" name="image_url" type="url" class="mt-1 block w-full" :value="old('image_url', $service?->image_url)" />
        <x-input-error class="mt-2" :messages="$errors->get('image_url')" />
    </div>

    <label class="flex items-start gap-3">
        <input type="checkbox" name="is_active" value="1" class="mt-1 rounded border-border text-primary focus:ring-primary" @checked(old('is_active', $service?->is_active ?? true))>
        <span class="text-sm text-foreground">
            Aktif
            <span class="mt-0.5 block text-xs text-muted">Layanan aktif tampil di halaman publik.</span>
        </span>
    </label>
</div>
