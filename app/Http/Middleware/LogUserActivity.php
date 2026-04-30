<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->user()) {
            return $response;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        Activity::create([
            'log_name' => 'default',
            'description' => sprintf('%s %s', $request->method(), $request->path()),
            'subject_type' => null,
            'subject_id' => null,
            'causer_type' => $request->user()::class,
            'causer_id' => $request->user()->getKey(),
            'properties' => [
                'menu' => $this->extractMenu($request->path()),
                'action' => $request->method(),
                'ip' => $request->ip(),
                'device' => (string) $request->userAgent(),
            ],
            'event' => strtolower($request->method()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $response;
    }

    private function extractMenu(string $path): string
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        return $segments[1] ?? ($segments[0] ?? 'dashboard');
    }
}
