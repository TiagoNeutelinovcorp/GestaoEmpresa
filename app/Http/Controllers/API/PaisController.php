<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pais;
use Illuminate\Http\Request;

class PaisController extends Controller
{
    public function index()
    {
        return response()->json(Pais::query()->orderBy('nome')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'sigla' => ['required', 'string', 'size:2', 'unique:paises,sigla'],
            'codigo_iso3' => ['nullable', 'string', 'size:3'],
        ]);

        $pais = Pais::create([
            ...$data,
            'sigla' => strtoupper($data['sigla']),
            'codigo_iso3' => isset($data['codigo_iso3']) ? strtoupper($data['codigo_iso3']) : null,
        ]);

        return response()->json($pais, 201);
    }

    public function show(string $id)
    {
        return response()->json(Pais::query()->findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $pais = Pais::query()->findOrFail($id);

        $data = $request->validate([
            'nome' => ['sometimes', 'string', 'max:100'],
            'sigla' => ['sometimes', 'string', 'size:2', 'unique:paises,sigla,'.$pais->id],
            'codigo_iso3' => ['nullable', 'string', 'size:3'],
        ]);

        if (isset($data['sigla'])) {
            $data['sigla'] = strtoupper($data['sigla']);
        }

        if (isset($data['codigo_iso3'])) {
            $data['codigo_iso3'] = strtoupper($data['codigo_iso3']);
        }

        $pais->update($data);

        return response()->json($pais);
    }

    public function destroy(string $id)
    {
        Pais::query()->findOrFail($id)->delete();

        return response()->json(['message' => 'País removido com sucesso.']);
    }
}
