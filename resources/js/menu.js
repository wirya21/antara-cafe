const STORAGE_KEY = 'antara-cart';

const rupiah = (value) => 'Rp' + Number(value).toLocaleString('id-ID');

export default (menu, initial = {}) => ({
    menu,
    search: '',
    activeCategory: menu[0]?.id ?? null,
    cart: [],
    cartOpen: initial.openCart ?? false,
    detail: null,
    detailQty: 1,
    detailNote: '',
    customerName: initial.customerName ?? '',
    tableNumber: initial.tableNumber ?? '',
    paymentMethod: initial.paymentMethod ?? '',
    submitting: false,
    bump: false,
    autoPromos: initial.autoPromos ?? [],
    voucherUrl: initial.voucherUrl,
    voucherCode: initial.voucherCode ?? '',
    voucher: null,
    voucherError: '',
    memberPhone: initial.memberPhone ?? '',

    init() {
        if (this.voucherCode) this.applyVoucher();

        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '[]');
            const products = this.allProducts;
            this.cart = saved.filter((line) => products.some((p) => p.id === line.id && p.available));
        } catch {
            this.cart = [];
        }

        this.$watch('cart', (value) => {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
            } catch {}
        });

        this.$watch('cartOpen', (open) => document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 1024));
    },

    rupiah,

    get allProducts() {
        return this.menu.flatMap((category) => category.products);
    },

    get featured() {
        return this.allProducts.filter((p) => p.featured && p.available);
    },

    get filteredMenu() {
        const term = this.search.trim().toLowerCase();
        if (!term) return this.menu;

        return this.menu
            .map((category) => ({
                ...category,
                products: category.products.filter((p) =>
                    (p.name + ' ' + (p.description ?? '')).toLowerCase().includes(term),
                ),
            }))
            .filter((category) => category.products.length);
    },

    product(id) {
        return this.allProducts.find((p) => p.id === id);
    },

    qtyOf(id) {
        return this.cart.filter((line) => line.id === id).reduce((sum, line) => sum + line.qty, 0);
    },

    get itemCount() {
        return this.cart.reduce((sum, line) => sum + line.qty, 0);
    },

    get subtotal() {
        return this.cart.reduce((sum, line) => sum + line.qty * (this.product(line.id)?.price ?? 0), 0);
    },

    discountOf(promo) {
        if (!promo || this.subtotal < promo.min) return 0;
        const amount = promo.type === 'percent' ? Math.floor((this.subtotal * Math.min(promo.value, 100)) / 100) : promo.value;
        return Math.min(amount, this.subtotal);
    },

    get appliedPromo() {
        if (this.voucher) return this.voucher;
        return [...this.autoPromos].sort((a, b) => this.discountOf(b) - this.discountOf(a)).find((p) => this.discountOf(p) > 0) ?? null;
    },

    get discount() {
        return this.discountOf(this.appliedPromo);
    },

    get total() {
        return this.subtotal - this.discount;
    },

    async applyVoucher() {
        this.voucherError = '';
        this.voucher = null;
        const code = this.voucherCode.trim();
        if (!code) return;

        try {
            const response = await fetch(this.voucherUrl + '?code=' + encodeURIComponent(code), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await response.json();
            if (!response.ok) {
                this.voucherError = data.message ?? 'Kode voucher tidak berlaku.';
                return;
            }
            this.voucher = data;
            if (this.subtotal < data.min) this.voucherError = 'Berlaku untuk belanja minimal ' + rupiah(data.min) + '.';
        } catch {
            this.voucherError = 'Gagal memeriksa voucher. Coba lagi.';
        }
    },

    add(id, qty = 1, note = '') {
        const line = this.cart.find((l) => l.id === id && (l.note ?? '') === note);
        if (line) {
            line.qty += qty;
        } else {
            this.cart.push({ id, qty, note });
        }
        this.bump = true;
        setTimeout(() => (this.bump = false), 300);
    },

    decrement(id) {
        const index = this.cart.findLastIndex((l) => l.id === id);
        if (index === -1) return;
        this.changeLine(index, -1);
    },

    changeLine(index, delta) {
        const line = this.cart[index];
        line.qty += delta;
        if (line.qty <= 0) this.cart.splice(index, 1);
    },

    openDetail(product) {
        if (!product.available) return;
        this.detail = product;
        this.detailQty = 1;
        this.detailNote = '';
    },

    addDetail() {
        this.add(this.detail.id, this.detailQty, this.detailNote.trim());
        this.detail = null;
    },

    scrollToCategory(id) {
        this.activeCategory = id;
        document.getElementById('kategori-' + id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    clearCart() {
        this.cart = [];
        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch {}
    },

    placeholder(group) {
        return {
            kopi: { emoji: '☕', bg: 'from-[#6f4520] via-[#8c5a2b] to-[#c08a4f]' },
            minuman: { emoji: '🧋', bg: 'from-[#3f6b5a] via-[#5d8f74] to-[#a9cf9c]' },
            makanan: { emoji: '🍽️', bg: 'from-[#9a3b2a] via-[#c4613f] to-[#f0a868]' },
        }[group] ?? { emoji: '☕', bg: 'from-[#6f4520] to-[#c08a4f]' };
    },
});
