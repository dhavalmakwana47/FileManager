<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $module, $permission): Response
    {
        $user = auth()->user();
        if ($user->hasPermission( $module, $permission)) {
            return $next($request);
        }
        return redirect()->route('accessdenied');
    }
}
