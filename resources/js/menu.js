import { savedOrders } from './order-history-store';

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
    hasHistory: savedOrders().length > 0,
    detailNote: '',
    detailOptions: [],
    detailAddons: [],
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
            this.cart = saved
                .filter((line) => products.some((p) => p.id === line.id && p.available))
                // Buang topping yang sudah dihapus admin sejak keranjang disimpan.
                .map((line) => ({ ...line, addons: (line.addons ?? []).filter((id) => this.product(line.id).addons?.some((a) => a.id === id)) }));
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
        return this.cart.reduce((sum, line) => sum + line.qty * this.unitPrice(line), 0);
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

    /** Masih boleh menambah? Dibatasi sisa porsi dari stok bahan. */
    canAdd(id, qty = 1) {
        const left = this.product(id)?.left;
        if (left === null || left === undefined) return true;
        const inCart = this.cart.filter((l) => l.id === id).reduce((sum, l) => sum + l.qty, 0);
        return inCart + qty <= left;
    },

    /** Tombol + cepat: menu bertopping dibuka dulu supaya toppingnya bisa dipilih. */
    quickAdd(product) {
        product.addons?.length ? this.openDetail(product) : this.add(product.id);
    },

    add(id, qty = 1, note = '', addons = []) {
        if (!this.canAdd(id, qty)) return;
        const sorted = [...addons].sort((a, b) => a - b);
        const line = this.cart.find((l) => l.id === id && (l.note ?? '') === note && (l.addons ?? []).join(',') === sorted.join(','));
        if (line) {
            line.qty += qty;
        } else {
            this.cart.push({ id, qty, note, addons: sorted });
        }
        this.bump = true;
        setTimeout(() => (this.bump = false), 300);
    },

    addonsOf(line) {
        const product = this.product(line.id);
        return (line.addons ?? []).map((id) => product?.addons?.find((a) => a.id === id)).filter(Boolean);
    },

    /** Harga satu porsi termasuk topping yang dipilih. */
    unitPrice(line) {
        return (this.product(line.id)?.price ?? 0) + this.addonsOf(line).reduce((sum, a) => sum + a.price, 0);
    },

    toggleAddon(id) {
        const i = this.detailAddons.indexOf(id);
        i === -1 ? this.detailAddons.push(id) : this.detailAddons.splice(i, 1);
    },

    get detailUnitPrice() {
        return this.unitPrice({ id: this.detail.id, addons: this.detailAddons });
    },

    decrement(id) {
        const index = this.cart.findLastIndex((l) => l.id === id);
        if (index === -1) return;
        this.changeLine(index, -1);
    },

    changeLine(index, delta) {
        const line = this.cart[index];
        if (delta > 0 && !this.canAdd(line.id)) return;
        line.qty += delta;
        if (line.qty <= 0) this.cart.splice(index, 1);
    },

    openDetail(product) {
        if (!product.available) return;
        this.detail = product;
        this.detailQty = 1;
        this.detailNote = '';
        this.detailOptions = [];
        this.detailAddons = [];
    },

    /** Permintaan gratis (tanpa biaya tambahan), disimpan sebagai catatan pesanan. */
    optionsFor(group) {
        return group === 'makanan'
            ? ['Lebih pedas', 'Tidak pedas', 'Tanpa sayur', 'Dibungkus']
            : ['Kurang gula', 'Tambah gula', 'Tanpa gula', 'Sedikit es', 'Tanpa es', 'Panas'];
    },

    toggleOption(option) {
        const i = this.detailOptions.indexOf(option);
        i === -1 ? this.detailOptions.push(option) : this.detailOptions.splice(i, 1);
    },

    get detailFullNote() {
        return [...this.detailOptions, this.detailNote.trim()].filter(Boolean).join(', ').slice(0, 120);
    },

    /** Minuman yang ditawarkan saat membuka menu makanan; dicatat sebagai item minuman sendiri. */
    get drinkPairings() {
        if (this.detail?.group !== 'makanan') return [];
        return this.allProducts
            .filter((p) => p.group !== 'makanan' && p.available)
            .sort((a, b) => Number(b.featured) - Number(a.featured))
            .slice(0, 8);
    },

    addDetail() {
        this.add(this.detail.id, this.detailQty, this.detailFullNote, this.detailAddons);
        this.detail = null;
    },

    focusSearch() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        this.$refs.search?.focus();
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
            kopi: { emoji: '☕', bg: 'from-[#fff1e6] to-[#ffd9bd]', tile: 'bg-[#fff1e6]' },
            minuman: { emoji: '🧋', bg: 'from-[#eaf7ef] to-[#c9ead6]', tile: 'bg-[#eaf7ef]' },
            makanan: { emoji: '🍽️', bg: 'from-[#fdecec] to-[#fbcfcf]', tile: 'bg-[#fdecec]' },
        }[group] ?? { emoji: '☕', bg: 'from-[#fff1e6] to-[#ffd9bd]', tile: 'bg-[#fff1e6]' };
    },
});
