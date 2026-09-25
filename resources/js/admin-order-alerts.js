let audioCtx = null;

function ensureAudioContext() {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) {
        return null;
    }

    if (!audioCtx) {
        try {
            audioCtx = new AudioContextClass();
        } catch {
            return null;
        }
    }

    if (audioCtx.state === 'suspended') {
        audioCtx.resume().catch(() => {});
    }

    return audioCtx;
}

function unlockAudioOnGesture() {
    const unlock = () => {
        const ctx = ensureAudioContext();
        if (ctx && ctx.state === 'running') {
            ['pointerdown', 'keydown', 'touchstart', 'click'].forEach((evt) => {
                document.removeEventListener(evt, unlock, true);
            });
        }
    };

    ['pointerdown', 'keydown', 'touchstart', 'click'].forEach((evt) => {
        document.addEventListener(evt, unlock, true);
    });

    unlock();
}

function playTing() {
    try {
        const ctx = ensureAudioContext();
        if (!ctx) {
            return;
        }

        const play = () => {
            const now = ctx.currentTime;
            const notes = [880, 1174.66];

            notes.forEach((freq, index) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                const start = now + index * 0.12;

                osc.type = 'sine';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0.0001, start);
                gain.gain.exponentialRampToValueAtTime(0.25, start + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, start + 0.28);

                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(start);
                osc.stop(start + 0.32);
            });
        };

        if (ctx.state === 'suspended') {
            ctx.resume().then(play).catch(() => {});
        } else {
            play();
        }
    } catch {
    }
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function showOrderToast(payload) {
    const existing = document.getElementById('order-created-toast');
    if (existing) {
        existing.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'order-created-toast';
    toast.className = 'fixed right-4 top-4 z-[80] w-[min(22rem,calc(100vw-2rem))] rounded-2xl border border-primary-line bg-surface p-4 shadow-lg';
    toast.setAttribute('role', 'status');
    toast.innerHTML = `
        <p class="text-xs font-semibold uppercase tracking-wide text-primary">Pesanan baru</p>
        <p class="mt-1 text-sm font-semibold text-foreground">${escapeHtml(payload.order_number)}</p>
        <p class="mt-0.5 text-sm text-muted">${escapeHtml(payload.customer_name)} · ${escapeHtml(payload.total)}</p>
        <div class="mt-3 flex gap-2">
            <a href="${escapeHtml(payload.url ?? '#')}" class="inline-flex min-h-9 items-center rounded-lg bg-primary px-3 text-xs font-semibold text-white transition hover:opacity-90">Lihat detail</a>
            <button type="button" data-dismiss-toast class="inline-flex min-h-9 items-center rounded-lg border border-border px-3 text-xs font-semibold text-muted transition hover:text-foreground">Tutup</button>
        </div>
    `;

    toast.querySelector('[data-dismiss-toast]')?.addEventListener('click', () => toast.remove());
    document.body.appendChild(toast);

    window.setTimeout(() => {
        if (toast.isConnected) {
            toast.remove();
        }
    }, 8000);
}

function bumpPendingCount() {
    const card = document.querySelector('[data-pending-count]');
    if (!card) {
        return;
    }

    const value = Number.parseInt(card.textContent.trim(), 10);
    if (!Number.isNaN(value)) {
        card.textContent = String(value + 1);
    }
}

function adjustStatusCount(status, delta) {
    if (!status) {
        return;
    }

    const card = document.querySelector(`[data-status-count="${status}"]`);
    if (!card) {
        return;
    }

    const value = Number.parseInt(card.textContent.trim(), 10);
    if (!Number.isNaN(value)) {
        card.textContent = String(Math.max(0, value + delta));
    }
}

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

function handleStatusUpdate(payload) {
    if (!payload?.id) {
        return;
    }

    adjustStatusCount(payload.previous_status, -1);
    adjustStatusCount(payload.status, 1);

    const item = document.querySelector(`[data-recent-orders] [data-order-id="${payload.id}"]`);
    const badge = item?.querySelector('[data-status-badge]');
    if (badge) {
        applyBadgeClasses(badge, payload);
    }
}

function renderRecentOrder(payload) {
    if (!payload?.id || !payload?.url) {
        return;
    }

    let list = document.querySelector('[data-recent-orders]');

    if (!list) {
        const empty = document.querySelector('[data-recent-orders-empty]');
        if (!empty) {
            return;
        }

        list = document.createElement('ul');
        list.className = 'mt-4 divide-y divide-border';
        list.dataset.recentOrders = '';
        empty.replaceWith(list);
    }

    const orderId = Number(payload.id);
    if (Number.isNaN(orderId)) {
        return;
    }

    if (list.querySelector(`[data-order-id="${orderId}"]`)) {
        return;
    }

    const item = document.createElement('li');
    item.dataset.orderId = String(orderId);
    item.innerHTML = `
        <a href="${escapeHtml(payload.url)}" class="flex flex-wrap items-center justify-between gap-3 py-3 transition hover:bg-background/60">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-foreground">${escapeHtml(payload.order_number)}</p>
                <p class="mt-0.5 text-sm text-muted">${escapeHtml(payload.customer_name)} · ${escapeHtml(payload.items_count)} item</p>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-1">
                <span data-status-badge class="rounded-lg px-2 py-1 text-xs font-semibold ${escapeHtml(payload.status_badge_class)}">${escapeHtml(payload.status_label)}</span>
                <span class="text-sm font-bold tabular-nums text-foreground">${escapeHtml(payload.total)}</span>
            </div>
        </a>
    `;

    list.prepend(item);

    while (list.children.length > 5) {
        list.lastElementChild?.remove();
    }
}

function subscribe() {
    if (!window.Echo) {
        return false;
    }

    try {
        window.Echo.private('admins')
            .listen('.OrderCreated', (payload) => {
                playTing();
                showOrderToast(payload ?? {});
                bumpPendingCount();
                renderRecentOrder(payload ?? {});
            })
            .listen('.OrderStatusUpdated', (payload) => {
                handleStatusUpdate(payload ?? {});
            })
            .error((status) => {
                console.warn('[order-alerts] channel auth error', status);
            });

        const connection = window.Echo.connector?.pusher?.connection;
        if (connection) {
            connection.bind('state_change', ({ previous, current }) => {
                console.info('[order-alerts] ws:', previous, '→', current);
            });
            connection.bind('connected', () => {
                console.info('[order-alerts] terhubung ke realtime');
            });
            connection.bind('error', (err) => {
                console.warn('[order-alerts] ws error', err);
            });
        }

        return true;
    } catch (error) {
        console.warn('[order-alerts] gagal subscribe', error);
        return false;
    }
}

export function listenNewOrders() {
    unlockAudioOnGesture();

    if (!window.Echo) {
        console.warn('[order-alerts] Echo belum siap');
        return;
    }

    if (!subscribe()) {
        window.setTimeout(subscribe, 2000);
    }
}
