@props([
    'serviceCount' => 0,
    'transactionCount' => 0,
])

<div {{ $attributes->merge(['class' => '']) }} aria-label="Statistik usaha">
    <div class="mx-auto flex max-w-6xl gap-2 px-4 pb-[calc(4.5rem+env(safe-area-inset-bottom))] pt-4 sm:gap-4 sm:px-6 sm:pb-5 sm:pt-5 lg:px-8">
        <div
            x-data="countUp({ target: {{ (int) $serviceCount }} })"
            x-init="start()"
            class="flex min-w-0 flex-1 flex-col items-center justify-center rounded-2xl border border-white/25 px-2 py-3 text-center opacity-0 sm:rounded-full sm:px-6 sm:py-4"
            :class="started && 'opacity-100 transition-opacity duration-700'"
        >
            <p class="text-[10px] font-medium leading-snug text-white/75 sm:text-xs">Jasa Kami</p>
            <p class="mt-0.5 text-xl font-bold tabular-nums tracking-tight text-white sm:mt-1 sm:text-4xl" x-text="display">0</p>
        </div>

        <div
            x-data="countUp({ target: {{ (int) $transactionCount }} })"
            x-init="start()"
            class="flex min-w-0 flex-1 flex-col items-center justify-center rounded-2xl border border-white/25 px-2 py-3 text-center opacity-0 sm:rounded-full sm:px-6 sm:py-4"
            :class="started && 'opacity-100 transition-opacity duration-700'"
        >
            <p class="text-[10px] font-medium leading-snug text-white/75 sm:text-xs">Diandalkan Oleh</p>
            <p class="mt-0.5 text-xl font-bold tabular-nums tracking-tight text-white sm:mt-1 sm:text-4xl" x-text="display">0</p>
            <p class="text-[10px] font-medium leading-snug text-white/75 sm:text-xs">Orang</p>
        </div>

        <div
            x-data="countUp({ target: 9, suffix: '+' })"
            x-init="start()"
            class="flex min-w-0 flex-1 flex-col items-center justify-center rounded-2xl border border-white/25 px-2 py-3 text-center opacity-0 sm:rounded-full sm:px-6 sm:py-4"
            :class="started && 'opacity-100 transition-opacity duration-700'"
        >
            <p class="text-[10px] font-medium leading-snug text-white/75 sm:text-xs">Telah berpengalaman</p>
            <p class="mt-0.5 text-xl font-bold tabular-nums tracking-tight text-white sm:mt-1 sm:text-4xl" x-text="display">0+</p>
            <p class="text-[10px] font-medium leading-snug text-white/75 sm:text-xs">Tahun</p>
        </div>
    </div>
</div>
