

import Alpine from 'alpinejs';
import './pay-order.js';
import './count-up.js';

window.Alpine = Alpine;

Alpine.start();

const reverb = document.documentElement.dataset;

if (reverb.reverb === 'true') {
    if (reverb.reverbScope === 'admin') {
        import('./echo.js').then(() => import('./admin-order-alerts.js'))
            .then(({ listenNewOrders }) => listenNewOrders())
            .catch((error) => {
                console.error('[order-alerts] gagal memuat modul', error);
            });
    } else if (reverb.reverbUser) {
        const userId = Number.parseInt(reverb.reverbUser, 10);

        import('./echo.js').then(() => import('./order-status-live.js'))
            .then(({ listenOrderStatus }) => listenOrderStatus(userId))
            .catch((error) => {
                console.error('[order-status] gagal memuat modul', error);
            });
    }
}
