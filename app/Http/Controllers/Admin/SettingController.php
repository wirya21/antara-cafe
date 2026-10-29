<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Busy;
use App\Support\Costing;
use App\Support\Photo;
use App\Support\Qris;
use App\Support\Tenant;
use App\Support\Theme;
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
            'qrisPayload' => Setting::get('qris_payload'),
            'qrisUrl' => $qris ? Storage::disk('public')->url($qris) : null,
            'bankAccounts' => Setting::get('bank_accounts'),
            'whatsappNumbers' => Setting::get('whatsapp_numbers'),
            'hasToken' => filled(Setting::get('fonnte_token')),
            'whatsappReady' => $whatsApp->enabled(),
            'overheads' => Costing::overheads(),
            'monthlyServings' => Costing::monthlyServings(),
            'busy' => Busy::on(),
            'busyMessage' => Setting::get('busy_message'),
            'busyExtra' => (int) Setting::get('busy_extra_minutes', '10'),
            'logoUrl' => Tenant::cafe()?->logoUrl(),
            'heroUrl' => ($hero = Setting::get('hero_image_path')) ? Storage::disk('public')->url($hero) : null,
            'heroTitle' => Setting::get('hero_title'),
            'heroSubtitle' => Setting::get('hero_subtitle'),
            'theme' => Theme::key(),
            'themeColor' => Theme::color(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'image' => ['nullable', 'image', 'max:3072'],
            'remove_image' => ['nullable', 'boolean'],
            'qris_payload' => ['nullable', 'string', 'max:512', function ($attribute, $value, $fail) {
                if (! Qris::isValid($value)) {
                    $fail('Kode QRIS tidak sah. Pastikan yang ditempel kode QRIS statis lengkap, diawali 000201.');
                }
            }],
            'bank_accounts' => ['nullable', 'string', 'max:500'],
            'whatsapp_numbers' => ['nullable', 'string', 'max:200', 'regex:/^[0-9+\s,;-]*$/'],
            'fonnte_token' => ['nullable', 'string', 'max:100'],
            'remove_token' => ['nullable', 'boolean'],
            'overheads' => ['nullable', 'array'],
            'overheads.*' => ['nullable', 'integer', 'min:0', 'max:1000000000'],
            'op_monthly_servings' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'busy_message' => ['nullable', 'string', 'max:200'],
            'busy_extra_minutes' => ['nullable', 'integer', 'min:0', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_hero_image' => ['nullable', 'boolean'],
            'hero_title' => ['nullable', 'string', 'max:80'],
            'hero_subtitle' => ['nullable', 'string', 'max:200'],
            'theme' => ['nullable', 'string', 'in:custom,'.implode(',', array_keys(Theme::PRESETS))],
            'theme_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
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
        if ($request->boolean('remove_image')) {
            Setting::put('qris_payload', null);
        } elseif ($request->has('qris_payload')) {
            Setting::put('qris_payload', filled($data['qris_payload']) ? trim($data['qris_payload']) : null);
        }

        $cafe = Tenant::cafe();
        $oldLogo = $cafe->logo_path;
        if ($request->hasFile('logo')) {
            // Disimpan apa adanya agar logo PNG transparan tetap transparan.
            $cafe->update(['logo_path' => $request->file('logo')->store('logo', 'public')]);
        } elseif ($request->boolean('remove_logo')) {
            $cafe->update(['logo_path' => null]);
        }
        if ($oldLogo && $oldLogo !== $cafe->logo_path) {
            Storage::disk('public')->delete($oldLogo);
        }

        $oldHero = Setting::get('hero_image_path');
        if ($request->hasFile('hero_image')) {
            Setting::put('hero_image_path', Photo::store($request->file('hero_image'), 'pengaturan', 1600));
        } elseif ($request->boolean('remove_hero_image')) {
            Setting::put('hero_image_path', null);
        }
        if ($oldHero && $oldHero !== Setting::get('hero_image_path')) {
            Storage::disk('public')->delete($oldHero);
        }
        Setting::put('hero_title', $data['hero_title'] ?? null);
        Setting::put('hero_subtitle', $data['hero_subtitle'] ?? null);

        Setting::put('bank_accounts', $data['bank_accounts'] ?? null);
        Setting::put('whatsapp_numbers', $data['whatsapp_numbers'] ?? null);

        foreach (array_keys(Costing::OVERHEADS) as $key) {
            Setting::put($key, (string) (int) ($data['overheads'][$key] ?? 0));
        }
        Setting::put('op_monthly_servings', (string) (int) ($data['op_monthly_servings'] ?? 0));

        Setting::put('busy_mode', $request->boolean('busy_mode') ? '1' : null);
        Setting::put('busy_message', $data['busy_message'] ?? null);
        Setting::put('busy_extra_minutes', (string) (int) ($data['busy_extra_minutes'] ?? 0));

        Setting::put('theme', $data['theme'] ?? Theme::DEFAULT);
        Setting::put('theme_color', $data['theme_color'] ?? null);

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

        return $whatsApp->send('✅ Tes notifikasi '.Tenant::cafe()?->name." berhasil.\nPesanan baru akan dikirim ke nomor ini.")
            ? back()->with('status', 'Pesan uji terkirim. Cek WhatsApp Anda.')
            : back()->with('error', 'Pesan uji gagal dikirim. Periksa token Fonnte dan pastikan perangkat WhatsApp di Fonnte terhubung.');
    }
}
