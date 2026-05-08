<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->decimal('preco_mensal', 10, 2)->default(0);
            $table->integer('trial_days')->default(0);
            $table->json('limites')->nullable();
            $table->json('features')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('tenant_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->foreignId('next_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->enum('status', ['trialing', 'active', 'cancelled'])->default('trialing');
            $table->timestamp('started_at');
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('downgrade_effective_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('tenant_plan_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignId('to_plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->enum('change_type', ['upgrade', 'downgrade_scheduled', 'cancel']);
            $table->decimal('prorata_amount', 10, 2)->default(0);
            $table->timestamp('effective_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_onboarding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->boolean('branding_completed')->default(false);
            $table->boolean('users_completed')->default(false);
            $table->boolean('permissions_completed')->default(false);
            $table->json('checklist')->nullable();
            $table->string('last_completed_step')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique('tenant_id');
        });

        $now = now();
        DB::table('plans')->insert([
            [
                'nome' => 'Free',
                'slug' => 'free',
                'preco_mensal' => 0,
                'trial_days' => 14,
                'limites' => json_encode(['max_users' => 3]),
                'features' => json_encode(['premium' => false]),
                'ativo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nome' => 'Pro',
                'slug' => 'pro',
                'preco_mensal' => 49.90,
                'trial_days' => 14,
                'limites' => json_encode(['max_users' => 25]),
                'features' => json_encode(['premium' => true]),
                'ativo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nome' => 'Enterprise',
                'slug' => 'enterprise',
                'preco_mensal' => 149.90,
                'trial_days' => 30,
                'limites' => json_encode(['max_users' => 200]),
                'features' => json_encode(['premium' => true]),
                'ativo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $defaultPlanId = DB::table('plans')->where('slug', 'free')->value('id');
        $tenantIds = DB::table('tenants')->pluck('id');
        foreach ($tenantIds as $tenantId) {
            DB::table('tenant_subscriptions')->insert([
                'tenant_id' => $tenantId,
                'plan_id' => $defaultPlanId,
                'status' => 'trialing',
                'started_at' => $now,
                'current_period_start' => $now,
                'current_period_end' => now()->addMonth(),
                'trial_ends_at' => now()->addDays(14),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('tenant_onboarding')->insert([
                'tenant_id' => $tenantId,
                'checklist' => json_encode([
                    ['key' => 'branding', 'label' => 'Configurar branding da empresa', 'done' => false],
                    ['key' => 'users', 'label' => 'Adicionar utilizadores', 'done' => false],
                    ['key' => 'permissions', 'label' => 'Definir permissões', 'done' => false],
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_onboarding');
        Schema::dropIfExists('tenant_plan_change_logs');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('plans');
    }
};

