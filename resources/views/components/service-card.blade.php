@props(['service', 'cta' => 'Lihat detail', 'href' => null, 'lineClamp' => 2, 'titleTag' => 'h3', 'surface' => 'bg-surface'])

@php
$href = $href ?? route('services.show', $service);
$lineClampClass = $lineClamp === 3 ? 'line-clamp-3' : 'line-clamp-2';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "group flex flex-col rounded-2xl border border-border $surface p-5 transition hover:-translate-y-0.5 hover:border-primary/40 hover:shadow-sm"]) }}>
    <x-service-photo :service="$service" class="-mx-1 -mt-1 mb-4" />
    <div class="flex items-start justify-between gap-3">
        <x-service-badge :service="$service" />
    </div>
    <{{ $titleTag }} class="mt-4 text-base font-semibold text-foreground transition group-hover:text-primary">
        {{ $service->name }}
    </{{ $titleTag }}>
    <p class="mt-2 {{ $lineClampClass }} flex-1 text-[15px] leading-relaxed text-slate-700">{{ $service->description }}</p>
    {{ $slot }}
    <div class="mt-5 flex items-center justify-between border-t border-border pt-4">
        <span class="text-sm font-bold tabular-nums text-foreground">{{ $service->formattedPrice() }}<span class="font-medium text-muted">/{{ $service->unit }}</span></span>
        <span class="text-sm font-semibold text-primary">{{ $cta }}</span>
    </div>
</a>
