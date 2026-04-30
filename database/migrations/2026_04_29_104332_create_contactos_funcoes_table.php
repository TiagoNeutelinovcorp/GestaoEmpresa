<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contactos_funcoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->timestamps();
        });

        DB::table('contactos_funcoes')->insert([
            ['nome' => 'Diretor'],
            ['nome' => 'Gerente'],
            ['nome' => 'Vendedor'],
            ['nome' => 'Apoio ao Cliente'],
            ['nome' => 'Financeiro'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contactos_funcoes');
    }
};
