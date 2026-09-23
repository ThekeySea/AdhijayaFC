document.addEventListener('alpine:init', function () {
    window.Alpine.data('paymentOverlay', function (config) {
        return {
            open: false,
            loading: false,
            method: 'qris',
            bank: 'bca',
            error: '',
            statusMessage: '',
            charge: null,
            pollTimer: null,
            copied: false,
            skipping: false,

            openOverlay: function () {
                this.open = true;
                this.error = '';
                document.body.classList.add('overflow-hidden');
                if (!this.charge) {
                    this.createCharge();
                } else {
                    this.startPolling();
                }
            },

            closeOverlay: function () {
                this.open = false;
                this.stopPolling();
                document.body.classList.remove('overflow-hidden');
            },

            /**
             * ✕ / Tutup = lewati pembayaran, anggap selesai.
             */
            skipPayment: function () {
                var self = this;
                if (self.skipping) {
                    return;
                }
                self.skipping = true;
                self.error = '';

                fetch(config.skipUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({}),
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(data.message || 'Gagal melewati pembayaran.');
                            }
                            return data;
                        });
                    })
                    .then(function () {
                        self.stopPolling();
                        self.statusMessage = 'Pembayaran dilewati. Memuat ulang...';
                        setTimeout(function () {
                            window.location.reload();
                        }, 600);
                    })
                    .catch(function (err) {
                        self.skipping = false;
                        self.closeOverlay();
                        self.statusMessage = err.message || 'Terjadi kesalahan.';
                    });
            },

            selectMethod: function (method) {
                this.method = method;
                this.charge = null;
                this.error = '';
                this.createCharge();
            },

            selectBank: function (bank) {
                this.bank = bank;
                if (this.method === 'bank_transfer') {
                    this.charge = null;
                    this.createCharge();
                }
            },

            createCharge: function () {
                var self = this;
                self.loading = true;
                self.error = '';
                self.statusMessage = '';

                fetch(config.payUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        method: self.method,
                        bank: self.method === 'bank_transfer' ? self.bank : null,
                    }),
                })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            if (!response.ok) {
                                throw new Error(data.message || 'Gagal menyiapkan pembayaran.');
                            }
                            return data;
                        });
                    })
                    .then(function (data) {
                        self.charge = data;
                        self.loading = false;
                        self.statusMessage = 'Siap dibayar.';
                        self.startPolling();
                    })
                    .catch(function (err) {
                        self.loading = false;
                        self.error = err.message || 'Terjadi kesalahan pembayaran.';
                    });
            },

            qrImageSrc: function () {
                if (!this.charge) {
                    return '';
                }
                if (this.charge.qr_code) {
                    return this.charge.qr_code;
                }
                if (this.charge.qr_string) {
                    return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=10&data='
                        + encodeURIComponent(this.charge.qr_string);
                }
                return '';
            },

            copyVa: function () {
                var self = this;
                if (!self.charge || !self.charge.va_number) {
                    return;
                }
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(self.charge.va_number).then(function () {
                        self.copied = true;
                        setTimeout(function () {
                            self.copied = false;
                        }, 2000);
                    });
                }
            },

            startPolling: function () {
                var self = this;
                self.stopPolling();
                self.pollTimer = setInterval(function () {
                    fetch(config.statusUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    })
                        .then(function (response) {
                            return response.json();
                        })
                        .then(function (data) {
                            if (data.is_paid) {
                                self.stopPolling();
                                self.statusMessage = 'Pembayaran berhasil. Memuat ulang...';
                                setTimeout(function () {
                                    window.location.reload();
                                }, 800);
                            }
                        })
                        .catch(function () {
                            /* polling silent fail */
                        });
                }, 3000);
            },

            stopPolling: function () {
                if (this.pollTimer) {
                    clearInterval(this.pollTimer);
                    this.pollTimer = null;
                }
            },
        };
    });
});
