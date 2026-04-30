<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['cliente', 'fornecedor', 'ambos'])->default('cliente');
            $table->string('numero')->unique();
            $table->string('nif', 20)->unique();
            $table->string('nome');
            $table->text('morada')->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('localidade')->nullable();
            $table->foreignId('pais_id')->nullable()->constrained('paises')->nullOnDelete();
            $table->string('telefone', 20)->nullable();
            $table->string('telemovel', 20)->nullable();
            $table->string('website')->nullable();
            $table->string('email')->nullable();
            $table->boolean('consentimento_rgpd')->default(false);
            $table->text('observacoes')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entidades');
    }
};
