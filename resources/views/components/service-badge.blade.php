@props(['service'])

<span {{ $attributes->merge(['class' => 'inline-flex w-fit rounded-lg px-2.5 py-1 text-xs font-semibold '.$service->badgeClass()]) }}>
    {{ $service->badgeLabel() }}
</span>
