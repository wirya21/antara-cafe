<?php

namespace App\Http\Controllers;

use App\Support\KafehubContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Halaman depan kafehub.id untuk pemilik kafe yang ingin bergabung. */
class LandingController extends Controller
{
    /** Domain utama menampilkan halaman kafehub, subdomain kafe langsung ke menu. */
    public function home(Request $request): View|RedirectResponse
    {
        return in_array($request->getHost(), config('kafehub.hosts'), true)
            ? $this->show()
            : redirect()->route('menu');
    }

    public function show(): View
    {
        $content = KafehubContent::get();

        return view('kafehub', [
            'c' => $content,
            'joinUrl' => KafehubContent::joinUrl($content),
            'showcase' => $content['showcase'],
        ]);
    }
}
