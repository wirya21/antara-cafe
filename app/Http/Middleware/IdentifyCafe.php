<?php

namespace App\Http\Middleware;

use App\Models\Cafe;
use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menentukan kafe dari subdomain ({slug}.kafehub.id). Di kafehub.id sendiri tidak ada kafe aktif.
 * Alamat lain (localhost saat pengembangan) memakai kafe pertama.
 */
class IdentifyCafe
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        if (in_array($host, config('kafehub.hosts'), true)) {
            Tenant::set(null);

            return $next($request);
        }

        $suffix = '.'.config('kafehub.domain');
        $cafe = str_ends_with($host, $suffix)
            ? Cafe::findBySlug(substr($host, 0, -strlen($suffix)))
            : Cafe::orderBy('id')->first();

        if (! $cafe && str_ends_with($host, $suffix)) {
            return response()->view('tenant.unavailable', ['title' => 'Kafe belum terdaftar', 'text' => 'Alamat ini belum dipakai kafe mana pun di kafehub.'], 404);
        }

        if ($cafe && ! $cafe->isOpen()) {
            return response()->view('tenant.unavailable', ['title' => $cafe->name.' sedang tidak aktif', 'text' => 'Layanan kafe ini sedang dihentikan sementara. Silakan hubungi pemilik kafe.'], 503);
        }

        Tenant::set($cafe);
        View::share('cafe', $cafe);

        return $next($request);
    }
}
