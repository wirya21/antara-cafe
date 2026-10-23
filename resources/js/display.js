import QRCode from 'qrcode';

export default (slides, menuUrl) => ({
    slides,
    current: 0,
    qr: '',
    clock: '',

    async init() {
        this.qr = await QRCode.toDataURL(menuUrl, { margin: 1, width: 240, color: { dark: '#2b1b0e', light: '#ffffff' } });

        if (this.slides.length > 1) {
            setInterval(() => (this.current = (this.current + 1) % this.slides.length), 8000);
        }

        this.tick();
        setInterval(() => this.tick(), 1000 * 15);

        // Muat ulang berkala agar perubahan menu & promo dari admin ikut tampil.
        setTimeout(() => window.location.reload(), 1000 * 60 * 5);
    },

    tick() {
        this.clock = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    },

    rupiah: (value) => 'Rp' + Number(value).toLocaleString('id-ID'),

    placeholder(group) {
        return {
            kopi: { emoji: '☕', bg: 'from-[#6f4520] via-[#8c5a2b] to-[#c08a4f]' },
            minuman: { emoji: '🧋', bg: 'from-[#3f6b5a] via-[#5d8f74] to-[#a9cf9c]' },
            makanan: { emoji: '🍽️', bg: 'from-[#9a3b2a] via-[#c4613f] to-[#f0a868]' },
        }[group] ?? { emoji: '☕', bg: 'from-[#6f4520] to-[#c08a4f]' };
    },
});
