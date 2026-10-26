<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * kafehub.id hanya menampilkan halaman depan dan panel pengelolanya. Alamat lain
 * (menu, kasir, admin) diarahkan ke subdomain kafe agar tidak tampil ganda di domain utama.
 * Sebaliknya, panel kafehub hanya dibuka dari kafehub.id, bukan dari subdomain kafe.
 */
class KeepLandingHostOnLanding
{
    public function handle(Request $request, Closure $next): Response
    {
        $hosts = config('kafehub.hosts');
        $isPanel = $request->is('panel', 'panel/*');

        if (! in_array($request->getHost(), $hosts, true)) {
            return $isPanel && $hosts
                ? redirect()->away($request->getScheme().'://'.reset($hosts).$request->getRequestUri())
                : $next($request);
        }

        if ($request->path() === '/' || $isPanel) {
            return $next($request);
        }

        if ($request->path() === 'kafehub') {
            return redirect('/');
        }

        return redirect()->away(rtrim(config('app.url'), '/').$request->getRequestUri());
    }
}
