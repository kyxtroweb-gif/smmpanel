<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $action = $this->getActionName($request);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'description' => "{$request->method()} {$request->path()}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function getActionName(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        if ($method === 'POST') {
            // Derive action from controller if available
            $action = $request->route()?->getActionName() ?? 'unknown';
            if ($action !== 'unknown') {
                $parts = explode('\\', $action);
                $controllerAction = end($parts);
                if (str_contains($controllerAction, '@')) {
                    [$controller, $method] = explode('@', $controllerAction);
                    return "{$controller}::{$method}";
                }
            }
        }

        return "{$method} {$path}";
    }
}
