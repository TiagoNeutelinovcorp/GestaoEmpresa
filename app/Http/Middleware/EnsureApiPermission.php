<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $permissionAction = $this->actionFromMethod($request->method());
        $permissionBase = $this->baseFromPath($request->path());

        if ($permissionBase === null || $permissionAction === null) {
            return $next($request);
        }

        $required = is_array($permissionBase)
            ? array_map(fn ($base) => "{$base}.{$permissionAction}", $permissionBase)
            : ["{$permissionBase}.{$permissionAction}"];

        foreach ($required as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Sem permissão para esta operação.');
    }

    private function actionFromMethod(string $method): ?string
    {
        return match (strtoupper($method)) {
            'GET', 'HEAD' => 'read',
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => null,
        };
    }

    private function baseFromPath(string $path): string|array|null
    {
        $clean = preg_replace('#^api/v1/#', '', $path);
        $first = explode('/', $clean)[0] ?? '';
        $second = explode('/', $clean)[1] ?? '';

        return match ($first) {
            'me' => null,
            'tenants' => null,
            'entidades' => ['clientes', 'fornecedores'],
            'contactos' => 'contactos',
            'propostas' => 'propostas',
            'encomendas-clientes' => 'encomendas_clientes',
            'encomendas-fornecedores' => 'encomendas_fornecedores',
            'calendario' => 'calendario',
            'contas-bancarias', 'conta-corrente-clientes', 'faturas-fornecedores' => 'financeiro',
            'arquivo-digital' => 'arquivo_digital',
            'access' => $second === 'permissoes' ? 'permissoes' : 'utilizadores',
            'settings', 'lookups', 'paises', 'ivas', 'artigos' => 'configuracoes',
            'logs' => 'logs',
            default => null,
        };
    }
}
