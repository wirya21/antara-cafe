<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(WhatsApp $whatsApp): View
    {
        $qris = Setting::get('qris_image_path');

        return view('admin.settings.edit', [
            'qrisUrl' => $qris ? Storage::disk('public')->url($qris) : null,
            'bankAccounts' => Setting::get('bank_accounts'),
            'whatsappNumbers' => Setting::get('whatsapp_numbers'),
            'hasToken' => filled(Setting::get('fonnte_token')),
            'whatsappReady' => $whatsApp->enabled(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['nullable', 'image', 'max:3072'],
            'remove_image' => ['nullable', 'boolean'],
            'bank_accounts' => ['nullable', 'string', 'max:500'],
            'whatsapp_numbers' => ['nullable', 'string', 'max:200', 'regex:/^[0-9+\s,;-]*$/'],
            'fonnte_token' => ['nullable', 'string', 'max:100'],
            'remove_token' => ['nullable', 'boolean'],
        ], [
            'whatsapp_numbers.regex' => 'Nomor WhatsApp hanya boleh berisi angka, dipisah koma.',
        ]);

        $old = Setting::get('qris_image_path');
        if ($request->hasFile('image')) {
            Setting::put('qris_image_path', $request->file('image')->store('pengaturan', 'public'));
        } elseif ($request->boolean('remove_image')) {
            Setting::put('qris_image_path', null);
        }
        if ($old && $old !== Setting::get('qris_image_path')) {
            Storage::disk('public')->delete($old);
        }

        Setting::put('bank_accounts', $data['bank_accounts'] ?? null);
        Setting::put('whatsapp_numbers', $data['whatsapp_numbers'] ?? null);

        // Token tidak pernah ditampilkan lagi; kosong berarti tidak diubah.
        if ($request->boolean('remove_token')) {
            Setting::put('fonnte_token', null);
        } elseif (filled($data['fonnte_token'] ?? null)) {
            Setting::put('fonnte_token', trim($data['fonnte_token']));
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Pengaturan disimpan.');
    }

    public function testWhatsApp(WhatsApp $whatsApp): RedirectResponse
    {
        if (! $whatsApp->enabled()) {
            return back()->with('error', 'Isi token Fonnte dan nomor WhatsApp tujuan terlebih dahulu.');
        }

        return $whatsApp->send("✅ Tes notifikasi Antara Cafe berhasil.\nPesanan baru akan dikirim ke nomor ini.")
            ? back()->with('status', 'Pesan uji terkirim. Cek WhatsApp Anda.')
            : back()->with('error', 'Pesan uji gagal dikirim. Periksa token Fonnte dan pastikan perangkat WhatsApp di Fonnte terhubung.');
    }
}
