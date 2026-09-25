function applyBadgeClasses(el, payload) {
    const prev = String(payload.previous_status_badge_class ?? '').split(/\s+/).filter(Boolean);
    const next = String(payload.status_badge_class ?? '').split(/\s+/).filter(Boolean);

    if (prev.length) {
        el.classList.remove(...prev);
    }
    if (next.length) {
        el.classList.add(...next);
    }
    if (payload.status_label) {
        el.textContent = payload.status_label;
    }
}

function updateBadges(payload) {
    document
        .querySelectorAll(`[data-status-badge][data-order-id="${payload.id}"]`)
        .forEach((el) => applyBadgeClasses(el, payload));
}

async function refreshTracking(payload) {
    const wrap = document.getElementById('order-tracking-wrap');
    if (!wrap || !payload.tracking_url) {
        return;
    }

    try {
        const response = await fetch(payload.tracking_url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'text/html',
            },
        });

        if (response.ok) {
            wrap.innerHTML = await response.text();
        }
    } catch {
    }
}

function updatePaymentLabel(payload) {
    const el = document.querySelector('[data-order-payment-label]');
    if (el && payload.payment_status_label) {
        el.textContent = payload.payment_status_label;
    }
}

function updateActionButtons(payload) {
    const payRoot = document.getElementById('payment-overlay-root');
    if (payRoot) {
        const needsPay = payload.payment_status !== 'PAID'
            && (payload.status === 'PENDING_PAYMENT' || payload.status === 'PAYMENT_FAILED');
        payRoot.hidden = !needsPay;
    }

    const cancelButton = document.querySelector('[data-cancel-button]');
    if (cancelButton) {
        cancelButton.hidden = !payload.can_cancel;
    }
}

function handleStatusUpdate(payload) {
    if (!payload?.id) {
        return;
    }

    updateBadges(payload);
    updatePaymentLabel(payload);
    updateActionButtons(payload);
    refreshTracking(payload);
}

function subscribe(userId) {
    if (!window.Echo) {
        return false;
    }

    try {
        window.Echo.private(`App.Models.User.${userId}`)
            .listen('.OrderStatusUpdated', (payload) => {
                console.info('[order-status] event diterima', payload?.id, payload?.status);
                handleStatusUpdate(payload ?? {});
            })
            .error((status) => {
                console.warn('[order-status] channel auth error', status);
            });

        console.info('[order-status] subscribe user', userId);

        return true;
    } catch (error) {
        console.warn('[order-status] gagal subscribe', error);
        return false;
    }
}

export function listenOrderStatus(userId) {
    if (!window.Echo) {
        console.warn('[order-status] Echo belum siap');
        return;
    }

    if (!Number.isInteger(userId) || userId <= 0) {
        return;
    }

    if (!subscribe(userId)) {
        window.setTimeout(() => subscribe(userId), 2000);
    }
}
