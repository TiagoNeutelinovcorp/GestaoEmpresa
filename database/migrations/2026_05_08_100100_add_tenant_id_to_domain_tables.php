<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'entidades',
        'contactos',
        'company_settings',
        'contactos_funcoes',
        'ivas',
        'artigos',
        'propostas',
        'proposta_linhas',
        'encomendas_clientes',
        'encomenda_cliente_linhas',
        'encomendas_fornecedores',
        'encomenda_fornecedor_linhas',
        'faturas_fornecedores',
        'contas_bancarias',
        'conta_corrente_clientes',
        'arquivo_digital',
        'calendario_tipos',
        'calendario_acoes',
        'calendario_eventos',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
                $table->index('tenant_id');
            });
        }

        $defaultTenantId = DB::table('tenants')->orderBy('id')->value('id');
        if (! $defaultTenantId) {
            return;
        }

        foreach ($this->tables as $tableName) {
            DB::table($tableName)->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};

