<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->bearerToken()
            ?? $request->header('X-API-KEY')
            ?? $request->input('api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $profile = UserProfile::where('api_key', $apiKey)->first();

        if (!$profile) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }

        $user = $profile->user;

        if (!$user || !$user->is_active) {
            return response()->json(['error' => 'Account suspended or not found'], 403);
        }

        // Attach user to request
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
