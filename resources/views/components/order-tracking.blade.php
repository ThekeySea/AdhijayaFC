{{--
    Timeline tracking status pesanan.
    @param \App\Models\Order $order
--}}
@php
    $steps = [
        ['key' => 'PENDING_PAYMENT', 'label' => 'Pesanan dibuat', 'hint' => 'Menunggu pembayaran', 'dot' => 'bg-slate-500', 'text' => 'text-slate-700', 'soft' => 'bg-slate-100'],
        ['key' => 'PAID', 'label' => 'Pembayaran', 'hint' => 'Sudah dibayar', 'dot' => 'bg-emerald-600', 'text' => 'text-emerald-700', 'soft' => 'bg-emerald-50'],
        ['key' => 'PROCESSING', 'label' => 'Diproses', 'hint' => 'Sedang dikerjakan', 'dot' => 'bg-sky-600', 'text' => 'text-sky-700', 'soft' => 'bg-sky-50'],
        ['key' => 'READY', 'label' => 'Siap diambil', 'hint' => 'Siap di toko', 'dot' => 'bg-amber-500', 'text' => 'text-amber-700', 'soft' => 'bg-amber-50'],
        ['key' => 'COMPLETED', 'label' => 'Selesai', 'hint' => 'Pesanan selesai', 'dot' => 'bg-primary', 'text' => 'text-primary', 'soft' => 'bg-primary-soft'],
    ];

    $orderStatus = $order->status->value;
    $isTerminal = in_array($orderStatus, ['CANCELLED', 'PAYMENT_FAILED'], true);
    $currentIndex = collect($steps)->search(fn ($step) => $step['key'] === $orderStatus);
    $currentIndex = $currentIndex === false ? null : (int) $currentIndex;

    // PENDING_PAYMENT: step 0 aktif; setelah bayar minimal index 1.
    if (! $isTerminal && $currentIndex === null) {
        $currentIndex = 0;
    }
@endphp

<div class="rounded-2xl border border-border bg-surface p-5 sm:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold text-foreground">Lacak pesanan</h2>
            <p class="mt-1 text-sm text-muted">Ikuti progres pesanan dari pembayaran sampai selesai.</p>
        </div>
        <span class="rounded-lg border border-transparent px-3 py-1.5 text-sm font-semibold {{ $order->status->badgeClass() }}">
            {{ $order->status->label() }}
        </span>
    </div>

    @if ($isTerminal)
        <div class="mt-5 rounded-xl border border-dashed border-border bg-background px-4 py-5 text-center">
            <p class="text-sm font-medium text-foreground">
                @if ($orderStatus === 'CANCELLED')
                    Pesanan ini dibatalkan.
                @else
                    Pembayaran gagal. Coba bayar lagi atau hubungi admin.
                @endif
            </p>
            <p class="mt-1 text-xs leading-relaxed text-muted">
                Status terakhir: {{ $order->status->label() }} · Pembayaran: {{ $order->payment_status->label() }}
            </p>
        </div>
    @else
        <ol class="mt-5 space-y-0">
            @foreach ($steps as $index => $step)
                @php
                    $isDone = $currentIndex !== null && $index < $currentIndex;
                    $isCurrent = $currentIndex !== null && $index === $currentIndex;
                    $isPending = $currentIndex === null || $index > $currentIndex;
                @endphp
                <li class="relative flex gap-3 pb-5 last:pb-0">
                    @if (! $loop->last)
                        <span
                            aria-hidden="true"
                            @class([
                                'absolute left-[11px] top-6 bottom-0 w-0.5',
                                'bg-primary' => $isDone || $isCurrent,
                                'bg-border' => $isPending,
                            ])
                        ></span>
                    @endif

                    <span
                        @class([
                            'relative z-10 mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[11px] font-bold text-white',
                            $step['dot'] => $isDone || $isCurrent,
                            'border-2 border-border bg-surface text-muted' => $isPending,
                        ])
                    >
                        @if ($isDone)
                            <span aria-hidden="true">✓</span>
                        @elseif ($isCurrent)
                            <span aria-hidden="true">•</span>
                        @else
                            <span aria-hidden="true">{{ $index + 1 }}</span>
                        @endif
                    </span>

                    <div class="min-w-0 flex-1 pb-0.5">
                        <p
                            @class([
                                'text-sm font-semibold leading-tight',
                                $step['text'] => $isDone || $isCurrent,
                                'text-muted' => $isPending,
                            ])
                        >
                            {{ $step['label'] }}
                            @if ($isCurrent)
                                <span @class(['ml-1 inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold', $step['soft'], $step['text']])>
                                    Sekarang
                                </span>
                            @endif
                        </p>
                        <p class="mt-0.5 text-xs leading-relaxed text-muted">{{ $step['hint'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="mt-4 rounded-xl bg-background px-4 py-3">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted">Pembayaran</p>
            <p class="mt-1 text-sm font-medium text-foreground">{{ $order->payment_status->label() }}</p>
            @if ($order->hasDownPayment())
                <p class="mt-1 text-xs text-muted">
                    Tagihan DP {{ $order->formattedAmountDue() }} · sisa {{ $order->formattedRemaining() }} di tempat.
                </p>
            @endif
        </div>
    @endif
</div>
