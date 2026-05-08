<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entidades', function (Blueprint $table) {
            $table->dropUnique('entidades_numero_unique');
            $table->dropUnique('entidades_nif_unique');
            $table->unique(['tenant_id', 'numero']);
            $table->unique(['tenant_id', 'nif']);
        });

        Schema::table('contactos', function (Blueprint $table) {
            $table->dropUnique('contactos_numero_unique');
            $table->unique(['tenant_id', 'numero']);
        });

        Schema::table('artigos', function (Blueprint $table) {
            $table->dropUnique('artigos_referencia_unique');
            $table->unique(['tenant_id', 'referencia']);
        });

        Schema::table('propostas', function (Blueprint $table) {
            $table->dropUnique('propostas_numero_unique');
            $table->unique(['tenant_id', 'numero']);
        });

        Schema::table('encomendas_clientes', function (Blueprint $table) {
            $table->dropUnique('encomendas_clientes_numero_unique');
            $table->unique(['tenant_id', 'numero']);
        });

        Schema::table('encomendas_fornecedores', function (Blueprint $table) {
            $table->dropUnique('encomendas_fornecedores_numero_unique');
            $table->unique(['tenant_id', 'numero']);
        });

        Schema::table('faturas_fornecedores', function (Blueprint $table) {
            $table->dropUnique('faturas_fornecedores_numero_unique');
            $table->unique(['tenant_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::table('faturas_fornecedores', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero']);
            $table->unique('numero');
        });

        Schema::table('encomendas_fornecedores', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero']);
            $table->unique('numero');
        });

        Schema::table('encomendas_clientes', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero']);
            $table->unique('numero');
        });

        Schema::table('propostas', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero']);
            $table->unique('numero');
        });

        Schema::table('artigos', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'referencia']);
            $table->unique('referencia');
        });

        Schema::table('contactos', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero']);
            $table->unique('numero');
        });

        Schema::table('entidades', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'numero']);
            $table->dropUnique(['tenant_id', 'nif']);
            $table->unique('numero');
            $table->unique('nif');
        });
    }
};

