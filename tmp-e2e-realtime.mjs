const BASE = 'https://adhijaya-fc.vercel.app';

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function poll(fn, timeout = 20000, interval = 300) {
    const end = Date.now() + timeout;
    while (Date.now() < end) {
        const v = await Promise.resolve(fn()).catch(() => null);
        if (v) return v;
        await sleep(interval);
    }
    return null;
}

export default async function run(page) {
    const out = { steps: [], errors: [] };
    const log = (...a) => console.log('[' + new Date().toISOString().slice(11, 19) + ']', ...a);
    const browser = page.context().browser();

    const adminLogs = [];
    const custLogs = [];
    const custAll = [];
    page.on('console', (m) => {
        const t = m.text();
        custAll.push(`[${m.type()}] ${t.slice(0, 240)}`);
        if (m.type() === 'error' || m.type() === 'warning' || t.includes('order-status')) custLogs.push(`[${m.type()}] ${t}`);
    });
    page.on('pageerror', (e) => custLogs.push(`[uncaught] ${String(e)}`));

    const adminCtx = await browser.newContext({ locale: 'en-US' });
    const admin = await adminCtx.newPage();
    admin.on('console', (m) => {
        const t = m.text();
        if (m.type() === 'error' || m.type() === 'warning' || t.includes('order-alerts')) adminLogs.push(`[${m.type()}] ${t}`);
    });
    admin.on('pageerror', (e) => adminLogs.push(`[uncaught] ${String(e)}`));

    await admin.goto(BASE + '/login', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await admin.fill('#email', 'admin@adhijaya.test');
    await admin.fill('#password', 'AdhijayaFC2026');
    await Promise.all([
        admin.waitForURL((u) => !u.pathname.includes('/login'), { timeout: 20000 }),
        admin.click('button[type=submit]'),
    ]);
    out.steps.push('admin login: ' + admin.url());
    log('admin login ok');

    await admin.goto(BASE + '/admin', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await admin.waitForSelector('[data-status-count="PENDING_PAYMENT"]', { timeout: 15000 });
    out.adminWs = !!(await poll(() => (adminLogs.some((l) => l.includes('terhubung ke realtime')) ? true : null), 25000));
    log('admin dashboard, ws=', out.adminWs);

    await page.goto(BASE + '/register', { waitUntil: 'domcontentloaded', timeout: 30000 });
    const email = `rt2-${Date.now()}@example.com`;
    await page.fill('#name', 'Tester Realtime');
    await page.fill('#email', email);
    await page.fill('#password', 'Password123!');
    await page.fill('#password_confirmation', 'Password123!');
    await Promise.all([
        page.waitForURL((u) => !u.pathname.includes('/register'), { timeout: 20000 }),
        page.click('button[type=submit]'),
    ]);
    out.steps.push('customer register: ' + email);
    log('register ok');

    await page.goto(BASE + '/layanan/pulpen', { waitUntil: 'domcontentloaded', timeout: 30000 });
    await page.waitForSelector('#add-to-cart-form', { timeout: 15000 });
    await page.evaluate(() => document.getElementById('add-to-cart-form').requestSubmit());
    await sleep(1500);
    log('cart ok');

    await page.goto(BASE + '/checkout', { waitUntil: 'domcontentloaded', timeout: 30000 });
    if (page.url().includes('/keranjang')) {
        out.errors.push('checkout bounced to cart — cart kosong');
        return out;
    }
    await page.fill('#phone', '6281234567890');
    await page.waitForFunction(() => document.querySelectorAll('#time_slot option').length > 0, null, { timeout: 15000 });
    await page.selectOption('#time_slot', { index: 0 });
    await page.evaluate(() => document.querySelector('form[action*="checkout"]').requestSubmit());
    await page.waitForURL((u) => u.pathname.startsWith('/pesanan/'), { timeout: 20000 });
    const orderId = page.url().split('/').pop();
    out.orderId = orderId;
    const banner = await page.locator('[role=status]').first().innerText().catch(() => '');
    out.orderNumber = (banner.match(/FA-\w+/) || [])[0] ?? null;
    out.steps.push('checkout order: ' + out.orderNumber + ' id=' + orderId);
    log('checkout ok', out.orderNumber, orderId);

    out.custReverb = await page.evaluate(() => ({
        reverb: document.documentElement.dataset.reverb ?? '',
        user: document.documentElement.dataset.reverbUser ?? '',
    }));
    await sleep(3000);

    const gotOrder = await poll(() =>
        admin.locator(`[data-recent-orders] [data-order-id="${orderId}"]`).count().then((c) => (c > 0 ? true : null)), 25000);
    out.adminGotOrderCreated = !!gotOrder;
    log('admin got OrderCreated:', out.adminGotOrderCreated, 'reverb=', JSON.stringify(out.custReverb));

    const readCounts = (p) =>
        p.evaluate(() => ({
            pending: Number(document.querySelector('[data-status-count="PENDING_PAYMENT"]')?.textContent.trim()),
            paid: Number(document.querySelector('[data-status-count="PAID"]')?.textContent.trim()),
        }));

    const baseline = await readCounts(admin);
    out.baseline = baseline;
    await admin.evaluate(() => { window.__adminMarker = 'keep'; });

    await page.click('#pay-now');
    await page.waitForSelector('#payment-overlay-title', { timeout: 10000 });
    await page.locator('button', { hasText: 'Lewati pembayaran' }).last().click();

    const custPaid = await poll(async () => {
        const t = await page.locator('[data-order-payment-label]').innerText().catch(() => '');
        return t.includes('Lunas') ? t : null;
    }, 25000);
    out.custPaymentLabel = custPaid ?? 'TIMEOUT';

    const scenarioA = await poll(async () => {
        const v = await admin.evaluate((id) => ({
            marker: window.__adminMarker,
            pending: Number(document.querySelector('[data-status-count="PENDING_PAYMENT"]')?.textContent.trim()),
            paid: Number(document.querySelector('[data-status-count="PAID"]')?.textContent.trim()),
            badge: document.querySelector(`[data-recent-orders] [data-order-id="${id}"] [data-status-badge]`)?.textContent.trim() ?? '',
        }), orderId);
        if (v.marker === 'keep' && v.pending === baseline.pending - 1 && v.paid === baseline.paid + 1 && v.badge.includes('Sudah dibayar')) return v;
        return null;
    }, 25000);
    out.scenarioA = scenarioA ?? { FAIL: true, final: await admin.evaluate((id) => ({
        marker: window.__adminMarker,
        pending: Number(document.querySelector('[data-status-count="PENDING_PAYMENT"]')?.textContent.trim()),
        paid: Number(document.querySelector('[data-status-count="PAID"]')?.textContent.trim()),
        badge: document.querySelector(`[data-recent-orders] [data-order-id="${id}"] [data-status-badge]`)?.textContent.trim() ?? '',
    }), orderId) };
    out.scenarioA = { ...out.scenarioA, baseline };
    log('scenarioA:', JSON.stringify(out.scenarioA));

    await sleep(6000);
    for (let i = 0; i < 3; i++) {
        await page.evaluate(() => { window.__custMarker = 'keep'; });
        await sleep(3000);
        const still = await page.evaluate(() => window.__custMarker ?? '');
        if (still === 'keep') break;
    }
    out.markerStable = await page.evaluate(() => window.__custMarker ?? '');
    log('customer settled, marker=', out.markerStable);

    await admin.goto(`${BASE}/admin/orders/${orderId}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await admin.waitForSelector('#status', { timeout: 30000 });
    await admin.selectOption('#status', 'PROCESSING');
    try {
        const [resp] = await Promise.all([
            admin.waitForResponse((r) => r.url().includes('/status') && r.request().method() === 'PATCH', { timeout: 45000 }),
            admin.click('form:has(#status) button[type=submit]'),
        ]);
        out.adminStatusResponse = resp.status();
    } catch (e) {
        out.adminStatusResponse = 'TIMEOUT: ' + String(e).split('\n')[0];
        out.adminPageAfter = await admin.locator('body').innerText().then((t) => t.slice(0, 300)).catch(() => '');
    }
    log('admin status PATCH resp:', out.adminStatusResponse);

    const scenarioB = await poll(async () => {
        const v = await page.evaluate(() => ({
            marker: window.__custMarker,
            badge: document.querySelector('[data-status-badge][data-order-id]')?.textContent.trim() ?? '',
            tracking: document.getElementById('order-tracking-wrap')?.innerText ?? '',
            payment: document.querySelector('[data-order-payment-label]')?.textContent.trim() ?? '',
        }));
        if (v.marker === 'keep' && v.badge.includes('Sedang diproses') && v.tracking.includes('Sedang diproses')) return v;
        return null;
    }, 40000);
    out.scenarioB = scenarioB ?? { FAIL: true, final: await page.evaluate(() => ({
        marker: window.__custMarker,
        badge: document.querySelector('[data-status-badge][data-order-id]')?.textContent.trim() ?? '',
        tracking: document.getElementById('order-tracking-wrap')?.innerText.slice(0, 200) ?? '',
    })) };
    log('scenarioB:', JSON.stringify(out.scenarioB));

    out.errors.push(...adminLogs.filter((l) => l.includes('[error]') || l.includes('[uncaught]') || l.includes('error')));
    out.errors.push(...custLogs.filter((l) => l.includes('[error]') || l.includes('[uncaught]') || l.includes('gagal')));
    out.errors = [...new Set(out.errors)].slice(0, 20);
    out.custConsole = custAll.filter((l) => l.includes('order-status') || l.includes('[error]') || l.includes('[warning]') || l.includes('[uncaught]')).slice(-30);

    await adminCtx.close();
    return out;
}

