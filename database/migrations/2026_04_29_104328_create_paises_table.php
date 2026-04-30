<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('sigla', 2)->unique();
            $table->string('codigo_iso3', 3)->nullable();
            $table->timestamps();
        });

        // Inserir dados padrão
        DB::table('paises')->insert([
            ['nome' => 'Portugal', 'sigla' => 'PT', 'codigo_iso3' => 'PRT'],
            ['nome' => 'Espanha', 'sigla' => 'ES', 'codigo_iso3' => 'ESP'],
            ['nome' => 'França', 'sigla' => 'FR', 'codigo_iso3' => 'FRA'],
            ['nome' => 'Alemanha', 'sigla' => 'DE', 'codigo_iso3' => 'DEU'],
            ['nome' => 'Brasil', 'sigla' => 'BR', 'codigo_iso3' => 'BRA'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('paises');
    }
};
