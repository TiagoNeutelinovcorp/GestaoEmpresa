<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoFuncao extends Model
{
    protected $table = 'contactos_funcoes';
    protected $fillable = ['nome'];

    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }
}
