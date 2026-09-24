@props(['eyebrow', 'title', 'description' => null, 'href' => null, 'linkLabel' => null])

<div class="flex flex-wrap items-end justify-between gap-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-primary">{{ $eyebrow }}</p>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">{!! $title !!}</h2>
        @if ($description)
            <p class="mt-2 max-w-xl text-[15px] leading-relaxed text-slate-700 sm:text-base">{{ $description }}</p>
        @endif
    </div>
    @if ($href && $linkLabel)
        <a href="{{ $href }}" class="text-sm font-semibold text-primary transition hover:text-primary-dark hover:underline">
            {{ $linkLabel }} →
        </a>
    @endif
</div>
