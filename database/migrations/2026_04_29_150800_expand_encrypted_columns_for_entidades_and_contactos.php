<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE entidades MODIFY localidade TEXT NULL');
        DB::statement('ALTER TABLE entidades MODIFY telefone TEXT NULL');
        DB::statement('ALTER TABLE entidades MODIFY telemovel TEXT NULL');
        DB::statement('ALTER TABLE entidades MODIFY website TEXT NULL');
        DB::statement('ALTER TABLE entidades MODIFY email TEXT NULL');
        DB::statement('ALTER TABLE entidades MODIFY observacoes TEXT NULL');

        DB::statement('ALTER TABLE contactos MODIFY nome TEXT NOT NULL');
        DB::statement('ALTER TABLE contactos MODIFY apelido TEXT NULL');
        DB::statement('ALTER TABLE contactos MODIFY telefone TEXT NULL');
        DB::statement('ALTER TABLE contactos MODIFY telemovel TEXT NULL');
        DB::statement('ALTER TABLE contactos MODIFY email TEXT NULL');
        DB::statement('ALTER TABLE contactos MODIFY observacoes TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE entidades MODIFY localidade VARCHAR(255) NULL');
        DB::statement('ALTER TABLE entidades MODIFY telefone VARCHAR(20) NULL');
        DB::statement('ALTER TABLE entidades MODIFY telemovel VARCHAR(20) NULL');
        DB::statement('ALTER TABLE entidades MODIFY website VARCHAR(255) NULL');
        DB::statement('ALTER TABLE entidades MODIFY email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE entidades MODIFY observacoes TEXT NULL');

        DB::statement('ALTER TABLE contactos MODIFY nome VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE contactos MODIFY apelido VARCHAR(255) NULL');
        DB::statement('ALTER TABLE contactos MODIFY telefone VARCHAR(20) NULL');
        DB::statement('ALTER TABLE contactos MODIFY telemovel VARCHAR(20) NULL');
        DB::statement('ALTER TABLE contactos MODIFY email VARCHAR(255) NULL');
        DB::statement('ALTER TABLE contactos MODIFY observacoes TEXT NULL');
    }
};
