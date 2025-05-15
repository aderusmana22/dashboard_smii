<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($request->routeIs('dashboard.dashboardProduction') && $user->hasRole('admin-production')) {
            return $next($request);
        }

        if ($request->routeIs('dashboard.dashboardWarehouse') && $user->hasRole('admin-shipping')) {
            return $next($request);
        }

        if ($user->hasRole('admin-production')) {
            return redirect()->route('dashboard.dashboardProduction');
        }

        if ($user->hasRole('admin-shipping')) {
            return redirect()->route('dashboard.dashboardWarehouse');
        }

        return $next($request);
    }
}
