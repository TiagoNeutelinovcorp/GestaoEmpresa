<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArtigoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max(1, min((int) $request->input('per_page', 15), 200));

        $query = DB::table('artigos')
            ->leftJoin('ivas', 'ivas.id', '=', 'artigos.iva_id')
            ->select('artigos.*', 'ivas.nome as iva_nome', 'ivas.percentagem as iva_percentagem')
            ->whereNull('artigos.deleted_at');

        if ($request->filled('search')) {
            $search = '%'.$request->string('search')->toString().'%';
            $query->where(function ($q) use ($search) {
                $q->where('artigos.referencia', 'like', $search)
                    ->orWhere('artigos.nome', 'like', $search);
            });
        }

        return response()->json($query->orderBy('artigos.nome')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'referencia' => ['required', 'string', 'max:100', 'unique:artigos,referencia'],
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'preco' => ['required', 'numeric', 'min:0'],
            'iva_id' => ['nullable', 'exists:ivas,id'],
            'foto_path' => ['nullable', 'string', 'max:500'],
            'observacoes' => ['nullable', 'string'],
            'estado' => ['boolean'],
        ]);

        $id = DB::table('artigos')->insertGetId([
            ...$data,
            'estado' => $data['estado'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(DB::table('artigos')->where('id', $id)->first(), 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'referencia' => ['sometimes', 'string', 'max:100', 'unique:artigos,referencia,'.$id],
            'nome' => ['sometimes', 'string', 'max:255'],
            'descricao' => ['nullable', 'string'],
            'preco' => ['sometimes', 'numeric', 'min:0'],
            'iva_id' => ['nullable', 'exists:ivas,id'],
            'foto_path' => ['nullable', 'string', 'max:500'],
            'observacoes' => ['nullable', 'string'],
            'estado' => ['boolean'],
        ]);

        DB::table('artigos')->where('id', $id)->update([...$data, 'updated_at' => now()]);

        return response()->json(DB::table('artigos')->where('id', $id)->first());
    }

    public function destroy(int $id)
    {
        DB::table('artigos')->where('id', $id)->update(['deleted_at' => now(), 'updated_at' => now()]);

        return response()->json(['message' => 'Artigo desativado com sucesso.']);
    }
}
