

import Alpine from 'alpinejs';
import './pay-order.js';
import './count-up.js';

window.Alpine = Alpine;

Alpine.start();

if (document.documentElement.dataset.reverb === 'true') {
    import('./echo.js').then(() => import('./admin-order-alerts.js'))
        .then(({ listenNewOrders }) => listenNewOrders())
        .catch((error) => {
            console.error('[order-alerts] gagal memuat modul', error);
        });
}
