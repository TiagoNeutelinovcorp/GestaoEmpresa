<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FaturaFornecedorController extends Controller
{
    private function tenantId(): int
    {
        return (int) app('tenant.id');
    }

    public function index()
    {
        return response()->json(
            DB::table('faturas_fornecedores')
                ->join('entidades', 'entidades.id', '=', 'faturas_fornecedores.fornecedor_id')
                ->leftJoin('encomendas_fornecedores', 'encomendas_fornecedores.id', '=', 'faturas_fornecedores.encomenda_fornecedor_id')
                ->select('faturas_fornecedores.*', 'entidades.nome as fornecedor_nome', 'encomendas_fornecedores.numero as encomenda_numero')
                ->where('faturas_fornecedores.tenant_id', $this->tenantId())
                ->whereNull('faturas_fornecedores.deleted_at')
                ->latest('faturas_fornecedores.id')
                ->paginate(15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero' => ['required', 'string', 'max:100', Rule::unique('faturas_fornecedores', 'numero')->where('tenant_id', $this->tenantId())],
            'data_fatura' => ['required', 'date'],
            'data_vencimento' => ['nullable', 'date'],
            'fornecedor_id' => ['required', 'exists:entidades,id'],
            'encomenda_fornecedor_id' => ['nullable', 'exists:encomendas_fornecedores,id'],
            'valor_total' => ['required', 'numeric', 'min:0'],
            'documento_path' => ['nullable', 'string', 'max:500'],
            'comprovativo_path' => ['nullable', 'string', 'max:500'],
            'estado' => ['in:pendente_pagamento,paga'],
            'enviar_comprovativo' => ['boolean'],
        ]);

        $id = DB::table('faturas_fornecedores')->insertGetId([
            ...collect($data)->except(['enviar_comprovativo'])->toArray(),
            'tenant_id' => $this->tenantId(),
            'estado' => $data['estado'] ?? 'pendente_pagamento',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invoice = DB::table('faturas_fornecedores')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        $this->sendProofIfNeeded($invoice, (bool) ($data['enviar_comprovativo'] ?? false));

        return response()->json($invoice, 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'data_fatura' => ['sometimes', 'date'],
            'data_vencimento' => ['nullable', 'date'],
            'fornecedor_id' => ['sometimes', 'exists:entidades,id'],
            'encomenda_fornecedor_id' => ['nullable', 'exists:encomendas_fornecedores,id'],
            'valor_total' => ['sometimes', 'numeric', 'min:0'],
            'documento_path' => ['nullable', 'string', 'max:500'],
            'comprovativo_path' => ['nullable', 'string', 'max:500'],
            'estado' => ['in:pendente_pagamento,paga'],
            'enviar_comprovativo' => ['boolean'],
        ]);

        DB::table('faturas_fornecedores')->where('tenant_id', $this->tenantId())->where('id', $id)->update([
            ...collect($data)->except(['enviar_comprovativo'])->toArray(),
            'updated_at' => now(),
        ]);

        $invoice = DB::table('faturas_fornecedores')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        $this->sendProofIfNeeded($invoice, (bool) ($data['enviar_comprovativo'] ?? false));

        return response()->json($invoice);
    }

    private function sendProofIfNeeded(object $invoice, bool $send): void
    {
        if (! $send || $invoice->estado !== 'paga') {
            return;
        }

        $supplier = DB::table('entidades')
            ->where('tenant_id', $this->tenantId())
            ->where('id', $invoice->fornecedor_id)
            ->first();
        if (! $supplier || ! $supplier->email) {
            return;
        }

        Mail::raw(
            "Estimado(a) Fornecedor,\n\nEnviamos em anexo o comprovativo de pagamento da fatura \"{$invoice->numero}\".\nQualquer questão, entre em contacto connosco.\n\nCumprimentos,",
            function ($message) use ($supplier, $invoice) {
                $message->to($supplier->email)
                    ->subject("Comprovativo de Pagamento - Fatura {$invoice->numero}");

                if ($invoice->comprovativo_path && Storage::disk('local')->exists($invoice->comprovativo_path)) {
                    $message->attach(Storage::disk('local')->path($invoice->comprovativo_path));
                }
            }
        );
    }
}
