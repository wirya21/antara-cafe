<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', ['users' => User::orderBy('role')->orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => new User(['role' => 'kasir'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::create($this->validated($request));

        return redirect()->route('admin.users.index')->with('status', 'Akun ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && $data['role'] !== 'admin') {
            return back()->with('error', 'Anda tidak bisa menurunkan peran akun sendiri.');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'Akun diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Akun dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash:ascii', Rule::unique('users')->ignore($user)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ]);

        $data['username'] = strtolower($data['username']);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }
}
