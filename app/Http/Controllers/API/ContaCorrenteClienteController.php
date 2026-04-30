<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContaCorrenteClienteController extends Controller
{
    public function index()
    {
        return response()->json(
            DB::table('conta_corrente_clientes')
                ->join('entidades', 'entidades.id', '=', 'conta_corrente_clientes.cliente_id')
                ->select('conta_corrente_clientes.*', 'entidades.nome as cliente_nome')
                ->latest('conta_corrente_clientes.id')
                ->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:entidades,id'],
            'data' => ['required', 'date'],
            'descricao' => ['required', 'string', 'max:255'],
            'debito' => ['nullable', 'numeric', 'min:0'],
            'credito' => ['nullable', 'numeric', 'min:0'],
        ]);

        $id = DB::table('conta_corrente_clientes')->insertGetId([
            ...$data,
            'debito' => $data['debito'] ?? 0,
            'credito' => $data['credito'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(DB::table('conta_corrente_clientes')->where('id', $id)->first(), 201);
    }
}
