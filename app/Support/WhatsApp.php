<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Kirim notifikasi WhatsApp lewat Fonnte (https://fonnte.com). */
class WhatsApp
{
    public function enabled(): bool
    {
        return filled(Setting::get('fonnte_token')) && filled($this->targets());
    }

    public function targets(): string
    {
        // Fonnte menerima beberapa nomor dipisah koma.
        return collect(preg_split('/[\s,;]+/', (string) Setting::get('whatsapp_numbers')))
            ->filter()
            ->map(fn ($n) => preg_replace('/\D/', '', $n))
            ->map(fn ($n) => str_starts_with($n, '0') ? '62'.substr($n, 1) : $n)
            ->filter()
            ->unique()
            ->join(',');
    }

    public function send(string $message): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        try {
            $response = Http::timeout(8)
                ->withHeaders(['Authorization' => Setting::get('fonnte_token')])
                ->asForm()
                ->post('https://api.fonnte.com/send', [
                    'target' => $this->targets(),
                    'message' => $message,
                ]);

            if (! $response->successful() || $response->json('status') === false) {
                Log::warning('Notifikasi WhatsApp gagal', ['body' => $response->body()]);

                return false;
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Notifikasi WhatsApp gagal', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /** Kirim setelah respons ke pelanggan selesai, agar checkout tidak menunggu WhatsApp. */
    public function notifyLater(Order $order, string $headline): void
    {
        if (! $this->enabled()) {
            return;
        }

        $message = $this->orderMessage($order, $headline);
        dispatch(fn () => app(self::class)->send($message))->afterResponse();
    }

    public function orderMessage(Order $order, string $headline): string
    {
        $order->loadMissing('items');

        $lines = [
            "*{$headline}*",
            "Kode: *{$order->code}*",
            'Nama: '.($order->customer_name ?: '-'),
            $order->table_number ? "Meja: {$order->table_number}" : 'Dibawa pulang',
            'Bayar: '.(Order::PAYMENT_METHODS[$order->payment_method] ?? '-'),
            '',
            ...$order->items->map(fn ($i) => "{$i->quantity}x {$i->product_name}".($i->note ? " ({$i->note})" : '')),
            '',
            'Total: Rp'.number_format($order->total, 0, ',', '.'),
            route('admin.orders.show', $order),
        ];

        return implode("\n", $lines);
    }
}
