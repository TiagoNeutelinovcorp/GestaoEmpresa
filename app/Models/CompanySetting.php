<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'nome',
        'numero_contribuinte',
        'morada',
        'codigo_postal',
        'localidade',
        'logo_path',
    ];
}
