<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $tenantId = $request->session()->get('active_tenant_id') ?? $user->preferred_tenant_id;

        $tenantIds = $user->tenants()->pluck('tenants.id')->all();
        if (empty($tenantIds)) {
            abort(403, 'Utilizador sem tenants atribuídos.');
        }

        if (! $tenantId || ! in_array((int) $tenantId, array_map('intval', $tenantIds), true)) {
            $tenantId = (int) $tenantIds[0];
        }

        $request->session()->put('active_tenant_id', $tenantId);
        if ((int) $user->preferred_tenant_id !== (int) $tenantId) {
            $user->forceFill(['preferred_tenant_id' => $tenantId])->save();
        }

        app()->instance('tenant.id', (int) $tenantId);
        $request->attributes->set('tenant_id', (int) $tenantId);

        return $next($request);
    }
}

