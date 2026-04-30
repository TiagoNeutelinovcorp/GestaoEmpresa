<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ivas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->decimal('percentagem', 5, 2);
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        Schema::create('artigos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia')->unique();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->decimal('preco', 12, 2)->default(0);
            $table->foreignId('iva_id')->nullable()->constrained('ivas')->nullOnDelete();
            $table->string('foto_path')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('propostas', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('data_proposta')->nullable();
            $table->date('validade')->nullable();
            $table->foreignId('cliente_id')->constrained('entidades')->cascadeOnDelete();
            $table->enum('estado', ['rascunho', 'fechado'])->default('rascunho');
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('proposta_linhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposta_id')->constrained('propostas')->cascadeOnDelete();
            $table->foreignId('artigo_id')->constrained('artigos')->restrictOnDelete();
            $table->foreignId('fornecedor_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('preco_unitario', 12, 2)->default(0);
            $table->decimal('preco_custo', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('encomendas_clientes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('data_encomenda')->nullable();
            $table->foreignId('cliente_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('proposta_id')->nullable()->constrained('propostas')->nullOnDelete();
            $table->enum('estado', ['rascunho', 'fechado'])->default('rascunho');
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('encomenda_cliente_linhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomenda_cliente_id')->constrained('encomendas_clientes')->cascadeOnDelete();
            $table->foreignId('artigo_id')->constrained('artigos')->restrictOnDelete();
            $table->foreignId('fornecedor_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('preco_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('encomendas_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('data_encomenda')->nullable();
            $table->foreignId('fornecedor_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('encomenda_cliente_id')->nullable()->constrained('encomendas_clientes')->nullOnDelete();
            $table->enum('estado', ['rascunho', 'fechado'])->default('rascunho');
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('encomenda_fornecedor_linhas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encomenda_fornecedor_id')->constrained('encomendas_fornecedores')->cascadeOnDelete();
            $table->foreignId('artigo_id')->constrained('artigos')->restrictOnDelete();
            $table->decimal('quantidade', 10, 2)->default(1);
            $table->decimal('preco_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('faturas_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->date('data_fatura');
            $table->date('data_vencimento')->nullable();
            $table->foreignId('fornecedor_id')->constrained('entidades')->cascadeOnDelete();
            $table->foreignId('encomenda_fornecedor_id')->nullable()->constrained('encomendas_fornecedores')->nullOnDelete();
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->string('documento_path')->nullable();
            $table->string('comprovativo_path')->nullable();
            $table->enum('estado', ['pendente_pagamento', 'paga'])->default('pendente_pagamento');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('contas_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('banco');
            $table->string('iban');
            $table->string('swift')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        Schema::create('conta_corrente_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('entidades')->cascadeOnDelete();
            $table->date('data');
            $table->string('descricao');
            $table->decimal('debito', 12, 2)->default(0);
            $table->decimal('credito', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('arquivo_digital', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('entidade_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('calendario_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        Schema::create('calendario_acoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });

        Schema::create('calendario_eventos', function (Blueprint $table) {
            $table->id();
            $table->date('data');
            $table->time('hora')->nullable();
            $table->integer('duracao_minutos')->default(60);
            $table->boolean('partilha')->default(false);
            $table->boolean('conhecimento')->default(false);
            $table->foreignId('entidade_id')->nullable()->constrained('entidades')->nullOnDelete();
            $table->foreignId('tipo_id')->nullable()->constrained('calendario_tipos')->nullOnDelete();
            $table->foreignId('acao_id')->nullable()->constrained('calendario_acoes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('descricao')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_eventos');
        Schema::dropIfExists('calendario_acoes');
        Schema::dropIfExists('calendario_tipos');
        Schema::dropIfExists('arquivo_digital');
        Schema::dropIfExists('conta_corrente_clientes');
        Schema::dropIfExists('contas_bancarias');
        Schema::dropIfExists('faturas_fornecedores');
        Schema::dropIfExists('encomenda_fornecedor_linhas');
        Schema::dropIfExists('encomendas_fornecedores');
        Schema::dropIfExists('encomenda_cliente_linhas');
        Schema::dropIfExists('encomendas_clientes');
        Schema::dropIfExists('proposta_linhas');
        Schema::dropIfExists('propostas');
        Schema::dropIfExists('artigos');
        Schema::dropIfExists('ivas');
    }
};
