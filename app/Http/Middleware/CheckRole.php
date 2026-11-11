<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();
        if (! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif.');
        }

        // If no specific roles required, just proceed
        if (empty($roles)) {
            return $next($request);
        }

        if ($user->role && in_array($user->role->name, $roles)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
