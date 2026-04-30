<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarioController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('calendario_eventos')
            ->leftJoin('users', 'users.id', '=', 'calendario_eventos.user_id')
            ->leftJoin('entidades', 'entidades.id', '=', 'calendario_eventos.entidade_id')
            ->leftJoin('calendario_tipos', 'calendario_tipos.id', '=', 'calendario_eventos.tipo_id')
            ->leftJoin('calendario_acoes', 'calendario_acoes.id', '=', 'calendario_eventos.acao_id')
            ->select(
                'calendario_eventos.*',
                'users.name as utilizador_nome',
                'entidades.nome as entidade_nome',
                'calendario_tipos.nome as tipo_nome',
                'calendario_acoes.nome as acao_nome'
            );

        if ($request->filled('user_id')) {
            $query->where('calendario_eventos.user_id', $request->integer('user_id'));
        }
        if ($request->filled('entidade_id')) {
            $query->where('calendario_eventos.entidade_id', $request->integer('entidade_id'));
        }

        return response()->json($query->latest('calendario_eventos.id')->paginate(30));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'data' => ['required', 'date'],
            'hora' => ['nullable', 'date_format:H:i'],
            'duracao_minutos' => ['nullable', 'integer', 'min:1'],
            'partilha' => ['boolean'],
            'conhecimento' => ['boolean'],
            'entidade_id' => ['nullable', 'exists:entidades,id'],
            'tipo_id' => ['nullable', 'exists:calendario_tipos,id'],
            'acao_id' => ['nullable', 'exists:calendario_acoes,id'],
            'user_id' => ['required', 'exists:users,id'],
            'descricao' => ['nullable', 'string'],
            'estado' => ['boolean'],
        ]);

        $id = DB::table('calendario_eventos')->insertGetId([
            ...$data,
            'duracao_minutos' => $data['duracao_minutos'] ?? 60,
            'partilha' => $data['partilha'] ?? false,
            'conhecimento' => $data['conhecimento'] ?? false,
            'estado' => $data['estado'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(DB::table('calendario_eventos')->where('id', $id)->first(), 201);
    }
}
