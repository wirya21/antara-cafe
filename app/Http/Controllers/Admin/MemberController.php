<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Support\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = Member::withCount(['orders' => fn ($q) => $q->where('status', 'paid')])
            ->withSum(['orders' => fn ($q) => $q->where('status', 'paid')], 'total')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('phone', 'like', '%'.Member::normalizePhone((string) $request->string('q')).'%')))
            ->orderByDesc('points')
            ->paginate(30)
            ->withQueryString();

        return view('admin.members.index', compact('members'));
    }

    public function edit(Member $member): View
    {
        return view('admin.members.form', compact('member'));
    }

    public function update(Request $request, Member $member): RedirectResponse
    {
        $request->merge(['phone' => Member::normalizePhone((string) $request->input('phone'))]);

        $member->update($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'min:8', 'max:20', Rule::unique('members')->where('cafe_id', Tenant::id())->ignore($member)],
            'points' => ['required', 'integer', 'min:0'],
        ]));

        return redirect()->route('admin.members.index')->with('status', 'Member diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()->route('admin.members.index')->with('status', 'Member dihapus.');
    }
}
