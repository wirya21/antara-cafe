// Kode pesanan disimpan di HP pelanggan, jadi riwayat bisa dilihat tanpa akun atau member.
const KEY = 'antara-orders';

export function savedOrders() {
    try {
        const list = JSON.parse(localStorage.getItem(KEY) || '[]');
        return Array.isArray(list) ? list.filter((code) => typeof code === 'string') : [];
    } catch {
        return [];
    }
}

export function rememberOrder(code) {
    try {
        const list = [code, ...savedOrders().filter((c) => c !== code)].slice(0, 30);
        localStorage.setItem(KEY, JSON.stringify(list));
    } catch {}
}

export function forgetOrder(code) {
    try {
        localStorage.setItem(KEY, JSON.stringify(savedOrders().filter((c) => c !== code)));
    } catch {}
}
