<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Entidade extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'entidades';

    protected $fillable = [
        'tenant_id', 'tipo', 'numero', 'nif', 'nome', 'morada', 'codigo_postal',
        'localidade', 'pais_id', 'telefone', 'telemovel', 'website',
        'email', 'consentimento_rgpd', 'observacoes', 'estado'
    ];

    protected $casts = [
        'morada' => 'encrypted',
        'localidade' => 'encrypted',
        'telefone' => 'encrypted',
        'telemovel' => 'encrypted',
        'website' => 'encrypted',
        'email' => 'encrypted',
        'observacoes' => 'encrypted',
        'consentimento_rgpd' => 'boolean',
        'estado' => 'boolean',
    ];

    public function pais()
    {
        return $this->belongsTo(Pais::class);
    }

    public function contactos()
    {
        return $this->hasMany(Contacto::class);
    }

    // Scope para filtrar clientes
    public function scopeClientes($query)
    {
        return $query->whereIn('tipo', ['cliente', 'ambos']);
    }

    // Scope para filtrar fornecedores
    public function scopeFornecedores($query)
    {
        return $query->whereIn('tipo', ['fornecedor', 'ambos']);
    }
}
