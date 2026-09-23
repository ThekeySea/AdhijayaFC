@props(['category' => null])

<div class="space-y-4">
    <div>
        <x-input-label for="name" value="Nama kategori" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category?->name)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="description" value="Deskripsi (opsional)" />
        <textarea id="description" name="description" rows="3" class="mt-1 block w-full rounded-lg border-border bg-surface px-3.5 py-2.5 text-sm text-foreground transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">{{ old('description', $category?->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div>
        <x-input-label for="sort_order" value="Urutan tampil" />
        <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full" :value="old('sort_order', $category?->sort_order ?? 0)" />
        <x-input-error class="mt-2" :messages="$errors->get('sort_order')" />
        <p class="mt-1 text-xs text-muted">Angka lebih kecil tampil lebih dulu di chip katalog.</p>
    </div>

    <label class="flex items-start gap-3">
        <input type="checkbox" name="is_active" value="1" class="mt-1 rounded border-border text-primary focus:ring-primary" @checked(old('is_active', $category?->is_active ?? true))>
        <span class="text-sm text-foreground">
            Aktif
            <span class="mt-0.5 block text-xs text-muted">Kategori aktif tampil sebagai filter di katalog.</span>
        </span>
    </label>
</div>
