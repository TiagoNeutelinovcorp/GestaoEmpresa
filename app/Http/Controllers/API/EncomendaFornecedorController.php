<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncomendaFornecedorController extends Controller
{
    public function index()
    {
        return response()->json(
            DB::table('encomendas_fornecedores')
                ->join('entidades', 'entidades.id', '=', 'encomendas_fornecedores.fornecedor_id')
                ->select('encomendas_fornecedores.*', 'entidades.nome as fornecedor_nome')
                ->whereNull('encomendas_fornecedores.deleted_at')
                ->latest('encomendas_fornecedores.id')
                ->paginate(15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fornecedor_id' => ['required', 'exists:entidades,id'],
            'data_encomenda' => ['nullable', 'date'],
            'estado' => ['in:rascunho,fechado'],
            'valor_total' => ['nullable', 'numeric', 'min:0'],
        ]);

        $id = DB::table('encomendas_fornecedores')->insertGetId([
            'numero' => sprintf('ENC-F-%06d', DB::table('encomendas_fornecedores')->count() + 1),
            'fornecedor_id' => $data['fornecedor_id'],
            'data_encomenda' => $data['data_encomenda'] ?? now()->toDateString(),
            'estado' => $data['estado'] ?? 'rascunho',
            'valor_total' => $data['valor_total'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(DB::table('encomendas_fornecedores')->where('id', $id)->first(), 201);
    }

    public function pdf(int $id)
    {
        $encomenda = DB::table('encomendas_fornecedores')
            ->leftJoin('entidades as fornecedores', 'fornecedores.id', '=', 'encomendas_fornecedores.fornecedor_id')
            ->select('encomendas_fornecedores.*', 'fornecedores.nome as fornecedor_nome')
            ->where('encomendas_fornecedores.id', $id)
            ->first();
        abort_if(! $encomenda, 404);

        $linhas = DB::table('encomenda_fornecedor_linhas')
            ->join('artigos', 'artigos.id', '=', 'encomenda_fornecedor_linhas.artigo_id')
            ->select('encomenda_fornecedor_linhas.*', 'artigos.nome as artigo_nome')
            ->where('encomenda_fornecedor_linhas.encomenda_fornecedor_id', $id)
            ->get();

        $pdf = Pdf::loadHTML(view('pdf.encomenda_fornecedor', compact('encomenda', 'linhas'))->render());

        return $pdf->download("encomenda-fornecedor-{$encomenda->numero}.pdf");
    }
}
