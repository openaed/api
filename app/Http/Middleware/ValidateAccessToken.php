<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AccessToken;

class ValidateAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $accessToken = AccessToken::where('token', $token)->first();

        if (!$accessToken) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        if (!$accessToken->validate()) {
            return response()->json(['message' => 'Unauthorised'], 403);
        }

        $request->attributes->set('access_token', $accessToken);
        // dd($request);

        return $next($request);
    }
}