<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContaBancariaController extends Controller
{
    private function tenantId(): int
    {
        return (int) app('tenant.id');
    }

    public function index()
    {
        return response()->json(
            DB::table('contas_bancarias')
                ->where('tenant_id', $this->tenantId())
                ->orderBy('banco')
                ->paginate(20)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'banco' => ['required', 'string', 'max:255'],
            'iban' => ['required', 'string', 'max:64'],
            'swift' => ['nullable', 'string', 'max:32'],
            'estado' => ['boolean'],
        ]);

        $id = DB::table('contas_bancarias')->insertGetId([
            ...$data,
            'tenant_id' => $this->tenantId(),
            'estado' => $data['estado'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(
            DB::table('contas_bancarias')
                ->where('tenant_id', $this->tenantId())
                ->where('id', $id)
                ->first(),
            201
        );
    }
}
