<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = 'paises';

    protected $fillable = ['nome', 'sigla', 'codigo_iso3'];

    public function entidades()
    {
        return $this->hasMany(Entidade::class);
    }
}
