// Halaman status pesanan pelanggan: waktu tunggu berjalan, cek status berkala, dan notifikasi saat berubah.
import { rememberOrder } from './order-history-store';

const MESSAGES = {
    processing: (code) => ({ title: 'Pembayaran diterima ✅', body: `Pesanan ${code} sedang dibuat.` }),
    completed: (code) => ({ title: 'Pesanan siap! 🎉', body: `Pesanan ${code} sudah siap. Terima kasih sudah menunggu.` }),
    cancelled: (code) => ({ title: 'Pesanan dibatalkan', body: `Pesanan ${code} dibatalkan. Hubungi kasir bila ada pertanyaan.` }),
};

export default ({ code, stage, hasProof, since, statusUrl }) => ({
    stage,
    proof: hasProof,
    now: Date.now(),
    permission: typeof Notification === 'undefined' ? 'unsupported' : Notification.permission,
    flash: null,
    audio: null,

    init() {
        rememberOrder(code);
        if (!['pending', 'processing'].includes(stage)) return;

        setInterval(() => (this.now = Date.now()), 15000);
        setInterval(() => this.check(), 8000);
        if (this.permission === 'granted') this.registerWorker();
    },

    get waited() {
        return Math.max(0, Math.floor((this.now - new Date(since).getTime()) / 60000));
    },

    async check() {
        try {
            const response = await fetch(statusUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) return;
            const data = await response.json();
            if (data.stage !== this.stage) {
                await this.announce(data.stage);
                setTimeout(() => window.location.reload(), 3000);
            } else if (data.has_proof !== this.proof) {
                window.location.reload();
            }
            this.stage = data.stage;
            this.proof = data.has_proof;
        } catch {}
    },

    async announce(next) {
        const message = MESSAGES[next]?.(code);
        if (!message) return;
        this.flash = message;
        this.beep();
        navigator.vibrate?.([200, 100, 200, 100, 400]);

        if (this.permission !== 'granted') return;
        try {
            const registration = await this.registerWorker();
            if (registration) {
                await registration.showNotification(message.title, { body: message.body, tag: `antara-${code}`, renotify: true, vibrate: [200, 100, 200] });
            } else {
                new Notification(message.title, { body: message.body, tag: `antara-${code}` });
            }
        } catch {}
    },

    async enableAlerts() {
        this.beep(true);
        if (this.permission === 'default') {
            try {
                this.permission = await Notification.requestPermission();
            } catch {}
        }
        if (this.permission === 'granted') this.registerWorker();
    },

    async registerWorker() {
        if (!('serviceWorker' in navigator)) return null;
        try {
            await navigator.serviceWorker.register('/sw.js');
            return await navigator.serviceWorker.ready;
        } catch {
            return null;
        }
    },

    beep(quiet = false) {
        try {
            this.audio ??= new (window.AudioContext || window.webkitAudioContext)();
            const ctx = this.audio;
            ctx.resume?.();
            [0, 0.2, 0.4].forEach((offset, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.frequency.value = [784, 988, 1319][i];
                gain.gain.setValueAtTime(quiet ? 0.05 : 0.3, ctx.currentTime + offset);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + offset + 0.18);
                osc.connect(gain).connect(ctx.destination);
                osc.start(ctx.currentTime + offset);
                osc.stop(ctx.currentTime + offset + 0.2);
            });
        } catch {}
    },
});
