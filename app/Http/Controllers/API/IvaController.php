<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IvaController extends Controller
{
    private function tenantId(): int
    {
        return (int) app('tenant.id');
    }

    public function index()
    {
        return response()->json(DB::table('ivas')->where('tenant_id', $this->tenantId())->orderBy('percentagem')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'percentagem' => ['required', 'numeric', 'min:0', 'max:100'],
            'estado' => ['boolean'],
        ]);

        $id = DB::table('ivas')->insertGetId([
            ...$data,
            'tenant_id' => $this->tenantId(),
            'estado' => $data['estado'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(DB::table('ivas')->where('tenant_id', $this->tenantId())->find($id), 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'nome' => ['sometimes', 'string', 'max:255'],
            'percentagem' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'estado' => ['boolean'],
        ]);

        DB::table('ivas')->where('tenant_id', $this->tenantId())->where('id', $id)->update([...$data, 'updated_at' => now()]);

        return response()->json(DB::table('ivas')->where('tenant_id', $this->tenantId())->find($id));
    }

    public function destroy(int $id)
    {
        DB::table('ivas')->where('tenant_id', $this->tenantId())->where('id', $id)->delete();

        return response()->json(['message' => 'IVA removido com sucesso.']);
    }
}
