<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Support\Photo;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        return view('admin.promotions.index', [
            'promotions' => Promotion::withCount(['orders' => fn ($q) => $q->where('status', 'paid')])->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.form', ['promotion' => new Promotion(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = Photo::store($request->file('image'), 'promotions', 1200);
        }

        Promotion::create($data);

        return redirect()->route('admin.promotions.index')->with('status', 'Promo ditambahkan.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('admin.promotions.form', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $data = $this->validated($request, $promotion);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($promotion->image_path) {
                Storage::disk('public')->delete($promotion->image_path);
            }
            $data['image_path'] = $request->hasFile('image') ? Photo::store($request->file('image'), 'promotions', 1200) : null;
        }

        $promotion->update($data);

        return redirect()->route('admin.promotions.index')->with('status', 'Promo diperbarui.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        if ($promotion->image_path) {
            Storage::disk('public')->delete($promotion->image_path);
        }

        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('status', 'Promo dihapus.');
    }

    private function validated(Request $request, ?Promotion $promotion = null): array
    {
        $request->merge(['voucher_code' => $request->filled('voucher_code') ? strtoupper(trim($request->input('voucher_code'))) : null]);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'discount_type' => ['nullable', Rule::in(array_keys(Promotion::DISCOUNT_TYPES))],
            'discount_value' => ['nullable', 'integer', 'min:0', Rule::when($request->input('discount_type') === 'percent', 'max:100')],
            'voucher_code' => ['nullable', 'alpha_dash:ascii', 'max:30', Rule::unique('promotions')->where('cafe_id', Tenant::id())->ignore($promotion)],
            'min_purchase' => ['nullable', 'integer', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        unset($data['image']);
        $data['discount_type'] ??= null;
        $data['discount_value'] = $data['discount_type'] ? (int) ($data['discount_value'] ?? 0) : 0;
        $data['min_purchase'] = (int) ($data['min_purchase'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_popup'] = $request->boolean('show_popup');

        return $data;
    }
}
