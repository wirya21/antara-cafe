<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * kafehub.id hanya menampilkan halaman depan. Alamat lain (menu, kasir, admin)
 * diarahkan ke subdomain kafe agar tidak tampil ganda di domain utama.
 */
class KeepLandingHostOnLanding
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->getHost(), config('kafehub.hosts'), true) || $request->path() === '/') {
            return $next($request);
        }

        if ($request->path() === 'kafehub') {
            return redirect('/');
        }

        return redirect()->away(rtrim(config('app.url'), '/').$request->getRequestUri());
    }
}
