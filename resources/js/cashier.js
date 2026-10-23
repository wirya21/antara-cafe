const rupiah = (value) => 'Rp' + Number(value).toLocaleString('id-ID');

export default (menu, pending, processing, pendingUrl) => ({
    menu,
    pending,
    processing,
    search: '',
    activeCategory: null,
    cart: [],
    tab: pending.length ? 'masuk' : 'baru',
    newArrivals: false,

    rupiah,

    init() {
        setInterval(() => this.refreshPending(), 10000);
    },

    async refreshPending() {
        try {
            const response = await fetch(pendingUrl, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) return;
            const fresh = await response.json();
            const known = new Set(this.pending.map((o) => o.code));
            if (fresh.pending.some((o) => !known.has(o.code)) && this.tab !== 'masuk') this.newArrivals = true;
            this.pending = fresh.pending;
            this.processing = fresh.processing;
        } catch {}
    },

    get products() {
        const term = this.search.trim().toLowerCase();
        return this.menu
            .filter((c) => this.activeCategory === null || c.id === this.activeCategory)
            .flatMap((c) => c.products)
            .filter((p) => !term || p.name.toLowerCase().includes(term));
    },

    product(id) {
        return this.menu.flatMap((c) => c.products).find((p) => p.id === id);
    },

    add(id) {
        const line = this.cart.find((l) => l.id === id);
        line ? line.qty++ : this.cart.push({ id, qty: 1, note: '' });
        this.tab = 'baru';
    },

    change(index, delta) {
        this.cart[index].qty += delta;
        if (this.cart[index].qty <= 0) this.cart.splice(index, 1);
    },

    qtyOf(id) {
        return this.cart.find((l) => l.id === id)?.qty ?? 0;
    },

    get total() {
        return this.cart.reduce((sum, l) => sum + l.qty * this.product(l.id).price, 0);
    },

    placeholder(group) {
        return { kopi: '☕', minuman: '🧋', makanan: '🍽️' }[group] ?? '☕';
    },
});
