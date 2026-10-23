<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(array_key_exists((string) $request->user()?->role, User::ROLES), 403);

        return $next($request);
    }
}
