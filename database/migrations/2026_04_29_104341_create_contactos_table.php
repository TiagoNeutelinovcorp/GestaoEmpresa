<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('entidade_id')->constrained('entidades')->onDelete('cascade');
            $table->string('nome');
            $table->string('apelido')->nullable();
            $table->foreignId('funcao_id')->nullable()->constrained('contactos_funcoes')->nullOnDelete();
            $table->string('telefone', 20)->nullable();
            $table->string('telemovel', 20)->nullable();
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
        Schema::dropIfExists('contactos');
    }
};
