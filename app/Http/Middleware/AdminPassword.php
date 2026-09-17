<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Hash;

class AdminPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $adminPassword = config('app.admin.password');
        if (!$adminPassword) {
            return response()->json(['message' => 'Admin password not set. Admin panel unavailable.'], 500);
        }

        $cookiePassword = $request->cookie('admin_pass');
        if (!$cookiePassword || !Hash::check($adminPassword, $cookiePassword)) {
            return response()->redirectToRoute('admin.login');
        }

        return $next($request);
    }
}