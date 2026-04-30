<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArquivoDigitalController extends Controller
{
    public function index()
    {
        return response()->json(
            DB::table('arquivo_digital')
                ->leftJoin('entidades', 'entidades.id', '=', 'arquivo_digital.entidade_id')
                ->select('arquivo_digital.*', 'entidades.nome as entidade_nome')
                ->latest('arquivo_digital.id')
                ->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'entidade_id' => ['nullable', 'exists:entidades,id'],
            'ficheiro' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('ficheiro');
        $path = $file->store('arquivo-digital', 'local');

        $id = DB::table('arquivo_digital')->insertGetId([
            'nome' => $data['nome'],
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'entidade_id' => $data['entidade_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(DB::table('arquivo_digital')->where('id', $id)->first(), 201);
    }

    public function download(int $id)
    {
        $doc = DB::table('arquivo_digital')->where('id', $id)->first();
        abort_if(! $doc, 404);

        return Storage::disk('local')->download($doc->path, $doc->nome);
    }
}
