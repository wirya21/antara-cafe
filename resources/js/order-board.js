/**
 * Papan pesanan admin: jam tunggu berjalan dan halaman dimuat ulang sendiri
 * saat ada pesanan baru atau pesanan berpindah tahap (dari kasir lain).
 */
export default (countsUrl, initial) => ({
    now: Date.now(),
    signature: JSON.stringify(initial),

    init() {
        setInterval(() => (this.now = Date.now()), 15000);
        setInterval(() => this.check(), 12000);
    },

    async check() {
        if (document.hidden) return;
        try {
            const res = await fetch(countsUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) return;
            const { latest, ...counts } = await res.json();
            // Jangan muat ulang saat admin sedang memilih metode bayar atau menggeser tombol.
            const busy = document.activeElement?.closest('form');
            if (JSON.stringify(counts) !== this.signature && !busy) window.location.reload();
        } catch {
            // Jaringan putus sebentar: coba lagi di putaran berikutnya.
        }
    },

    waited(since) {
        return Math.max(0, Math.floor((this.now - new Date(since).getTime()) / 60000));
    },

    waitClass(since, eta) {
        const minutes = this.waited(since);
        const limit = eta || 15;
        if (minutes >= limit) return 'bg-red-600 text-white';
        if (minutes >= Math.max(1, Math.round(limit * 0.7))) return 'bg-amber-400 text-espresso-900';
        return 'bg-espresso-100 text-espresso-800';
    },
});
