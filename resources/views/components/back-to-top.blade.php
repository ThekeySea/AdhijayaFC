<div
    x-data="{
        visible: false,
        init() {
            const check = () => {
                const hero = document.getElementById('hero');
                const threshold = hero ? hero.offsetHeight : Math.max(320, window.innerHeight * 0.5);
                this.visible = window.scrollY >= threshold;
            };
            window.addEventListener('scroll', check, { passive: true });
            check();
        },
        toTop() {
            const hero = document.getElementById('hero');
            if (hero) {
                hero.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
    }"
    x-show="visible"
    x-transition:enter="transition duration-200 ease-out"
    x-transition:enter-start="translate-y-2 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition duration-150 ease-in"
    x-transition:leave-start="translate-y-0 opacity-100"
    x-transition:leave-end="translate-y-2 opacity-0"
    x-cloak
    class="fixed bottom-[calc(10.75rem+env(safe-area-inset-bottom))] right-4 z-40 sm:bottom-[5.75rem] sm:right-6"
>
    <button
        type="button"
        @click="toTop()"
        class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-white/20 bg-foreground/85 text-white shadow-[0_8px_24px_-6px_rgba(15,23,42,0.45)] ring-1 ring-white/25 backdrop-blur-sm transition hover:bg-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-background"
        aria-label="Kembali ke atas"
        title="Kembali ke atas"
    >
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
        </svg>
    </button>
</div>
