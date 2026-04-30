<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EntidadeController;
use App\Http\Controllers\API\PaisController;
use App\Http\Controllers\API\ContactoController;
use App\Http\Controllers\API\ViesController;
use App\Http\Controllers\API\ActivityLogController;
use App\Http\Controllers\API\CompanySettingController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserAccessController;
use App\Http\Controllers\API\ArtigoController;
use App\Http\Controllers\API\ArquivoDigitalController;
use App\Http\Controllers\API\CalendarioController;
use App\Http\Controllers\API\ContaBancariaController;
use App\Http\Controllers\API\ContaCorrenteClienteController;
use App\Http\Controllers\API\EncomendaClienteController;
use App\Http\Controllers\API\EncomendaFornecedorController;
use App\Http\Controllers\API\FaturaFornecedorController;
use App\Http\Controllers\API\IvaController;
use App\Http\Controllers\API\LookupController;
use App\Http\Controllers\API\PropostaController;
use Spatie\Permission\Models\Permission;

// Rotas públicas (VIES)
Route::post('/vies/consultar', [ViesController::class, 'consultar']);

// Rotas protegidas por autenticação web (sessão)
Route::middleware(['web', 'auth', 'activity.log', 'api.permission'])->prefix('v1')->group(function () {
    Route::get('me', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->values(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values(),
        ]);
    });

    // Países
    Route::apiResource('paises', PaisController::class);

    // Entidades (Clientes/Fornecedores)
    Route::get('entidades', [EntidadeController::class, 'index']);
    Route::post('entidades', [EntidadeController::class, 'store']);
    Route::post('entidades/consultar-nif', [EntidadeController::class, 'consultarNif']);
    Route::post('entidades/buscar-vies', [EntidadeController::class, 'buscarDadosVies']);
    Route::get('entidades/{id}', [EntidadeController::class, 'show']);
    Route::put('entidades/{id}', [EntidadeController::class, 'update']);
    Route::delete('entidades/{id}', [EntidadeController::class, 'destroy']);
    Route::post('entidades/{id}/restore', [EntidadeController::class, 'restore']);

    // Contactos
    Route::apiResource('contactos', ContactoController::class);
    Route::get('lookups/{type}', [LookupController::class, 'list']);
    Route::post('lookups/{type}', [LookupController::class, 'store']);
    Route::delete('lookups/{type}/{id}', [LookupController::class, 'destroy']);

    // Configurações Financeiro/Artigos
    Route::get('ivas', [IvaController::class, 'index']);
    Route::post('ivas', [IvaController::class, 'store']);
    Route::put('ivas/{id}', [IvaController::class, 'update']);
    Route::delete('ivas/{id}', [IvaController::class, 'destroy']);
    Route::get('artigos', [ArtigoController::class, 'index']);
    Route::post('artigos', [ArtigoController::class, 'store']);
    Route::put('artigos/{id}', [ArtigoController::class, 'update']);
    Route::delete('artigos/{id}', [ArtigoController::class, 'destroy']);

    // Propostas / Encomendas
    Route::get('propostas', [PropostaController::class, 'index']);
    Route::post('propostas', [PropostaController::class, 'store']);
    Route::get('propostas/{id}', [PropostaController::class, 'show']);
    Route::post('propostas/{id}/to-order', [PropostaController::class, 'toOrder']);
    Route::get('propostas/{id}/pdf', [PropostaController::class, 'pdf']);
    Route::get('encomendas-clientes', [EncomendaClienteController::class, 'index']);
    Route::post('encomendas-clientes', [EncomendaClienteController::class, 'store']);
    Route::post('encomendas-clientes/{id}/to-suppliers', [EncomendaClienteController::class, 'convertToSupplierOrders']);
    Route::get('encomendas-clientes/{id}/pdf', [EncomendaClienteController::class, 'pdf']);
    Route::get('encomendas-fornecedores', [EncomendaFornecedorController::class, 'index']);
    Route::post('encomendas-fornecedores', [EncomendaFornecedorController::class, 'store']);
    Route::get('encomendas-fornecedores/{id}/pdf', [EncomendaFornecedorController::class, 'pdf']);

    // Financeiro - Faturas Fornecedor
    Route::get('contas-bancarias', [ContaBancariaController::class, 'index']);
    Route::post('contas-bancarias', [ContaBancariaController::class, 'store']);
    Route::get('conta-corrente-clientes', [ContaCorrenteClienteController::class, 'index']);
    Route::post('conta-corrente-clientes', [ContaCorrenteClienteController::class, 'store']);
    Route::get('faturas-fornecedores', [FaturaFornecedorController::class, 'index']);
    Route::post('faturas-fornecedores', [FaturaFornecedorController::class, 'store']);
    Route::put('faturas-fornecedores/{id}', [FaturaFornecedorController::class, 'update']);

    // Arquivo digital privado
    Route::get('arquivo-digital', [ArquivoDigitalController::class, 'index']);
    Route::post('arquivo-digital', [ArquivoDigitalController::class, 'store']);
    Route::get('arquivo-digital/{id}/download', [ArquivoDigitalController::class, 'download']);

    // Calendário
    Route::get('calendario', [CalendarioController::class, 'index']);
    Route::post('calendario', [CalendarioController::class, 'store']);

    // Gestão de acessos
    Route::get('access/users', [UserAccessController::class, 'index']);
    Route::post('access/users', [UserAccessController::class, 'store']);
    Route::put('access/users/{user}', [UserAccessController::class, 'update']);
    Route::get('access/roles', [RoleController::class, 'index']);
    Route::post('access/roles', [RoleController::class, 'store']);
    Route::put('access/roles/{role}', [RoleController::class, 'update']);
    Route::delete('access/roles/{role}', [RoleController::class, 'destroy']);
    Route::get('access/permissions', fn () => Permission::query()->orderBy('name')->pluck('name'));

    // Configurações empresa
    Route::get('settings/company', [CompanySettingController::class, 'show']);
    Route::put('settings/company', [CompanySettingController::class, 'update']);

    // Logs
    Route::get('logs', [ActivityLogController::class, 'index']);
});
