<?php

namespace App\Http\Middleware;

use App\Models\AccessToken;
use App\Models\EventLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ValidateAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->headers->get('origin');
        $origin = preg_replace('/^https?:\/\/|:\d+$/', '', $origin);

        $trustedOrigins = ['erin.openaed.org'];
        if (app()->isLocal() || $request->ip() == '127.0.0.1' || in_array($origin, $trustedOrigins)) {
            return $next($request);
        }

        $log = EventLog::create(
            [
                'id' => Str::uuid(),
                'type' => 'request',
                'description' => 'HTTP request',
                'data' => [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'body' => $request->all(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ]
            ]
        );

        $token = $request->bearerToken();
        $accessToken = AccessToken::where('token', $token)->first();

        if (!$accessToken) {
            return response()->json(['message' => 'Unauthorised'], 401);
        }

        if (!$accessToken->validate()) {
            return response()->json(['message' => 'Unauthorised'], 403);
        }

        $request->attributes->set('access_token', $accessToken);

        $log->access_token = $accessToken->token;
        $log->save();

        return $next($request);
    }
}