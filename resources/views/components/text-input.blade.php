@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-border bg-surface px-3.5 py-2.5 text-sm text-foreground shadow-none transition placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20']) }}>
