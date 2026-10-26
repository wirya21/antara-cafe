<?php

namespace App\Http\Controllers\Kafehub;

use App\Http\Controllers\Controller;
use App\Models\Cafe;
use App\Models\Order;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Daftar kafe pengguna kafehub: tambah, ubah, perpanjang, dan atur ulang password admin. */
class CafeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('cari'));
        $status = $request->query('status');

        $cafes = Cafe::query()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")->orWhere('owner_name', 'like', "%{$search}%")->orWhere('city', 'like', "%{$search}%")))
            ->orderBy('name')->get()
            ->when(isset(Cafe::STATUSES[$status]), fn ($c) => $c->filter(fn (Cafe $cafe) => $cafe->status() === $status));

        $orders = Order::where('status', 'paid')->where('paid_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('cafe_id, count(*) as n')->groupBy('cafe_id')->pluck('n', 'cafe_id');

        return view('panel.cafes.index', compact('cafes', 'orders', 'search', 'status'));
    }

    public function create(): View
    {
        return view('panel.cafes.form', ['cafe' => new Cafe([
            'is_active' => true,
            'is_trial' => true,
            'active_until' => now()->addDays(30),
        ])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $request->validate(['admin_username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash:ascii']]);
        $password = Str::password(12, symbols: false);

        $cafe = DB::transaction(function () use ($data, $request, $password) {
            $cafe = Cafe::create($data);
            Tenant::run($cafe, fn () => User::create([
                'name' => ($data['owner_name'] ?? null) ?: 'Admin '.$cafe->name,
                'username' => strtolower($request->input('admin_username')),
                'password' => $password,
                'role' => 'admin',
            ]));

            return $cafe;
        });

        return redirect()->route('panel.cafes.edit', $cafe)
            ->with('status', "{$cafe->name} sudah terdaftar.")
            ->with('credentials', ['username' => strtolower($request->input('admin_username')), 'password' => $password]);
    }

    public function edit(Cafe $cafe): View
    {
        return view('panel.cafes.form', [
            'cafe' => $cafe,
            'admins' => User::withoutGlobalScope('cafe')->where('cafe_id', $cafe->id)->orderByRaw("role = 'admin' desc")->orderBy('name')->get(),
            'stats' => Order::where('cafe_id', $cafe->id)->where('status', 'paid')
                ->selectRaw('count(*) as orders, coalesce(sum(total), 0) as revenue, max(paid_at) as last_paid')->first(),
            'productCount' => $cafe->products()->withoutGlobalScope('cafe')->count(),
        ]);
    }

    public function update(Request $request, Cafe $cafe): RedirectResponse
    {
        $cafe->update($this->validated($request, $cafe));

        return redirect()->route('panel.cafes.edit', $cafe)->with('status', 'Data kafe disimpan.');
    }

    /** Tambah satu bulan dari hari ini atau dari akhir langganan, mana yang lebih akhir. */
    public function extend(Cafe $cafe): RedirectResponse
    {
        $from = $cafe->active_until && $cafe->active_until->isFuture() ? $cafe->active_until : today();
        $cafe->update(['active_until' => $from->copy()->addMonthNoOverflow(), 'is_trial' => false, 'is_active' => true]);

        return back()->with('status', "Langganan {$cafe->name} aktif sampai {$cafe->active_until->translatedFormat('j F Y')}.");
    }

    public function resetPassword(Cafe $cafe, int $user): RedirectResponse
    {
        $account = User::withoutGlobalScope('cafe')->where('cafe_id', $cafe->id)->findOrFail($user);
        $password = Str::password(12, symbols: false);
        $account->update(['password' => $password]);

        return back()->with('status', "Password {$account->username} diatur ulang.")
            ->with('credentials', ['username' => $account->username, 'password' => $password]);
    }

    private function validated(Request $request, ?Cafe $cafe = null): array
    {
        $request->merge(['slug' => strtolower(trim((string) $request->input('slug')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', Rule::notIn(Cafe::RESERVED), Rule::unique('cafes')->ignore($cafe)],
            'owner_name' => ['nullable', 'string', 'max:80'],
            'owner_phone' => ['nullable', 'string', 'regex:/^[0-9+\s-]{8,20}$/'],
            'city' => ['nullable', 'string', 'max:60'],
            'active_until' => ['nullable', 'date'],
            'monthly_fee' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'slug.regex' => 'Subdomain hanya boleh huruf kecil, angka, dan tanda minus.',
            'slug.not_in' => 'Subdomain ini dipakai sistem. Pilih nama lain.',
            'slug.unique' => 'Subdomain ini sudah dipakai kafe lain.',
            'owner_phone.regex' => 'Nomor WhatsApp tidak valid.',
        ]);

        return array_merge($data, [
            'is_active' => $request->boolean('is_active'),
            'is_trial' => $request->boolean('is_trial'),
            'monthly_fee' => (int) ($data['monthly_fee'] ?? 0),
        ]);
    }
}
