<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'nome',
        'slug',
        'preco_mensal',
        'trial_days',
        'limites',
        'features',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'preco_mensal' => 'decimal:2',
            'trial_days' => 'integer',
            'limites' => 'array',
            'features' => 'array',
            'ativo' => 'boolean',
        ];
    }
}

