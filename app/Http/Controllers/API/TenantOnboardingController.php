<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantOnboardingController extends Controller
{
    private function tenantId(): int
    {
        return (int) app('tenant.id');
    }

    public function show()
    {
        $row = DB::table('tenant_onboarding')->where('tenant_id', $this->tenantId())->first();
        if (! $row) {
            abort(404, 'Onboarding do tenant não encontrado.');
        }

        return response()->json([
            'tenant_id' => $row->tenant_id,
            'branding_completed' => (bool) $row->branding_completed,
            'users_completed' => (bool) $row->users_completed,
            'permissions_completed' => (bool) $row->permissions_completed,
            'checklist' => json_decode($row->checklist ?: '[]', true),
            'last_completed_step' => $row->last_completed_step,
            'completed_at' => $row->completed_at,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'branding_completed' => ['nullable', 'boolean'],
            'users_completed' => ['nullable', 'boolean'],
            'permissions_completed' => ['nullable', 'boolean'],
            'checklist' => ['nullable', 'array'],
            'last_completed_step' => ['nullable', 'string', 'max:100'],
        ]);

        $existing = DB::table('tenant_onboarding')->where('tenant_id', $this->tenantId())->first();
        if (! $existing) {
            abort(404, 'Onboarding do tenant não encontrado.');
        }

        $payload = [];
        foreach (['branding_completed', 'users_completed', 'permissions_completed', 'last_completed_step'] as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = $data[$field];
            }
        }

        if (array_key_exists('checklist', $data)) {
            $payload['checklist'] = json_encode($data['checklist']);
        }

        $isCompleted = ($payload['branding_completed'] ?? (bool) $existing->branding_completed)
            && ($payload['users_completed'] ?? (bool) $existing->users_completed)
            && ($payload['permissions_completed'] ?? (bool) $existing->permissions_completed);

        $payload['completed_at'] = $isCompleted ? now() : null;
        $payload['updated_at'] = now();

        DB::table('tenant_onboarding')
            ->where('tenant_id', $this->tenantId())
            ->update($payload);

        return $this->show();
    }
}

