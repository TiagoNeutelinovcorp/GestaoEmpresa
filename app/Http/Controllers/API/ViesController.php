<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ViesService;
use Illuminate\Http\Request;

class ViesController extends Controller
{
    protected $viesService;

    public function __construct(ViesService $viesService)
    {
        $this->viesService = $viesService;
    }

    public function consultar(Request $request)
    {
        $request->validate([
            'pais' => 'required|string|size:2',
            'nif' => 'required|string'
        ]);

        $result = $this->viesService->consultarVat($request->pais, $request->nif);

        return response()->json($result);
    }
}
