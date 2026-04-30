<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ViesService
{
    private $wsdlUrl = 'https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number';

    public function consultarVat($paisSigla, $nif)
    {
        try {
            $response = Http::timeout(10)->get($this->wsdlUrl, [
                'countryCode' => strtoupper($paisSigla),
                'vatNumber' => $nif
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['isValid'] ?? false) {
                    return [
                        'success' => true,
                        'nome' => $data['name'] ?? null,
                        'morada' => $data['address'] ?? null,
                        'dados' => $data
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'NIF não é válido no VIES'
            ];
        } catch (\Exception $e) {
            Log::error('VIES Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao consultar VIES: ' . $e->getMessage()
            ];
        }
    }

    // Para Portugal especificamente
    public function consultarPortugal($nif)
    {
        return $this->consultarVat('PT', $nif);
    }
}
