<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

class CompanySettingController extends Controller
{
    public function show()
    {
        return response()->json(CompanySetting::query()->first());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'nome' => ['nullable', 'string', 'max:255'],
            'numero_contribuinte' => ['nullable', 'string', 'max:20'],
            'morada' => ['nullable', 'string'],
            'codigo_postal' => ['nullable', 'regex:/^\d{4}-\d{3}$/'],
            'localidade' => ['nullable', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = CompanySetting::query()->firstOrCreate([]);
        $settings->update($data);

        return response()->json($settings);
    }
}
