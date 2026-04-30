<?php

namespace Database\Seeders;

use League\ISO3166\ISO3166;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PaisSeeder extends Seeder
{
    public function run(): void
    {
        $paises = (new ISO3166())->all();

        $now = Carbon::now();

        foreach ($paises as $pais) {
            $nomePt = class_exists(\Locale::class)
                ? \Locale::getDisplayRegion('-'.$pais['alpha2'], 'pt_PT')
                : null;
            $nome = $nomePt && $nomePt !== $pais['alpha2'] ? $nomePt : $pais['name'];

            DB::table('paises')->updateOrInsert(
                ['sigla' => $pais['alpha2']],
                [
                'nome' => mb_convert_case($nome, MB_CASE_TITLE, 'UTF-8'),
                'codigo_iso3' => $pais['alpha3'],
                'updated_at' => $now,
                'created_at' => $now,
                ]
            );
        }
    }
}
