<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Info usaha</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-foreground">Edit info usaha</h1>
                <p class="mt-1.5 text-sm text-muted">Perbarui data yang tampil di situs pelanggan.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-muted transition hover:text-primary">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-2xl rounded-2xl border border-border bg-surface p-5 sm:p-6">
        <form method="POST" action="{{ route('admin.business-settings.update') }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <x-input-label for="name" value="Nama usaha" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $settings->name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="tagline" value="Tagline (opsional)" />
                    <x-text-input id="tagline" name="tagline" type="text" class="mt-1 block w-full" :value="old('tagline', $settings->tagline)" />
                    <x-input-error class="mt-2" :messages="$errors->get('tagline')" />
                    <p class="mt-1 text-xs text-muted">Kalimat singkat di footer beranda.</p>
                </div>

                <div>
                    <x-input-label for="about" value="Tentang kami (opsional)" />
                    <textarea id="about" name="about" rows="5" class="mt-1 block w-full rounded-lg border-border bg-surface px-3.5 py-2.5 text-sm text-foreground transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">{{ old('about', $settings->about) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('about')" />
                </div>

                <div>
                    <x-input-label for="address" value="Alamat toko (opsional)" />
                    <textarea id="address" name="address" rows="3" class="mt-1 block w-full rounded-lg border-border bg-surface px-3.5 py-2.5 text-sm text-foreground transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">{{ old('address', $settings->address) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('address')" />
                </div>

                <div>
                    <x-input-label value="Posisi lokasi di peta (opsional)" />
                    <p class="mt-1 text-xs text-muted">Klik peta untuk menandai posisi toko. Tanpa pin, peta di halaman Kontak tidak tampil.</p>
                    <div id="location-picker" class="mt-2 h-64 w-full overflow-hidden rounded-xl border border-border bg-background" aria-label="Peta pemilihan lokasi"></div>
                    <div class="mt-3 grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="latitude" value="Latitude" />
                            <x-text-input id="latitude" name="latitude" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('latitude', $settings->latitude)" placeholder="-6.914744" />
                            <x-input-error class="mt-2" :messages="$errors->get('latitude')" />
                        </div>
                        <div>
                            <x-input-label for="longitude" value="Longitude" />
                            <x-text-input id="longitude" name="longitude" type="text" inputmode="decimal" class="mt-1 block w-full" :value="old('longitude', $settings->longitude)" placeholder="107.609781" />
                            <x-input-error class="mt-2" :messages="$errors->get('longitude')" />
                        </div>
                    </div>
                    <button type="button" id="clear-location" class="mt-2 text-xs font-semibold text-muted transition hover:text-foreground">
                        Hapus pin lokasi
                    </button>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="phone" value="Telepon (opsional)" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $settings->phone)" />
                        <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                    </div>
                    <div>
                        <x-input-label for="whatsapp_number" value="WhatsApp (opsional)" />
                        <x-text-input id="whatsapp_number" name="whatsapp_number" type="text" class="mt-1 block w-full" :value="old('whatsapp_number', $settings->whatsapp_number)" placeholder="6281234567890" />
                        <x-input-error class="mt-2" :messages="$errors->get('whatsapp_number')" />
                        <p class="mt-1 text-xs text-muted">Format internasional tanpa +. Kosongkan bila pakai .env.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="hours_weekday" value="Jam Senin – Sabtu" />
                        <x-text-input id="hours_weekday" name="hours_weekday" type="text" class="mt-1 block w-full" :value="old('hours_weekday', $settings->hours_weekday)" placeholder="08.00 – 20.00" />
                        <x-input-error class="mt-2" :messages="$errors->get('hours_weekday')" />
                    </div>
                    <div>
                        <x-input-label for="hours_sunday" value="Jam Minggu" />
                        <x-text-input id="hours_sunday" name="hours_sunday" type="text" class="mt-1 block w-full" :value="old('hours_sunday', $settings->hours_sunday)" placeholder="Tutup" />
                        <x-input-error class="mt-2" :messages="$errors->get('hours_sunday')" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <x-primary-button>Simpan perubahan</x-primary-button>
            </div>
        </form>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function initLocationPicker() {
                    if (typeof L === 'undefined') {
                        window.setTimeout(initLocationPicker, 50);

                        return;
                    }

                    var latInput = document.getElementById('latitude');
                    var lngInput = document.getElementById('longitude');
                    var clearBtn = document.getElementById('clear-location');
                    var lat = latInput.value ? parseFloat(latInput.value) : null;
                    var lng = lngInput.value ? parseFloat(lngInput.value) : null;
                    var hasPin = lat !== null && lng !== null && !isNaN(lat) && !isNaN(lng);
                    var initial = hasPin ? [lat, lng] : [-6.914744, 107.609781];
                    var map = L.map('location-picker').setView(initial, hasPin ? 16 : 12);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap',
                    }).addTo(map);

                    var marker = null;

                    function setPin(nextLat, nextLng) {
                        lat = nextLat;
                        lng = nextLng;
                        latInput.value = nextLat.toFixed(7);
                        lngInput.value = nextLng.toFixed(7);

                        if (marker) {
                            marker.setLatLng([nextLat, nextLng]);
                        } else {
                            marker = L.marker([nextLat, nextLng]).addTo(map);
                        }
                    }

                    function clearPin() {
                        lat = null;
                        lng = null;
                        latInput.value = '';
                        lngInput.value = '';

                        if (marker) {
                            map.removeLayer(marker);
                            marker = null;
                        }
                    }

                    if (hasPin) {
                        marker = L.marker([lat, lng]).addTo(map);
                    }

                    map.on('click', function (event) {
                        setPin(event.latlng.lat, event.latlng.lng);
                    });

                    clearBtn.addEventListener('click', clearPin);
                }

                initLocationPicker();
            });
        </script>
    @endpush
</x-admin-layout>
