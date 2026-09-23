<x-app-layout>
    <x-slot name="header">
        <nav class="flex flex-wrap items-center gap-2 text-sm" aria-label="Breadcrumb">
            <a href="{{ route('services.index') }}" class="font-medium text-muted transition hover:text-primary">Layanan</a>
            <span class="text-border" aria-hidden="true">/</span>
            <span class="font-medium text-foreground">{{ $service->name }}</span>
        </nav>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8"
        x-data="serviceOrder({
            basePrice: {{ (float) $service->price }},
            minQty: {{ (int) ($service->min_quantity ?? 1) }},
            tiers: @js($service->priceTiers->map(fn ($t) => [
                'min' => (int) $t->min_qty,
                'max' => $t->max_qty === null ? null : (int) $t->max_qty,
                'price' => (float) $t->unit_price,
            ])->values()),
            groups: @js($optionGroups),
            freeOptions: @js($freeOptions),
            requiresFile: {{ $service->requiresFile() ? 'true' : 'false' }},
            allowsFile: {{ $service->allowsFile() ? 'true' : 'false' }},
        })">
        <form method="POST" action="{{ route('cart.store') }}" enctype="multipart/form-data" id="add-to-cart-form">
            @csrf
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <input type="hidden" name="quantity" value="{{ old('quantity', $service->min_quantity ?? 1) }}" x-model.number="quantity" x-ref="qtyInput">

            <div class="grid gap-6 lg:grid-cols-3">
                {{-- Left: info + opsi + file --}}
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-2xl border border-border bg-surface p-6 sm:p-8">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($service->category)
                                <span class="rounded-lg bg-background px-2.5 py-1 text-xs font-medium text-muted">{{ $service->category->name }}</span>
                            @endif
                            <span class="rounded-lg bg-primary-soft px-2.5 py-1 text-xs font-semibold text-primary">Harga contoh</span>
                            @if ($service->is_active)
                                <span class="rounded-lg border border-primary-line px-2.5 py-1 text-xs font-semibold text-primary">Tersedia</span>
                            @endif
                            @if ($service->min_quantity)
                                <span class="rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Min. {{ $service->min_quantity }} {{ $service->unit }}</span>
                            @endif
                        </div>

                        <h1 class="mt-4 text-balance text-2xl font-bold tracking-tight text-foreground sm:text-3xl">{{ $service->name }}</h1>

                        @if ($service->description)
                            <p class="mt-4 text-base leading-relaxed text-muted">{{ $service->description }}</p>
                        @endif

                        <dl class="mt-6 grid gap-4 border-t border-border pt-6 sm:grid-cols-2">
                            <div class="rounded-xl bg-background p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Satuan</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground">per {{ $service->unit }}</dd>
                            </div>
                            <div class="rounded-xl bg-background p-4">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Status</dt>
                                <dd class="mt-1 text-sm font-semibold {{ $service->is_active ? 'text-primary' : 'text-muted' }}">
                                    {{ $service->is_active ? 'Tersedia' : 'Tidak tersedia' }}
                                </dd>
                            </div>
                        </dl>

                        @if ($service->priceTiers->isNotEmpty())
                            <div class="mt-6 border-t border-border pt-6">
                                <h2 class="text-base font-semibold text-foreground">Harga per jumlah</h2>
                                <p class="mt-1 text-sm text-muted">Makin banyak, makin hemat per {{ $service->unit }}.</p>
                                <ul class="mt-4 divide-y divide-border">
                                    @foreach ($service->priceTiers as $tier)
                                        <li class="flex items-center justify-between gap-3 py-2.5 first:pt-0 last:pb-0">
                                            <span class="text-sm text-foreground">{{ $tier->quantityLabel() }} {{ $service->unit }}</span>
                                            <span class="text-sm font-semibold tabular-nums text-foreground">{{ $tier->formattedUnitPrice() }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    @if ($service->activeOptionGroups->isNotEmpty() || $service->activeOptions->isNotEmpty())
                        <div class="rounded-2xl border border-border bg-surface p-6 sm:p-8">
                            <h2 class="text-base font-semibold text-foreground">Pilihan layanan</h2>
                            <p class="mt-1 text-sm text-muted">Atur bahan, sisi cetak, finishing, dan opsi lain sesuai kebutuhanmu.</p>

                            <div class="mt-2">
                                <template x-for="(group, gi) in groups" :key="'g-' + group.id">
                                    <fieldset class="mb-4 border-t border-border pt-5 first:border-t-0 first:pt-0">
                                        <legend class="text-xs font-semibold uppercase tracking-wide text-muted">
                                            <span x-text="group.name"></span>
                                            <template x-if="group.required"><span class="text-red-600"> *</span></template>
                                        </legend>
                                        <div class="mt-3">
                                            <template x-if="useDropdown(group)">
                                                <select
                                                    :name="'options[' + group.id + ']'"
                                                    :id="'group_select_' + group.id"
                                                    :required="group.required"
                                                    :value="String(selected[group.id]?.[0] ?? '')"
                                                    @change="onSingleSelectChange(group, $event)"
                                                    class="block w-full rounded-lg border-border bg-background px-3 py-2.5 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                                >
                                                    <template x-for="option in group.options" :key="'opt-' + option.id">
                                                        <option
                                                            :value="String(option.id)"
                                                            :selected="isSelected(group, option.id)"
                                                            x-text="option.name + ' — ' + option.label"
                                                        ></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="!useDropdown(group)">
                                                <div class="space-y-2">
                                                    <template x-for="(option, oi) in group.options" :key="'o-' + option.id">
                                                        <label class="flex items-start gap-3 rounded-lg border bg-background px-3 py-2.5 transition cursor-pointer"
                                                            :class="isSelected(group, option.id) ? 'border-primary bg-primary-soft/40' : 'border-border hover:border-primary/40'">
                                                            <input
                                                                :type="group.selection === 'single' ? 'radio' : 'checkbox'"
                                                                :name="group.selection === 'single' ? 'options[' + group.id + ']' : 'options[]'"
                                                                :value="option.id"
                                                                class="mt-0.5 rounded border-border text-primary focus:ring-primary"
                                                                :checked="isSelected(group, option.id)"
                                                                @change="toggleOption(group, option)"
                                                            >
                                                            <span class="min-w-0 flex-1">
                                                                <span class="block text-sm font-medium text-foreground" x-text="option.name"></span>
                                                                <span class="mt-0.5 block text-xs text-muted" x-text="option.label"></span>
                                                            </span>
                                                        </label>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>
                                    </fieldset>
                                </template>

                                @if ($freeOptions->isNotEmpty())
                                    <fieldset class="mb-4 border-t border-border pt-5">
                                        <legend class="text-xs font-semibold uppercase tracking-wide text-muted">Opsi tambahan</legend>
                                        <div class="mt-3 space-y-2">
                                            @foreach ($freeOptions as $option)
                                                <label class="flex items-start gap-3 rounded-lg border border-border bg-background px-3 py-2.5 transition hover:border-primary/40">
                                                    <input type="checkbox" name="options[]" value="{{ $option['id'] }}" class="mt-0.5 rounded border-border text-primary focus:ring-primary"
                                                        @change="onFreeOptionChange"
                                                        @checked(in_array($option['id'], old('options', []), false))>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block text-sm font-medium text-foreground">{{ $option['name'] }}</span>
                                                        <span class="mt-0.5 block text-xs text-muted">{{ $option['label'] }}</span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endif

                                <x-input-error :messages="$errors->get('options')" class="mt-3" />
                            </div>
                        </div>
                    @endif

                    @if ($service->allowsFile())
                        <div class="rounded-2xl border border-border bg-surface p-6 sm:p-8">
                            <h2 class="text-base font-semibold text-foreground">File desain</h2>
                            <p class="mt-1 text-sm text-muted">
                                @if ($service->requiresFile())
                                    Wajib diunggah untuk jasa ini.
                                @else
                                    Opsional — unggah jika file sudah siap.
                                @endif
                            </p>

                            <div class="mt-4">
                                <label for="files-main" class="text-xs font-semibold uppercase tracking-wide text-muted">
                                    @if ($service->requiresFile())
                                        <span class="text-red-600">File wajib</span>
                                    @else
                                        File (opsional)
                                    @endif
                                </label>
                                <input id="files-main" type="file" name="files[]" multiple
                                    accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.txt,.zip"
                                    class="mt-2 block w-full text-sm text-muted file:mr-3 file:rounded-lg file:border-0 file:bg-primary-soft file:px-3 file:py-2 file:text-xs file:font-semibold file:text-primary hover:file:bg-primary/15">
                                <p class="mt-1 text-xs text-muted">PDF, gambar, DOC(X), TXT, ZIP. Maks 5 MB per file, maks 5 file.</p>
                                <x-input-error :messages="$errors->get('files')" class="mt-2" />
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right: total + pesan + aksi --}}
                <aside class="lg:col-span-1">
                    <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm lg:sticky lg:top-24">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Harga contoh</p>
                                <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-foreground" x-text="format(unitPrice)" x-init="recalc()">{{ $service->formattedPrice() }}</p>
                                <p class="mt-1 text-sm text-muted">per {{ $service->unit }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-semibold uppercase tracking-wide text-muted">Total</p>
                                <p class="mt-2 text-2xl font-bold tabular-nums tracking-tight text-primary" x-text="format(total)" x-init="recalc()">{{ $service->formattedPrice() }}</p>
                            </div>
                        </div>
                        @if ($service->priceTiers->isNotEmpty())
                            <p class="mt-2 text-xs leading-relaxed text-muted">Harga menyesuaikan jumlah pesanan. Lihat tabel “Harga per jumlah”.</p>
                        @endif

                        <div class="mt-5 border-t border-border pt-5">
                            <label for="quantity-main" class="text-xs font-semibold uppercase tracking-wide text-muted">Jumlah ({{ $service->unit }})</label>
                            <input
                                id="quantity-main"
                                type="number"
                                name="quantity_display"
                                min="{{ $service->min_quantity ?? 1 }}"
                                max="9999"
                                x-model.number="quantity"
                                @input.debounce.150ms="syncQty"
                                class="mt-2 w-28 rounded-lg border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                            >
                            @if ($service->min_quantity)
                                <p class="mt-1 text-xs text-muted">Minimal {{ $service->min_quantity }} {{ $service->unit }}.</p>
                            @endif
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <label for="detail-main" class="text-xs font-semibold uppercase tracking-wide text-muted">Pesan untuk penjual (opsional)</label>
                            <textarea id="detail-main" name="detail" rows="3" maxlength="500"
                                placeholder="misal: file sudah siap, cetak A4 2 sisi, jilid kiri"
                                class="mt-2 block w-full rounded-lg border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">{{ old('detail') }}</textarea>
                            <x-input-error :messages="$errors->get('detail')" class="mt-2" />
                        </div>

                        @guest
                            <x-primary-button class="mt-5 w-full">Tambah ke keranjang</x-primary-button>
                            <p class="mt-3 text-center text-xs leading-relaxed text-muted">
                                Bisa dulu ditambahkan. <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">Masuk</a> saat checkout untuk menyelesaikan pesanan.
                            </p>
                        @else
                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.services.edit', $service) }}" class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded-lg bg-primary px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-dark">
                                    Ubah layanan
                                </a>
                            @else
                                <x-primary-button class="mt-5 w-full">Tambah ke keranjang</x-primary-button>
                                <a href="{{ route('cart.index') }}" class="mt-3 inline-flex w-full items-center justify-center text-sm font-medium text-muted transition hover:text-primary">
                                    Lihat keranjang
                                </a>
                            @endif
                        @endauth

                        <p class="mt-4 border-t border-border pt-4 text-xs leading-relaxed text-muted">
                            Harga bersifat contoh dan dapat berubah. Hubungi admin untuk kebutuhan khusus.
                        </p>
                    </div>
                </aside>
            </div>
        </form>
    </div>

    @once
        <script>
            function serviceOrder(config) {
                const quantity = Math.max(config.minQty, 1);
                return {
                    quantity,
                    unitPrice: config.basePrice,
                    total: config.basePrice * quantity,
                    selected: {},
                    groups: config.groups || [],
                    freeOptions: config.freeOptions || [],
                    requiresFile: !!config.requiresFile,
                    allowsFile: !!config.allowsFile,
                    init() {
                        config.groups.forEach((group) => {
                            if (group.selection === 'single' && group.options.length) {
                                this.selected[group.id] = [group.options[0].id];
                            } else {
                                this.selected[group.id] = [];
                            }
                        });
                        this.recalc();
                    },
                    syncQty() {
                        const min = config.minQty || 1;
                        if (!Number.isFinite(this.quantity) || this.quantity < min) {
                            this.quantity = min;
                        }
                        const qtyInput = this.$refs.qtyInput;
                        if (qtyInput) qtyInput.value = this.quantity;
                        this.recalc();
                    },
                    isSelected(group, optionId) {
                        return (this.selected[group.id] || []).includes(optionId);
                    },
                    useDropdown(group) {
                        return group.selection === 'single' && (group.options?.length || 0) > 3;
                    },
                    onSingleSelectChange(group, event) {
                        const id = Number(event.target.value);
                        this.selected[group.id] = Number.isFinite(id) && id > 0 ? [id] : [];
                        this.recalc();
                    },
                    toggleOption(group, option) {
                        const current = this.selected[group.id] || [];
                        if (group.selection === 'single') {
                            this.selected[group.id] = [option.id];
                        } else if (current.includes(option.id)) {
                            this.selected[group.id] = current.filter((id) => id !== option.id);
                        } else {
                            this.selected[group.id] = [...current, option.id];
                        }
                        this.syncOptionInputs();
                        this.recalc();
                    },
                    onFreeOptionChange() {
                        this.$nextTick(() => this.recalc());
                    },
                    syncOptionInputs() {
                        const form = this.$el;
                        form.querySelectorAll('input[name^="options"]').forEach((input) => {
                            if (input.type === 'radio') {
                                const groupId = input.name.replace('options[', '').replace(']', '');
                                input.checked = (this.selected[groupId] || []).includes(Number(input.value));
                            }
                        });
                    },
                    selectedIds() {
                        const ids = [];
                        Object.values(this.selected).forEach((list) => list.forEach((id) => ids.push(Number(id))));
                        this.$el.querySelectorAll('input[name="options[]"]:checked').forEach((input) => {
                            ids.push(Number(input.value));
                        });
                        return ids;
                    },
                    unitPriceFor(qty) {
                        let price = config.basePrice;
                        for (const tier of config.tiers) {
                            if (qty >= tier.min && (tier.max === null || qty <= tier.max)) {
                                price = tier.price;
                                break;
                            }
                        }
                        return price;
                    },
                    recalc() {
                        const qty = Math.max(config.minQty || 1, Number(this.quantity) || 1);
                        const ids = this.selectedIds();
                        const allOptions = [...config.groups.flatMap((g) => g.options), ...config.freeOptions];
                        let unitSurcharge = 0;
                        let fixed = 0;
                        allOptions.forEach((option) => {
                            if (!ids.includes(Number(option.id))) return;
                            if (option.pricing === 'per_order') fixed += option.price;
                            else unitSurcharge += option.price;
                        });
                        const base = this.unitPriceFor(qty);
                        this.unitPrice = base + unitSurcharge;
                        this.total = this.unitPrice * qty + fixed;
                    },
                    format(value) {
                        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
                    },
                };
            }
        </script>
    @endonce
</x-app-layout>
