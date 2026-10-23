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
    now: Date.now(),

    rupiah,

    init() {
        setInterval(() => this.refreshPending(), 10000);
        setInterval(() => (this.now = Date.now()), 15000);
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

    /** Sisa porsi dari stok bahan; null bila menu tidak punya resep. */
    canAdd(id) {
        const left = this.product(id)?.left;
        return left === null || left === undefined || this.qtyOf(id) < left;
    },

    add(id) {
        if (!this.canAdd(id)) return;
        const line = this.cart.find((l) => l.id === id);
        line ? line.qty++ : this.cart.push({ id, qty: 1, note: '' });
        this.tab = 'baru';
    },

    change(index, delta) {
        if (delta > 0 && !this.canAdd(this.cart[index].id)) return;
        this.cart[index].qty += delta;
        if (this.cart[index].qty <= 0) this.cart.splice(index, 1);
    },

    qtyOf(id) {
        return this.cart.find((l) => l.id === id)?.qty ?? 0;
    },

    get total() {
        return this.cart.reduce((sum, l) => sum + l.qty * this.product(l.id).price, 0);
    },

    /** Lama menunggu dalam menit, sejak masuk (belum bayar) atau sejak dibayar (sedang dibuat). */
    waited(order) {
        return Math.max(0, Math.floor((this.now - new Date(order.since).getTime()) / 60000));
    },

    /** Merah bila lewat perkiraan waktu siap (atau 15 mnt bila belum ada perkiraan), kuning menjelang batas. */
    waitClass(order) {
        const minutes = this.waited(order);
        const limit = order.eta || 15;
        if (minutes >= limit) return 'bg-red-600 text-white';
        if (minutes >= Math.max(1, Math.round(limit * 0.7))) return 'bg-amber-400 text-espresso-900';
        return 'bg-espresso-100 text-espresso-800';
    },

    placeholder(group) {
        return { kopi: '☕', minuman: '🧋', makanan: '🍽️' }[group] ?? '☕';
    },
});
