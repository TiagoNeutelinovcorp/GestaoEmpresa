<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contacto;
use Illuminate\Http\Request;

class ContactoController extends Controller
{
    public function index(Request $request)
    {
        $query = Contacto::query()->with(['entidade', 'funcao']);

        if ($request->filled('entidade_id')) {
            $query->where('entidade_id', $request->integer('entidade_id'));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero' => ['nullable', 'string', 'max:20', 'unique:contactos,numero'],
            'entidade_id' => ['required', 'exists:entidades,id'],
            'nome' => ['required', 'string', 'max:255'],
            'apelido' => ['nullable', 'string', 'max:255'],
            'funcao_id' => ['nullable', 'exists:contactos_funcoes,id'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'consentimento_rgpd' => ['boolean'],
            'observacoes' => ['nullable', 'string'],
            'estado' => ['boolean'],
        ]);

        if (empty($data['numero'])) {
            $lastId = Contacto::withTrashed()->max('id') ?? 0;
            $data['numero'] = str_pad((string) ($lastId + 1), 6, '0', STR_PAD_LEFT);
        }

        $contacto = Contacto::create($data);

        return response()->json($contacto->load(['entidade', 'funcao']), 201);
    }

    public function show(string $id)
    {
        return response()->json(Contacto::with(['entidade', 'funcao'])->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $contacto = Contacto::query()->findOrFail($id);

        $data = $request->validate([
            'entidade_id' => ['sometimes', 'exists:entidades,id'],
            'nome' => ['sometimes', 'string', 'max:255'],
            'apelido' => ['nullable', 'string', 'max:255'],
            'funcao_id' => ['nullable', 'exists:contactos_funcoes,id'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'telemovel' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'consentimento_rgpd' => ['boolean'],
            'observacoes' => ['nullable', 'string'],
            'estado' => ['boolean'],
        ]);

        $contacto->update($data);

        return response()->json($contacto->load(['entidade', 'funcao']));
    }

    public function destroy(string $id)
    {
        Contacto::query()->findOrFail($id)->delete();

        return response()->json(['message' => 'Contacto removido com sucesso.']);
    }
}
