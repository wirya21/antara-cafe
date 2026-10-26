<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\KafehubContent;
use App\Support\LineIcon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Panel untuk mengubah isi halaman depan kafehub.id. */
class KafehubController extends Controller
{
    public function edit(): View
    {
        return view('admin.kafehub.edit', [
            'c' => KafehubContent::get(),
            'icons' => LineIcon::options(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = [
            'whatsapp' => ['nullable', 'string', 'regex:/^[0-9+\s-]{8,20}$/'],
            'hero_subtitle' => ['nullable', 'string', 'max:300'],
            'features_subtitle' => ['nullable', 'string', 'max:300'],
            'reasons_subtitle' => ['nullable', 'string', 'max:300'],
            'cta_subtitle' => ['nullable', 'string', 'max:300'],
        ];
        foreach (KafehubContent::TEXTS as $key) {
            $rules[$key] ??= ['nullable', 'string', 'max:120'];
        }
        foreach (KafehubContent::LISTS as $list => $fields) {
            $rules[$list] = ['nullable', 'array', 'max:12'];
            foreach ($fields as $field) {
                $rules["{$list}.*.{$field}"] = match ($field) {
                    'icon' => ['nullable', Rule::in(array_keys(LineIcon::ICONS))],
                    'url' => ['nullable', 'url', 'max:200'],
                    'text', 'a', 'fix' => ['nullable', 'string', 'max:400'],
                    default => ['nullable', 'string', 'max:120'],
                };
            }
        }

        $data = $request->validate($rules, ['whatsapp.regex' => 'Nomor WhatsApp tidak valid.']);

        $content = [];
        foreach (KafehubContent::TEXTS as $key) {
            $content[$key] = $data[$key] ?? null;
        }
        foreach (KafehubContent::LISTS as $list => $fields) {
            // Baris yang judul/isinya kosong semua dibuang.
            $content[$list] = collect($data[$list] ?? [])
                ->map(fn ($row) => collect($fields)->mapWithKeys(fn ($f) => [$f => trim((string) ($row[$f] ?? ''))])->all())
                ->filter(fn ($row) => collect($row)->except('icon')->filter()->isNotEmpty())
                ->values()->all();
        }

        KafehubContent::save($content);

        return redirect()->route('admin.kafehub.edit')->with('status', 'Halaman kafehub.id disimpan.');
    }
}
