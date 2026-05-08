<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $activeTenantId = (int) ($request->attributes->get('tenant_id') ?? 0);

        $tenants = $user->tenants()
            ->select('tenants.id', 'tenants.nome', 'tenants.slug', 'tenants.settings', 'tenants.owner_user_id')
            ->orderBy('tenants.nome')
            ->get()
            ->map(fn ($tenant) => [
                'id' => $tenant->id,
                'nome' => $tenant->nome,
                'slug' => $tenant->slug,
                'settings' => $tenant->settings,
                'owner_user_id' => $tenant->owner_user_id,
                'is_active' => (int) $tenant->id === $activeTenantId,
            ]);

        return response()->json($tenants);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'settings' => ['nullable', 'array'],
        ]);

        $baseSlug = $data['slug'] ?? Str::slug($data['nome']);
        $slug = $baseSlug ?: 'tenant';
        $counter = 1;
        while (Tenant::query()->where('slug', $slug)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }

        $tenant = Tenant::query()->create([
            'nome' => $data['nome'],
            'slug' => $slug,
            'settings' => $data['settings'] ?? [],
            'owner_user_id' => $user->id,
        ]);

        $tenant->users()->syncWithoutDetaching([
            $user->id => ['role' => 'owner'],
        ]);

        $defaultPlan = DB::table('plans')->where('slug', 'free')->first();
        if ($defaultPlan) {
            DB::table('tenant_subscriptions')->insert([
                'tenant_id' => $tenant->id,
                'plan_id' => $defaultPlan->id,
                'status' => 'trialing',
                'started_at' => now(),
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
                'trial_ends_at' => now()->addDays((int) $defaultPlan->trial_days),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('tenant_onboarding')->insert([
            'tenant_id' => $tenant->id,
            'checklist' => json_encode([
                ['key' => 'branding', 'label' => 'Configurar branding da empresa', 'done' => false],
                ['key' => 'users', 'label' => 'Adicionar utilizadores', 'done' => false],
                ['key' => 'permissions', 'label' => 'Definir permissões', 'done' => false],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request->session()->put('active_tenant_id', $tenant->id);
        $user->forceFill(['preferred_tenant_id' => $tenant->id])->save();

        return response()->json($tenant, 201);
    }

    public function switch(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
        ]);

        $tenantId = (int) $data['tenant_id'];
        $allowed = $user->tenants()->where('tenants.id', $tenantId)->exists();
        if (! $allowed) {
            abort(403, 'Sem acesso ao tenant selecionado.');
        }

        $request->session()->put('active_tenant_id', $tenantId);
        $user->forceFill(['preferred_tenant_id' => $tenantId])->save();

        return response()->json(['message' => 'Tenant ativo atualizado.', 'tenant_id' => $tenantId]);
    }
}

