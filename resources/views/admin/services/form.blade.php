@props(['service' => null])

<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nama layanan" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $service?->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="category" value="Kategori" />
        <x-text-input id="category" name="category" type="text" class="mt-1 block w-full" :value="old('category', $service?->category)" placeholder="Fotokopi, Print, Scan, Jilid" />
        <x-input-error class="mt-2" :messages="$errors->get('category')" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="price" value="Harga (Rp)" />
            <x-text-input id="price" name="price" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('price', $service?->price)" required />
            <x-input-error class="mt-2" :messages="$errors->get('price')" />
            <p class="mt-1 text-xs text-muted">Harga contoh, dapat diubah kapan saja.</p>
        </div>

        <div>
            <x-input-label for="unit" value="Satuan" />
            <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full" :value="old('unit', $service?->unit ?? 'lembar')" required />
            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
        </div>
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
