<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncomendaClienteController extends Controller
{
    public function index()
    {
        return response()->json(
            DB::table('encomendas_clientes')
                ->join('entidades', 'entidades.id', '=', 'encomendas_clientes.cliente_id')
                ->leftJoin('propostas', 'propostas.id', '=', 'encomendas_clientes.proposta_id')
                ->select('encomendas_clientes.*', 'entidades.nome as cliente_nome', 'propostas.validade')
                ->whereNull('encomendas_clientes.deleted_at')
                ->latest('encomendas_clientes.id')
                ->paginate(15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:entidades,id'],
            'data_encomenda' => ['nullable', 'date'],
            'estado' => ['in:rascunho,fechado'],
            'valor_total' => ['nullable', 'numeric', 'min:0'],
            'linhas' => ['array'],
            'linhas.*.artigo_id' => ['required', 'exists:artigos,id'],
            'linhas.*.fornecedor_id' => ['nullable', 'exists:entidades,id'],
            'linhas.*.quantidade' => ['nullable', 'numeric', 'min:0.01'],
            'linhas.*.preco_unitario' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data) {
            $id = DB::table('encomendas_clientes')->insertGetId([
                'numero' => sprintf('ENC-C-%06d', DB::table('encomendas_clientes')->count() + 1),
                'cliente_id' => $data['cliente_id'],
                'data_encomenda' => $data['data_encomenda'] ?? now()->toDateString(),
                'estado' => $data['estado'] ?? 'rascunho',
                'valor_total' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $total = (float) ($data['valor_total'] ?? 0);
            foreach (($data['linhas'] ?? []) as $line) {
                $qtd = (float) ($line['quantidade'] ?? 1);
                $preco = (float) ($line['preco_unitario'] ?? 0);
                $subtotal = $qtd * $preco;
                $total += $subtotal;
                DB::table('encomenda_cliente_linhas')->insert([
                    'encomenda_cliente_id' => $id,
                    'artigo_id' => $line['artigo_id'],
                    'fornecedor_id' => $line['fornecedor_id'] ?? null,
                    'quantidade' => $qtd,
                    'preco_unitario' => $preco,
                    'subtotal' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('encomendas_clientes')->where('id', $id)->update(['valor_total' => $total, 'updated_at' => now()]);

            return response()->json(DB::table('encomendas_clientes')->where('id', $id)->first(), 201);
        });
    }

    public function convertToSupplierOrders(int $id)
    {
        return DB::transaction(function () use ($id) {
            $encomenda = DB::table('encomendas_clientes')->where('id', $id)->first();
            abort_if(! $encomenda, 404, 'Encomenda não encontrada.');
            abort_if($encomenda->estado !== 'fechado', 422, 'Só pode converter encomendas no estado fechado.');

            $lines = DB::table('encomenda_cliente_linhas')->where('encomenda_cliente_id', $id)->get()->groupBy('fornecedor_id');

            $created = [];
            foreach ($lines as $fornecedorId => $items) {
                if (! $fornecedorId) {
                    continue;
                }

                $supplierOrderId = DB::table('encomendas_fornecedores')->insertGetId([
                    'numero' => sprintf('ENC-F-%06d', DB::table('encomendas_fornecedores')->count() + 1),
                    'data_encomenda' => now()->toDateString(),
                    'fornecedor_id' => $fornecedorId,
                    'encomenda_cliente_id' => $id,
                    'estado' => 'rascunho',
                    'valor_total' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $total = 0;
                foreach ($items as $item) {
                    DB::table('encomenda_fornecedor_linhas')->insert([
                        'encomenda_fornecedor_id' => $supplierOrderId,
                        'artigo_id' => $item->artigo_id,
                        'quantidade' => $item->quantidade,
                        'preco_unitario' => $item->preco_unitario,
                        'subtotal' => $item->subtotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $total += (float) $item->subtotal;
                }

                DB::table('encomendas_fornecedores')->where('id', $supplierOrderId)->update(['valor_total' => $total, 'updated_at' => now()]);
                $created[] = $supplierOrderId;
            }

            return response()->json([
                'message' => 'Encomendas de fornecedor geradas com sucesso.',
                'encomendas_fornecedor_ids' => $created,
            ]);
        });
    }

    public function pdf(int $id)
    {
        $encomenda = DB::table('encomendas_clientes')
            ->leftJoin('entidades as clientes', 'clientes.id', '=', 'encomendas_clientes.cliente_id')
            ->select('encomendas_clientes.*', 'clientes.nome as cliente_nome')
            ->where('encomendas_clientes.id', $id)
            ->first();
        abort_if(! $encomenda, 404);

        $linhas = DB::table('encomenda_cliente_linhas')
            ->join('artigos', 'artigos.id', '=', 'encomenda_cliente_linhas.artigo_id')
            ->leftJoin('entidades as fornecedores', 'fornecedores.id', '=', 'encomenda_cliente_linhas.fornecedor_id')
            ->select('encomenda_cliente_linhas.*', 'artigos.nome as artigo_nome', 'fornecedores.nome as fornecedor_nome')
            ->where('encomenda_cliente_linhas.encomenda_cliente_id', $id)
            ->get();

        $pdf = Pdf::loadHTML(view('pdf.encomenda_cliente', compact('encomenda', 'linhas'))->render());

        return $pdf->download("encomenda-{$encomenda->numero}.pdf");
    }
}
