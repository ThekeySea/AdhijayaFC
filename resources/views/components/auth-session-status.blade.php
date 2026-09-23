@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-lg border border-primary-line bg-primary-soft px-3 py-2 text-sm font-medium text-foreground']) }}>
        {{ $status }}
    </div>
@endif
