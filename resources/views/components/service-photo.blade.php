@props(['service', 'alt' => null])

@php
    $label = $alt ?? ($service->name.' — contoh hasil layanan');
@endphp

<div {{ $attributes->merge(['class' => 'relative aspect-[4/3] w-full overflow-hidden rounded-xl border border-border bg-background']) }}>
    <img
        src="{{ $service->previewImageUrl() }}"
        alt="{{ $label }}"
        width="800"
        height="600"
        loading="lazy"
        class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
    >
</div>
