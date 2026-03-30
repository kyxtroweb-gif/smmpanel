<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignature
{
    public function handle(Request $request, Closure $next, ?string $relative = null): Response
    {
        if ($request->hasValidSignature()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Invalid or expired signature'], 403);
        }

        return redirect('/');
    }
}
