<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'nome',
        'numero_contribuinte',
        'morada',
        'codigo_postal',
        'localidade',
        'logo_path',
    ];
}
