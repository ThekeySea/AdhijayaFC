document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.dataset.confirm || 'Yakin?')) {
                event.preventDefault();
            }
        });
    });

    var button = document.getElementById('pay-now');
    if (!button) {
        return;
    }

    var amountLabel = button.dataset.amountLabel || 'Bayar';

    function resetButton() {
        button.disabled = false;
        button.textContent = 'Bayar sekarang — ' + amountLabel;
    }

    button.addEventListener('click', function () {
        button.disabled = true;
        button.textContent = 'Menyiapkan pembayaran...';

        fetch(button.dataset.payUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        throw new Error(data.message || 'Gagal memulai pembayaran.');
                    }
                    return data;
                });
            })
            .then(function (data) {
                if (typeof window.snap === 'undefined') {
                    throw new Error('Snap belum termuat. Muat ulang halaman.');
                }

                window.snap.pay(data.snap_token, {
                    onSuccess: function () {
                        window.location.reload();
                    },
                    onPending: function () {
                        window.location.reload();
                    },
                    onError: function () {
                        window.location.reload();
                    },
                    onClose: resetButton,
                });
            })
            .catch(function (error) {
                resetButton();
                alert(error.message || 'Terjadi kesalahan pembayaran.');
            });
    });
});
