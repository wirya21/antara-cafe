// Notifikasi pesanan masuk untuk admin & kasir: lencana, bunyi, dan notifikasi browser.
const STORAGE_KEY = 'antara-last-order-alert';

export default (alertsUrl, ordersUrl) => ({
    pending: 0,
    proofs: 0,
    soundOn: false,
    permission: typeof Notification === 'undefined' ? 'unsupported' : Notification.permission,
    audio: null,

    init() {
        try {
            this.soundOn = localStorage.getItem('antara-sound') === '1';
        } catch {}
        this.check(true);
        setInterval(() => this.check(false), 10000);
    },

    get lastSeen() {
        try {
            return Number(localStorage.getItem(STORAGE_KEY) || 0);
        } catch {
            return 0;
        }
    },

    set lastSeen(id) {
        try {
            localStorage.setItem(STORAGE_KEY, String(id));
        } catch {}
    },

    async check(first) {
        try {
            const response = await fetch(alertsUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) return;
            const data = await response.json();
            const isNew = data.latest > this.lastSeen;
            this.pending = data.pending;
            this.proofs = data.proofs;
            if (isNew) {
                this.lastSeen = data.latest;
                // Halaman baru dibuka: jangan berbunyi untuk pesanan lama.
                if (!first) this.announce(data.pending);
            }
        } catch {}
    },

    announce(count) {
        if (this.soundOn) this.beep();
        if (this.permission === 'granted') {
            const n = new Notification('Pesanan baru masuk', {
                body: `${count} pesanan menunggu pembayaran.`,
                tag: 'antara-order',
            });
            n.onclick = () => {
                window.focus();
                window.location.href = ordersUrl;
            };
        }
    },

    async enable() {
        this.soundOn = true;
        try {
            localStorage.setItem('antara-sound', '1');
        } catch {}
        this.beep();
        if (this.permission === 'default') {
            this.permission = await Notification.requestPermission();
        }
    },

    disable() {
        this.soundOn = false;
        try {
            localStorage.setItem('antara-sound', '0');
        } catch {}
    },

    beep() {
        try {
            this.audio ??= new (window.AudioContext || window.webkitAudioContext)();
            const ctx = this.audio;
            [0, 0.18, 0.36].forEach((offset, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.frequency.value = [880, 1175, 1568][i];
                gain.gain.setValueAtTime(0.25, ctx.currentTime + offset);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + offset + 0.16);
                osc.connect(gain).connect(ctx.destination);
                osc.start(ctx.currentTime + offset);
                osc.stop(ctx.currentTime + offset + 0.17);
            });
        } catch {}
    },
});
