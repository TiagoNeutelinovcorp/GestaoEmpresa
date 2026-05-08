<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantBillingController extends Controller
{
    private function tenantId(): int
    {
        return (int) app('tenant.id');
    }

    public function plans()
    {
        return response()->json(
            Plan::query()
                ->where('ativo', true)
                ->orderBy('preco_mensal')
                ->get()
        );
    }

    public function subscription()
    {
        $subscription = DB::table('tenant_subscriptions')
            ->join('plans', 'plans.id', '=', 'tenant_subscriptions.plan_id')
            ->leftJoin('plans as next_plans', 'next_plans.id', '=', 'tenant_subscriptions.next_plan_id')
            ->where('tenant_subscriptions.tenant_id', $this->tenantId())
            ->select(
                'tenant_subscriptions.*',
                'plans.nome as plan_nome',
                'plans.slug as plan_slug',
                'plans.preco_mensal as plan_preco_mensal',
                'plans.limites as plan_limites',
                'plans.features as plan_features',
                'next_plans.nome as next_plan_nome'
            )
            ->latest('tenant_subscriptions.id')
            ->first();

        if (! $subscription) {
            abort(404, 'Subscrição não encontrada para o tenant.');
        }

        $trialEndsAt = $subscription->trial_ends_at ? \Carbon\Carbon::parse($subscription->trial_ends_at) : null;
        $trialDaysLeft = $trialEndsAt ? max(0, now()->diffInDays($trialEndsAt, false)) : 0;

        return response()->json([
            ... (array) $subscription,
            'plan_limites' => json_decode($subscription->plan_limites ?: '{}', true),
            'plan_features' => json_decode($subscription->plan_features ?: '{}', true),
            'trial_days_left' => $trialDaysLeft,
        ]);
    }

    public function usage()
    {
        $subscription = $this->subscription()->getData(true);
        $limits = $subscription['plan_limites'] ?? [];
        $maxUsers = (int) ($limits['max_users'] ?? 0);

        $usersCount = DB::table('tenant_user')->where('tenant_id', $this->tenantId())->count();

        return response()->json([
            'users' => [
                'used' => $usersCount,
                'limit' => $maxUsers,
                'remaining' => max(0, $maxUsers - $usersCount),
            ],
            'premium_enabled' => (bool) (($subscription['plan_features'] ?? [])['premium'] ?? false),
            'trial_days_left' => (int) ($subscription['trial_days_left'] ?? 0),
        ]);
    }

    public function changePlan(Request $request)
    {
        $data = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $subscription = DB::table('tenant_subscriptions')
            ->where('tenant_id', $this->tenantId())
            ->latest('id')
            ->first();
        abort_if(! $subscription, 404, 'Subscrição não encontrada.');

        $currentPlan = Plan::query()->findOrFail($subscription->plan_id);
        $targetPlan = Plan::query()->findOrFail($data['plan_id']);

        if ($currentPlan->id === $targetPlan->id) {
            return response()->json(['message' => 'O tenant já está nesse plano.']);
        }

        $periodStart = \Carbon\Carbon::parse($subscription->current_period_start);
        $periodEnd = \Carbon\Carbon::parse($subscription->current_period_end);
        $now = now();

        if ((float) $targetPlan->preco_mensal > (float) $currentPlan->preco_mensal) {
            $totalDays = max(1, $periodStart->diffInDays($periodEnd));
            $remainingDays = max(0, $now->diffInDays($periodEnd, false));
            $priceDiff = (float) $targetPlan->preco_mensal - (float) $currentPlan->preco_mensal;
            $prorata = round(($remainingDays / $totalDays) * $priceDiff, 2);

            DB::table('tenant_subscriptions')
                ->where('id', $subscription->id)
                ->update([
                    'plan_id' => $targetPlan->id,
                    'next_plan_id' => null,
                    'downgrade_effective_at' => null,
                    'status' => 'active',
                    'updated_at' => $now,
                ]);

            $this->logChange($request, $subscription->plan_id, $targetPlan->id, 'upgrade', $prorata, $now);

            return response()->json([
                'message' => 'Upgrade aplicado de imediato.',
                'prorata_amount' => $prorata,
            ]);
        }

        DB::table('tenant_subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'next_plan_id' => $targetPlan->id,
                'downgrade_effective_at' => $subscription->current_period_end,
                'updated_at' => $now,
            ]);

        $this->logChange($request, $subscription->plan_id, $targetPlan->id, 'downgrade_scheduled', 0, \Carbon\Carbon::parse($subscription->current_period_end));

        return response()->json([
            'message' => 'Downgrade agendado para o próximo ciclo.',
            'effective_at' => $subscription->current_period_end,
        ]);
    }

    public function cancel(Request $request)
    {
        $subscription = DB::table('tenant_subscriptions')
            ->where('tenant_id', $this->tenantId())
            ->latest('id')
            ->first();
        abort_if(! $subscription, 404, 'Subscrição não encontrada.');

        DB::table('tenant_subscriptions')
            ->where('id', $subscription->id)
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'updated_at' => now(),
            ]);

        $this->logChange($request, $subscription->plan_id, null, 'cancel', 0, now());

        return response()->json(['message' => 'Subscrição cancelada.']);
    }

    public function changeLogs()
    {
        $logs = DB::table('tenant_plan_change_logs')
            ->leftJoin('users', 'users.id', '=', 'tenant_plan_change_logs.user_id')
            ->leftJoin('plans as from_plan', 'from_plan.id', '=', 'tenant_plan_change_logs.from_plan_id')
            ->leftJoin('plans as to_plan', 'to_plan.id', '=', 'tenant_plan_change_logs.to_plan_id')
            ->where('tenant_plan_change_logs.tenant_id', $this->tenantId())
            ->select(
                'tenant_plan_change_logs.*',
                'users.name as user_name',
                'from_plan.nome as from_plan_nome',
                'to_plan.nome as to_plan_nome'
            )
            ->latest('tenant_plan_change_logs.id')
            ->paginate(20);

        return response()->json($logs);
    }

    private function logChange(Request $request, ?int $fromPlanId, ?int $toPlanId, string $type, float $prorata, \Carbon\CarbonInterface $effectiveAt): void
    {
        DB::table('tenant_plan_change_logs')->insert([
            'tenant_id' => $this->tenantId(),
            'user_id' => $request->user()->id,
            'from_plan_id' => $fromPlanId,
            'to_plan_id' => $toPlanId,
            'change_type' => $type,
            'prorata_amount' => $prorata,
            'effective_at' => $effectiveAt,
            'meta' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

