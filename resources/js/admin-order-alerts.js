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
        <p class="mt-1 text-sm font-semibold text-foreground">${payload.order_number ?? ''}</p>
        <p class="mt-0.5 text-sm text-muted">${payload.customer_name ?? ''} · ${payload.total ?? ''}</p>
        <div class="mt-3 flex gap-2">
            <a href="${payload.url ?? '#'}" class="inline-flex min-h-9 items-center rounded-lg bg-primary px-3 text-xs font-semibold text-white transition hover:opacity-90">Lihat detail</a>
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

function refreshRecentOrders() {
    const list = document.querySelector('[data-recent-orders]');
    if (!list) {
        return;
    }

    list.dataset.stale = '1';
    if (list.parentElement?.querySelector('[data-orders-stale-hint]')) {
        return;
    }

    const hint = document.createElement('div');
    hint.dataset.ordersStaleHint = '1';
    hint.className = 'mt-3 rounded-xl border border-dashed border-primary/40 bg-primary-soft px-3 py-2 text-xs font-medium text-primary';
    hint.textContent = 'Ada pesanan baru — muat ulang untuk memperbarui daftar.';
    list.parentElement?.appendChild(hint);
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
                refreshRecentOrders();
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
