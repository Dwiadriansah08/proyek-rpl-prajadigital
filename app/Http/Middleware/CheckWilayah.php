<?php

namespace App\Http\Middleware;

use App\Models\Rt;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWilayah
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $type = null): Response
    {
        $user = auth()->user();

        // Super admin can access all wilayah
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        if ($type === 'rw') {
            $rw_id = $request->route('rw') ?: $request->input('rw_id');
            // If checking RW, user must be RW and own the RW
            if ($user->hasRole('rw') && $user->rw_id == $rw_id) {
                return $next($request);
            }
        }

        if ($type === 'rt') {
            $rt_id = $request->route('rt') ?: $request->input('rt_id');
            // If checking RT, user must be RT and own the RT, or RW owning the RT
            if ($user->hasRole('rt') && $user->rt_id == $rt_id) {
                return $next($request);
            }
            if ($user->hasRole('rw')) {
                $rt = Rt::find($rt_id);
                if ($rt && $rt->rw_id == $user->rw_id) {
                    return $next($request);
                }
            }
        }

        abort(403, 'Anda tidak memiliki akses ke wilayah ini.');
    }
}
