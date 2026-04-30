<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $menus = [
            'dashboard',
            'clientes',
            'fornecedores',
            'contactos',
            'propostas',
            'calendario',
            'encomendas_clientes',
            'encomendas_fornecedores',
            'ordens_trabalho',
            'financeiro',
            'arquivo_digital',
            'utilizadores',
            'permissoes',
            'configuracoes',
            'logs',
        ];

        $actions = ['create', 'read', 'update', 'delete'];
        $permissions = collect();

        foreach ($menus as $menu) {
            foreach ($actions as $action) {
                $permissions->push(Permission::findOrCreate("{$menu}.{$action}", 'web'));
            }
        }

        // Matriz de cargos/permissões.
        $rolesMatrix = [
            'Administrador' => $permissions->pluck('name')->all(),
            'Operacional' => $this->permissionsFor([
                'dashboard',
                'clientes',
                'fornecedores',
                'contactos',
                'propostas',
                'encomendas_clientes',
                'encomendas_fornecedores',
                'ordens_trabalho',
                'calendario',
                'financeiro',
                'arquivo_digital',
            ], ['create', 'read', 'update']),
            'Basico' => ['dashboard.read'],
        ];

        foreach ($rolesMatrix as $roleName => $permissionNames) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissionNames);
        }

        // Remove cargos obsoletos mantendo apenas os definidos acima.
        $allowedRoleNames = array_keys($rolesMatrix);
        $obsoleteRoleIds = Role::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $allowedRoleNames)
            ->pluck('id');

        if ($obsoleteRoleIds->isNotEmpty()) {
            DB::table('model_has_roles')->whereIn('role_id', $obsoleteRoleIds)->delete();
            DB::table('role_has_permissions')->whereIn('role_id', $obsoleteRoleIds)->delete();
            Role::query()->whereIn('id', $obsoleteRoleIds)->delete();
        }

        $adminRole = Role::findOrCreate('Administrador', 'web');
        User::query()->where('email', 'test@example.com')->first()?->assignRole($adminRole);
    }

    private function permissionsFor(array $menuKeys, array $actions): array
    {
        $permissionNames = [];

        foreach ($menuKeys as $menu) {
            foreach ($actions as $action) {
                $permissionNames[] = "{$menu}.{$action}";
            }
        }

        return $permissionNames;
    }
}
