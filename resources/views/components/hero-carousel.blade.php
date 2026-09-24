<div
    x-data="{
        index: 0,
        count: 3,
        timer: null,
        init() {
            this.timer = window.setInterval(() => this.next(), 5000);
        },
        next() {
            this.index = (this.index + 1) % this.count;
        },
        prev() {
            this.index = (this.index - 1 + this.count) % this.count;
        },
        go(i) {
            this.index = i;
        },
    }"
    {{ $attributes }}
>
    {{ $slot }}
</div>
