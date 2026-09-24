document.addEventListener('alpine:init', function () {
    window.Alpine.data('countUp', function (config) {
        var target = Math.max(0, Number(config.target) || 0);
        var suffix = config.suffix || '';
        var duration = Number(config.duration) || 1600;

        return {
            started: false,
            value: 0,
            get display() {
                return new Intl.NumberFormat('id-ID').format(Math.round(this.value)) + suffix;
            },
            start: function () {
                var self = this;
                if (self.started) {
                    return;
                }
                self.started = true;

                if (target <= 0) {
                    self.value = target;
                    return;
                }

                var startTime = null;

                function frame(now) {
                    if (startTime === null) {
                        startTime = now;
                    }

                    var progress = Math.min((now - startTime) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    self.value = target * eased;

                    if (progress < 1) {
                        window.requestAnimationFrame(frame);
                    } else {
                        self.value = target;
                    }
                }

                window.requestAnimationFrame(frame);
            },
        };
    });
});
