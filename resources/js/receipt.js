// Struk: tampilkan QR ke halaman status pesanan, lalu cetak.
import QRCode from 'qrcode';

window.addEventListener('load', async () => {
    const img = document.getElementById('track-qr');
    try {
        img.src = await QRCode.toDataURL(document.body.dataset.trackUrl, { margin: 0, width: 220 });
        await img.decode();
    } catch {
        img.remove();
    }
    window.print();
});
