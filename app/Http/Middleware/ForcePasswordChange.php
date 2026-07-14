<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            // Izinkan akses ke halaman change-password, logout, dan profile update password
            $allowed = ['password.change', 'password.force-update', 'logout', 'profile.update', 'password.update'];

            if (!in_array($request->route()?->getName(), $allowed)) {
                return redirect()->route('password.change');
            }
        }

        return $next($request);
    }
}
