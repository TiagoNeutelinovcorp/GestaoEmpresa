<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contacto extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'numero', 'entidade_id', 'nome', 'apelido', 'funcao_id',
        'telefone', 'telemovel', 'email', 'consentimento_rgpd',
        'observacoes', 'estado'
    ];

    protected $casts = [
        'nome' => 'encrypted',
        'apelido' => 'encrypted',
        'telefone' => 'encrypted',
        'telemovel' => 'encrypted',
        'email' => 'encrypted',
        'observacoes' => 'encrypted',
        'consentimento_rgpd' => 'boolean',
        'estado' => 'boolean',
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class);
    }

    public function funcao()
    {
        return $this->belongsTo(ContactoFuncao::class);
    }
}
