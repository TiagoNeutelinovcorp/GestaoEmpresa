<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LookupController extends Controller
{
    private function tenantId(): int
    {
        return (int) app('tenant.id');
    }

    public function list(string $type)
    {
        return response()->json(
            DB::table($this->table($type))
                ->where('tenant_id', $this->tenantId())
                ->orderBy('nome')
                ->get()
        );
    }

    public function store(Request $request, string $type)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'estado' => ['boolean'],
        ]);

        $id = DB::table($this->table($type))->insertGetId([
            'nome' => $data['nome'],
            'tenant_id' => $this->tenantId(),
            'estado' => $data['estado'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(
            DB::table($this->table($type))
                ->where('tenant_id', $this->tenantId())
                ->find($id),
            201
        );
    }

    public function destroy(string $type, int $id)
    {
        DB::table($this->table($type))
            ->where('tenant_id', $this->tenantId())
            ->where('id', $id)
            ->delete();

        return response()->json(['message' => 'Registo removido com sucesso.']);
    }

    private function table(string $type): string
    {
        return match ($type) {
            'funcoes-contacto' => 'contactos_funcoes',
            'calendario-tipos' => 'calendario_tipos',
            'calendario-acoes' => 'calendario_acoes',
            default => abort(404),
        };
    }
}
