<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Checkout</p>
                <h1 class="mt-1 text-balance text-2xl font-bold tracking-tight text-foreground">Selesaikan pesanan</h1>
            </div>
            <a href="{{ route('cart.index') }}" class="text-sm font-medium text-muted transition hover:text-primary">
                Kembali ke keranjang
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <form
            method="POST"
            action="{{ route('checkout.store') }}"
            enctype="multipart/form-data"
            class="grid gap-6 lg:grid-cols-3"
            x-data="checkoutForm({
                quoteUrl: @js(route('checkout.delivery-quote')),
                slotsUrl: @js(route('checkout.slots')),
                subtotal: @js($subtotal),
                bookableDates: @js($bookableDates),
                defaultDate: @js(old('pickup_date', $bookableDates[0] ?? now()->toDateString())),
                deliveryEnabled: @js((bool) $deliveryEnabled),
            })"
        >
            @csrf

            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">Item pesanan</h2>
                    <ul class="mt-4 divide-y divide-border">
                        @foreach ($lines as $line)
                            <li class="flex flex-wrap items-start justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-foreground">{{ $line['service']->name }}</p>
                                    <p class="mt-0.5 text-sm text-muted">
                                        {{ \App\Support\Cart::formatAmount((float) ($line['unit_price'] ?? $line['price'])) }} × {{ $line['quantity'] }} {{ $line['service']->unit }}
                                    </p>
                                    @if (! empty($line['options']) && $line['options']->isNotEmpty())
                                        <p class="mt-1 text-sm text-primary">
                                            Opsi: {{ $line['options']->map(fn ($option) => $option->name)->implode(', ') }}
                                        </p>
                                    @endif
                                    @if ($line['detail'] !== '')
                                        <p class="mt-1 text-sm text-foreground">
                                            <span class="font-medium text-muted">Detail:</span> {{ $line['detail'] }}
                                        </p>
                                    @endif
                                </div>
                                <p class="shrink-0 text-sm font-bold tabular-nums text-foreground">
                                    {{ \App\Support\Cart::formatAmount((float) $line['subtotal']) }}
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">Kontak</h2>
                    <p class="mt-1 text-sm text-muted">Nomor WhatsApp dipakai admin untuk update status tracking pesanan.</p>

                    <div class="mt-4">
                        <x-input-label for="phone" value="Nomor WhatsApp" />
                        <x-text-input
                            id="phone"
                            name="phone"
                            type="tel"
                            value="{{ old('phone', auth()->user()?->phone) }}"
                            placeholder="628xxxxxxxxxx"
                            autocomplete="tel"
                            class="mt-1 w-full"
                            required
                        />
                        <p class="mt-1 text-xs leading-relaxed text-muted">
                            Format internasional tanpa +, spasi, atau tanda baca. Contoh: 6281234567890
                        </p>
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
                    <h2 class="text-base font-semibold text-foreground">Metode penerimaan</h2>
                    <p class="mt-1 text-sm text-muted">Pilih ambil di tempat atau delivery ke alamat Anda.</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Metode penerimaan">
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                            :class="fulfillment === 'pickup' ? 'border-primary bg-primary-soft' : 'border-border bg-background hover:border-primary/40'"
                        >
                            <input
                                type="radio"
                                name="fulfillment_type"
                                value="pickup"
                                class="mt-1 border-border text-primary focus:ring-primary"
                                x-model="fulfillment"
                                @checked(old('fulfillment_type', 'pickup') === 'pickup')
                            >
                            <span>
                                <span class="block text-sm font-semibold text-foreground">Ambil di tempat</span>
                                <span class="mt-0.5 block text-xs text-muted">Datang ke toko sesuai jadwal.</span>
                            </span>
                        </label>

                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition"
                            :class="fulfillment === 'delivery' ? 'border-primary bg-primary-soft' : (deliveryEnabled ? 'border-border bg-background hover:border-primary/40' : 'border-border bg-background opacity-60')"
                        >
                            <input
                                type="radio"
                                name="fulfillment_type"
                                value="delivery"
                                class="mt-1 border-border text-primary focus:ring-primary"
                                x-model="fulfillment"
                                :disabled="!deliveryEnabled"
                                @checked(old('fulfillment_type') === 'delivery')
                            >
                            <span>
                                <span class="block text-sm font-semibold text-foreground">Delivery</span>
                                <span class="mt-0.5 block text-xs text-muted">
                                    @if ($deliveryEnabled)
                                        Maks {{ \App\Models\BusinessSetting::current()->delivery_max_radius_km ?? 20 }} km dari toko.
                                    @else
                                        Lokasi toko belum diatur — hubungi admin.
                                    @endif
                                </span>
                            </span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('fulfillment_type')" class="mt-2" />

                    <div class="mt-4 border-t border-border pt-4" x-show="fulfillment === 'pickup'" x-cloak>
                        <h3 class="text-sm font-semibold text-foreground">Jadwal ambil</h3>
                        <p class="mt-1 text-xs text-muted">Minimal siap {{ $minReadyMinutes }} menit setelah pesanan masuk. Slot mengikuti jam buka toko.</p>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="pickup_date" value="Tanggal" />
                                <select
                                    id="pickup_date"
                                    name="pickup_date"
                                    class="mt-1 w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    x-model="scheduleDate"
                                    @change="loadSlots()"
                                    :disabled="fulfillment !== 'pickup'"
                                    required
                                >
                                    @foreach ($bookableDates as $date)
                                        <option value="{{ $date }}" @selected(old('pickup_date', $bookableDates[0] ?? '') === $date)>{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d F Y') }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('pickup_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="time_slot" value="Slot waktu" />
                                <select
                                    id="time_slot"
                                    name="time_slot"
                                    class="mt-1 w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    :disabled="fulfillment !== 'pickup'"
                                    required
                                >
                                    <template x-for="slot in slots" :key="slot">
                                        <option
                                            :value="slot"
                                            x-text="slot"
                                            :selected="slot === @js(old('time_slot', $timeSlots[0] ?? ''))"
                                        ></option>
                                    </template>
                                    @if (old('time_slot') && ! in_array(old('time_slot'), $timeSlots, true))
                                        <option value="{{ old('time_slot') }}" selected>{{ old('time_slot') }}</option>
                                    @endif
                                </select>
                                <x-input-error :messages="$errors->get('time_slot')" class="mt-2" />
                                <p class="mt-1 text-xs text-muted" x-show="slots.length === 0" x-cloak>Tidak ada slot tersedia di tanggal ini.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-border pt-4" x-show="fulfillment === 'delivery'" x-cloak>
                        <h3 class="text-sm font-semibold text-foreground">Alamat delivery</h3>
                        <p class="mt-1 text-xs text-muted">Klik peta atau isi koordinat — ongkir dihitung otomatis dari jarak ke toko.</p>

                        <div class="mt-3">
                            <x-input-label for="delivery_address" value="Alamat lengkap" />
                            <textarea
                                id="delivery_address"
                                name="delivery_address"
                                rows="2"
                                maxlength="500"
                                placeholder="Nama jalan, nomor, patokan"
                                :disabled="fulfillment !== 'delivery'"
                                class="mt-1 w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                            >{{ old('delivery_address') }}</textarea>
                            <x-input-error :messages="$errors->get('delivery_address')" class="mt-2" />
                        </div>

                        <input type="hidden" name="delivery_latitude" id="delivery_latitude" x-ref="latInput" :value="lat ?? @js(old('delivery_latitude'))">
                        <input type="hidden" name="delivery_longitude" id="delivery_longitude" x-ref="lngInput" :value="lng ?? @js(old('delivery_longitude'))">

                        <div id="delivery-map" class="mt-3 h-64 w-full overflow-hidden rounded-xl border border-border bg-background" aria-label="Peta alamat delivery"></div>

                        <div class="mt-3 rounded-xl border border-border bg-background p-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <span class="text-muted">Jarak</span>
                                <span class="font-semibold tabular-nums text-foreground" x-text="quote.distance_km !== null ? quote.distance_km + ' km' : '—'"></span>
                            </div>
                            <div class="mt-1 flex justify-between gap-3">
                                <span class="text-muted">Ongkir</span>
                                <span class="font-semibold tabular-nums text-foreground" x-text="quote.formatted_fee ?? '—'"></span>
                            </div>
                            <div class="mt-1 flex justify-between gap-3" x-show="quote.discount > 0" x-cloak>
                                <span class="text-muted">Diskon belanja</span>
                                <span class="font-semibold tabular-nums text-emerald-700" x-text="'−' + (quote.formatted_discount || '')"></span>
                            </div>
                            <p class="mt-2 text-xs text-red-600" x-show="quote.error" x-text="quote.error" x-cloak></p>
                            <p class="mt-2 text-xs text-muted" x-show="! quote.error && quote.within_radius === false" x-cloak>
                                Melebihi radius maks {{ \App\Models\BusinessSetting::current()->delivery_max_radius_km ?? 20 }} km.
                            </p>
                        </div>
                        <x-input-error :messages="$errors->get('delivery_latitude')" class="mt-2" />
                        <x-input-error :messages="$errors->get('delivery_longitude')" class="mt-2" />

                        <div class="mt-4">
                            <x-input-label value="Jadwal kirim" />
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <label class="flex items-start gap-2 rounded-lg border p-3 transition cursor-pointer"
                                    :class="deliveryMode === 'asap' ? 'border-primary bg-primary-soft' : 'border-border bg-surface'">
                                    <input type="radio" name="delivery_mode" value="asap" class="mt-0.5 border-border text-primary focus:ring-primary" x-model="deliveryMode" @checked(old('delivery_mode', 'asap') === 'asap')>
                                    <span>
                                        <span class="block text-sm font-semibold text-foreground">Segera setelah selesai</span>
                                        <span class="mt-0.5 block text-xs text-muted">Dikirim begitu pesanan READY.</span>
                                    </span>
                                </label>
                                <label class="flex items-start gap-2 rounded-lg border p-3 transition cursor-pointer"
                                    :class="deliveryMode === 'scheduled' ? 'border-primary bg-primary-soft' : 'border-border bg-surface'">
                                    <input type="radio" name="delivery_mode" value="scheduled" class="mt-0.5 border-border text-primary focus:ring-primary" x-model="deliveryMode" @checked(old('delivery_mode') === 'scheduled')>
                                    <span>
                                        <span class="block text-sm font-semibold text-foreground">Jadwal</span>
                                        <span class="mt-0.5 block text-xs text-muted">Pilih tanggal + slot jam buka.</span>
                                    </span>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('delivery_mode')" class="mt-2" />
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2" x-show="deliveryMode === 'scheduled'" x-cloak>
                            <div>
                                <x-input-label for="delivery_date" value="Tanggal kirim" />
                                <select
                                    id="delivery_date"
                                    name="pickup_date"
                                    class="mt-1 w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    x-model="scheduleDate"
                                    @change="loadSlots()"
                                    :disabled="fulfillment !== 'delivery' || deliveryMode !== 'scheduled'"
                                >
                                    @foreach ($bookableDates as $date)
                                        <option value="{{ $date }}" @selected(old('pickup_date', $bookableDates[0] ?? '') === $date)>{{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('d F Y') }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('pickup_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="delivery_slot" value="Slot jam" />
                                <select
                                    id="delivery_slot"
                                    name="time_slot"
                                    class="mt-1 w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    :disabled="fulfillment !== 'delivery' || deliveryMode !== 'scheduled'"
                                >
                                    <template x-for="slot in slots" :key="'d-' + slot">
                                        <option :value="slot" x-text="slot" :selected="slot === @js(old('time_slot', $timeSlots[0] ?? ''))"></option>
                                    </template>
                                </select>
                                <x-input-error :messages="$errors->get('time_slot')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-border pt-4">
                        <x-input-label for="customer_note" value="Catatan pesanan (opsional)" />
                        <textarea
                            id="customer_note"
                            name="customer_note"
                            rows="3"
                            maxlength="1000"
                            placeholder="misal: tolong rangkum hasil fotokopi per map"
                            class="mt-1 w-full rounded-lg border border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"
                        >{{ old('customer_note') }}</textarea>
                        <x-input-error :messages="$errors->get('customer_note')" class="mt-2" />
                    </div>

                    <div class="mt-4 border-t border-border pt-4">
                        <x-input-label for="files" value="Upload file (opsional)" />
                        <input
                            id="files"
                            name="files[]"
                            type="file"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.txt,.zip"
                            class="mt-1 block w-full text-sm text-foreground file:mr-3 file:rounded-lg file:border-0 file:bg-primary-soft file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary hover:file:bg-primary/10"
                        >
                        <p class="mt-1 text-xs leading-relaxed text-muted">
                            Maksimal 5 file, tiap file maks 5 MB. Format: PDF, JPG, PNG, WEBP, DOC, DOCX, TXT, ZIP.
                        </p>
                        <x-input-error :messages="$errors->get('files')" class="mt-2" />
                        <x-input-error :messages="$errors->get('files.0')" class="mt-2" />
                    </div>
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="rounded-2xl border border-border bg-surface p-6 shadow-sm lg:sticky lg:top-24">
                    <p class="text-xs font-semibold uppercase tracking-wide text-primary">Ringkasan</p>

                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Jumlah item</dt>
                            <dd class="font-medium tabular-nums text-foreground">{{ $lines->sum('quantity') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Subtotal</dt>
                            <dd class="font-medium tabular-nums text-foreground">{{ \App\Support\Cart::formatAmount($subtotal) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4" x-show="fulfillment === 'delivery'" x-cloak>
                            <dt class="text-muted">Ongkir</dt>
                            <dd class="font-medium tabular-nums text-foreground" x-text="quote.formatted_fee ?? '—'"></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-border pt-3">
                            <dt class="font-semibold text-foreground">Total</dt>
                            <dd class="text-lg font-bold tabular-nums text-foreground" x-text="formattedTotal"></dd>
                        </div>

                        <div x-show="isDownPayment" x-cloak class="rounded-xl border border-primary-line bg-primary-soft p-3">
                            <p class="text-xs font-semibold text-primary">Uang muka (DP) 50%</p>
                            <p class="mt-1 text-sm font-bold tabular-nums text-foreground">
                                Bayar sekarang: <span x-text="formattedDue">{{ \App\Support\Cart::formatAmount($amountDue) }}</span>
                            </p>
                            <p class="mt-1 text-xs leading-relaxed text-muted">
                                Sisa <span x-text="formattedRemaining">{{ \App\Support\Cart::formatAmount($remaining) }}</span> dibayar saat pesanan diterima.
                            </p>
                        </div>
                        <div x-show="! isDownPayment" x-cloak class="flex items-center justify-between gap-4">
                            <dt class="text-muted">Bayar sekarang</dt>
                            <dd class="font-bold tabular-nums text-foreground" x-text="formattedDue">{{ \App\Support\Cart::formatAmount($amountDue) }}</dd>
                        </div>
                    </dl>

                    <x-primary-button class="mt-6 w-full">Buat pesanan</x-primary-button>

                    <p class="mt-3 text-center text-xs leading-relaxed text-muted">
                        Bayar via QRIS atau transfer bank setelah pesanan dibuat. Harga dicek ulang oleh server.
                    </p>
                </div>
            </aside>
        </form>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
        <script>
            function checkoutForm(config) {
                return {
                    fulfillment: @js(old('fulfillment_type', 'pickup')),
                    deliveryMode: @js(old('delivery_mode', 'asap')),
                    deliveryEnabled: config.deliveryEnabled,
                    scheduleDate: config.defaultDate,
                    slots: @js($timeSlots),
                    lat: @js(old('delivery_latitude') !== null && old('delivery_latitude') !== '' ? (float) old('delivery_latitude') : null),
                    lng: @js(old('delivery_longitude') !== null && old('delivery_longitude') !== '' ? (float) old('delivery_longitude') : null),
                    quote: {
                        distance_km: null,
                        fee: 0,
                        discount: 0,
                        formatted_fee: null,
                        formatted_discount: null,
                        within_radius: true,
                        error: null,
                    },
                    isDownPayment: @js($isDownPayment),
                    baseSubtotal: @js($subtotal),
                    dpThreshold: @js((float) config('midtrans.dp_threshold', 100000)),
                    dpPercent: @js((float) config('midtrans.dp_percent', 50)),
                    map: null,
                    marker: null,

                    get deliveryFee() {
                        return this.fulfillment === 'delivery' ? (this.quote.fee || 0) : 0;
                    },
                    get total() {
                        return this.baseSubtotal + this.deliveryFee;
                    },
                    get formattedTotal() {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(this.total));
                    },
                    get amountDue() {
                        if (this.total > this.dpThreshold) {
                            return Math.round(this.total * this.dpPercent / 100);
                        }
                        return Math.round(this.total);
                    },
                    get isDp() {
                        return this.total > this.dpThreshold;
                    },
                    get formattedDue() {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(this.amountDue);
                    },
                    get formattedRemaining() {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(this.total - this.amountDue));
                    },

                    init() {
                        this.isDownPayment = this.isDp;
                        this.loadSlots();
                        this.$watch('fulfillment', () => {
                            this.isDownPayment = this.isDp;
                            if (this.fulfillment === 'delivery') {
                                this.initMap();
                                if (this.lat && this.lng) this.fetchQuote();
                            }
                        });
                        this.$watch('deliveryMode', () => { this.isDownPayment = this.isDp; });
                        document.addEventListener('DOMContentLoaded', () => this.initMap());
                    },

                    async loadSlots() {
                        if (!this.scheduleDate) return;
                        try {
                            const res = await fetch(config.slotsUrl + '?date=' + encodeURIComponent(this.scheduleDate), {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            if (!res.ok) return;
                            const data = await res.json();
                            this.slots = data.slots || [];
                        } catch (e) { /* keep server-rendered slots */ }
                    },

                    initMap() {
                        if (this.fulfillment !== 'delivery' || this.map) return;
                        if (typeof L === 'undefined') {
                            window.setTimeout(() => this.initMap(), 50);
                            return;
                        }
                        const el = document.getElementById('delivery-map');
                        if (!el) return;

                        const storeLat = @json($storeLatitude);
                        const storeLng = @json($storeLongitude);
                        const hasPin = this.lat !== null && this.lng !== null;
                        const initial = hasPin ? [this.lat, this.lng] : (storeLat && storeLng ? [parseFloat(storeLat), parseFloat(storeLng)] : [-6.914744, 107.609781]);

                        this.map = L.map(el).setView(initial, hasPin ? 15 : 12);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(this.map);

                        if (storeLat && storeLng) {
                            L.marker([parseFloat(storeLat), parseFloat(storeLng)]).addTo(this.map)
                                .bindPopup('Toko');
                        }

                        if (hasPin) {
                            this.marker = L.marker([this.lat, this.lng]).addTo(this.map);
                        }

                        this.map.on('click', (e) => {
                            this.lat = e.latlng.lat;
                            this.lng = e.latlng.lng;
                            if (this.marker) this.marker.setLatLng(e.latlng);
                            else this.marker = L.marker(e.latlng).addTo(this.map);
                            this.fetchQuote();
                        });
                    },

                    async fetchQuote() {
                        if (this.lat === null || this.lng === null) return;
                        this.quote.error = null;
                        try {
                            const url = config.quoteUrl
                                + '?latitude=' + encodeURIComponent(this.lat)
                                + '&longitude=' + encodeURIComponent(this.lng)
                                + '&subtotal=' + encodeURIComponent(this.baseSubtotal);
                            const res = await fetch(url, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                            });
                            const data = await res.json();
                            if (!data.ok) {
                                this.quote.error = data.message || 'Gagal hitung ongkir.';
                                return;
                            }
                            this.quote = data;
                            this.isDownPayment = this.isDp;
                        } catch (e) {
                            this.quote.error = 'Gagal hitung ongkir. Coba lagi.';
                        }
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
