// Halaman "Pesanan saya": ambil status pesanan yang kodenya tersimpan di HP ini.
import { forgetOrder, savedOrders } from './order-history-store';

export default (dataUrl) => ({
    orders: [],
    loading: true,

    init() {
        this.load();
        setInterval(() => this.load(), 15000);
    },

    async load() {
        const codes = savedOrders();
        if (!codes.length) {
            this.loading = false;
            return;
        }
        try {
            const response = await fetch(`${dataUrl}?kode=${encodeURIComponent(codes.join(','))}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (response.ok) this.orders = (await response.json()).orders;
        } catch {}
        this.loading = false;
    },

    forget(code) {
        forgetOrder(code);
        this.orders = this.orders.filter((o) => o.code !== code);
    },

    get active() {
        return this.orders.filter((o) => ['pending', 'processing'].includes(o.stage));
    },

    get past() {
        return this.orders.filter((o) => !['pending', 'processing'].includes(o.stage));
    },
});
