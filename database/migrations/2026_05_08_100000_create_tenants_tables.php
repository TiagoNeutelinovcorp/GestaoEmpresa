<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug')->unique();
            $table->json('settings')->nullable();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('tenant_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('member');
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('preferred_tenant_id')->nullable()->after('estado')->constrained('tenants')->nullOnDelete();
        });

        $now = now();
        $userIds = DB::table('users')->pluck('id');

        foreach ($userIds as $userId) {
            $baseName = "Tenant {$userId}";
            $slug = Str::slug($baseName);
            $counter = 1;
            while (DB::table('tenants')->where('slug', $slug)->exists()) {
                $counter++;
                $slug = Str::slug("{$baseName}-{$counter}");
            }

            $tenantId = DB::table('tenants')->insertGetId([
                'nome' => $baseName,
                'slug' => $slug,
                'settings' => json_encode([]),
                'owner_user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('tenant_user')->insert([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'role' => 'owner',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('users')->where('id', $userId)->update([
                'preferred_tenant_id' => $tenantId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_tenant_id');
        });

        Schema::dropIfExists('tenant_user');
        Schema::dropIfExists('tenants');
    }
};

