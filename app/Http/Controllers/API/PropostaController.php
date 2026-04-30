<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropostaController extends Controller
{
    public function index()
    {
        $rows = DB::table('propostas')
            ->join('entidades', 'entidades.id', '=', 'propostas.cliente_id')
            ->select('propostas.*', 'entidades.nome as cliente_nome')
            ->whereNull('propostas.deleted_at')
            ->orderByDesc('propostas.created_at')
            ->paginate(15);

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:entidades,id'],
            'data_proposta' => ['nullable', 'date'],
            'validade' => ['nullable', 'date'],
            'estado' => ['in:rascunho,fechado'],
            'linhas' => ['array'],
            'linhas.*.artigo_id' => ['required', 'exists:artigos,id'],
            'linhas.*.fornecedor_id' => ['nullable', 'exists:entidades,id'],
            'linhas.*.quantidade' => ['nullable', 'numeric', 'min:0.01'],
            'linhas.*.preco_unitario' => ['nullable', 'numeric', 'min:0'],
            'linhas.*.preco_custo' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data) {
            $id = DB::table('propostas')->insertGetId([
                'numero' => $this->nextNumber('PROP', 'propostas'),
                'cliente_id' => $data['cliente_id'],
                'data_proposta' => $data['data_proposta'] ?? now()->toDateString(),
                'validade' => $data['validade'] ?? now()->addDays(30)->toDateString(),
                'estado' => $data['estado'] ?? 'rascunho',
                'valor_total' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $total = $this->storeLines($id, $data['linhas'] ?? []);

            DB::table('propostas')->where('id', $id)->update(['valor_total' => $total, 'updated_at' => now()]);

            return response()->json(DB::table('propostas')->where('id', $id)->first(), 201);
        });
    }

    public function show(int $id)
    {
        $proposta = DB::table('propostas')->where('id', $id)->first();
        $linhas = DB::table('proposta_linhas')
            ->join('artigos', 'artigos.id', '=', 'proposta_linhas.artigo_id')
            ->leftJoin('entidades as fornecedores', 'fornecedores.id', '=', 'proposta_linhas.fornecedor_id')
            ->select('proposta_linhas.*', 'artigos.nome as artigo_nome', 'artigos.referencia', 'fornecedores.nome as fornecedor_nome')
            ->where('proposta_linhas.proposta_id', $id)
            ->get();

        return response()->json(['proposta' => $proposta, 'linhas' => $linhas]);
    }

    public function toOrder(int $id)
    {
        return DB::transaction(function () use ($id) {
            $proposta = DB::table('propostas')->where('id', $id)->first();
            abort_if(! $proposta, 404);

            $orderId = DB::table('encomendas_clientes')->insertGetId([
                'numero' => $this->nextNumber('ENC-C', 'encomendas_clientes'),
                'data_encomenda' => now()->toDateString(),
                'cliente_id' => $proposta->cliente_id,
                'proposta_id' => $proposta->id,
                'estado' => 'rascunho',
                'valor_total' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $total = 0;
            $lines = DB::table('proposta_linhas')->where('proposta_id', $id)->get();
            foreach ($lines as $line) {
                DB::table('encomenda_cliente_linhas')->insert([
                    'encomenda_cliente_id' => $orderId,
                    'artigo_id' => $line->artigo_id,
                    'fornecedor_id' => $line->fornecedor_id,
                    'quantidade' => $line->quantidade,
                    'preco_unitario' => $line->preco_unitario,
                    'subtotal' => $line->subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $total += (float) $line->subtotal;
            }

            DB::table('encomendas_clientes')->where('id', $orderId)->update(['valor_total' => $total, 'updated_at' => now()]);

            return response()->json([
                'message' => 'Proposta convertida em Encomenda Cliente.',
                'encomenda_cliente_id' => $orderId,
            ]);
        });
    }

    public function pdf(int $id)
    {
        $proposta = DB::table('propostas')
            ->leftJoin('entidades as clientes', 'clientes.id', '=', 'propostas.cliente_id')
            ->select('propostas.*', 'clientes.nome as cliente_nome')
            ->where('propostas.id', $id)
            ->first();
        abort_if(! $proposta, 404);

        $linhas = DB::table('proposta_linhas')
            ->join('artigos', 'artigos.id', '=', 'proposta_linhas.artigo_id')
            ->leftJoin('entidades as fornecedores', 'fornecedores.id', '=', 'proposta_linhas.fornecedor_id')
            ->select('proposta_linhas.*', 'artigos.nome as artigo_nome', 'fornecedores.nome as fornecedor_nome')
            ->where('proposta_linhas.proposta_id', $id)
            ->get();

        $pdf = Pdf::loadHTML(view('pdf.proposta', compact('proposta', 'linhas'))->render());

        return $pdf->download("proposta-{$proposta->numero}.pdf");
    }

    private function storeLines(int $propostaId, array $linhas): float
    {
        $total = 0;
        foreach ($linhas as $linha) {
            $qtd = (float) ($linha['quantidade'] ?? 1);
            $preco = (float) ($linha['preco_unitario'] ?? 0);
            $subtotal = $qtd * $preco;
            DB::table('proposta_linhas')->insert([
                'proposta_id' => $propostaId,
                'artigo_id' => $linha['artigo_id'],
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'quantidade' => $qtd,
                'preco_unitario' => $preco,
                'preco_custo' => (float) ($linha['preco_custo'] ?? 0),
                'subtotal' => $subtotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $total += $subtotal;
        }

        return $total;
    }

    private function nextNumber(string $prefix, string $table): string
    {
        $count = DB::table($table)->count() + 1;

        return sprintf('%s-%06d', $prefix, $count);
    }
}
