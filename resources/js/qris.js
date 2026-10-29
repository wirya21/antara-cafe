import QRCode from 'qrcode';

// QRIS dengan nominal terisi: digambar di browser dari kode yang dibuat server.
export function qrisCode(payload) {
    return {
        src: '',
        async init() {
            this.src = await QRCode.toDataURL(payload, { margin: 2, width: 520, errorCorrectionLevel: 'M' });
        },
    };
}

// Pengaturan admin: baca isi gambar QRIS statis yang diunggah supaya nominal bisa diisi otomatis.
export function qrisReader(initial) {
    return {
        payload: initial ?? '',
        state: initial ? 'ok' : 'empty',
        async read(event) {
            const file = event.target.files?.[0];
            if (!file || event.target.name !== 'image') return;

            this.state = 'reading';
            try {
                const { default: jsQR } = await import('jsqr');
                const bitmap = await createImageBitmap(file);
                const canvas = document.createElement('canvas');
                const scale = Math.min(1, 1600 / Math.max(bitmap.width, bitmap.height));
                canvas.width = Math.round(bitmap.width * scale);
                canvas.height = Math.round(bitmap.height * scale);
                const ctx = canvas.getContext('2d', { willReadFrequently: true });
                ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                const code = jsQR(ctx.getImageData(0, 0, canvas.width, canvas.height).data, canvas.width, canvas.height);

                this.payload = code?.data?.startsWith('000201') ? code.data : '';
            } catch {
                this.payload = '';
            }
            this.state = this.payload ? 'ok' : 'failed';
        },
    };
}
