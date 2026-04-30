<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Entidade;
use App\Models\Pais;
use App\Services\ViesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntidadeController extends Controller
{
    private function defaultPaisId(): ?int
    {
        return Pais::query()->where('sigla', 'PT')->value('id');
    }

    protected $viesService;

    public function __construct(ViesService $viesService)
    {
        $this->viesService = $viesService;
    }

    // Gerar número incremental
    private function gerarNumero()
    {
        $ultimo = Entidade::withTrashed()->orderBy('id', 'desc')->first();
        $numero = $ultimo ? intval($ultimo->numero) + 1 : 1;
        return str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    // Validar NIF único
    private function validarNifUnico($nif, $ignoreId = null)
    {
        $query = Entidade::where('nif', $nif);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }
        return !$query->exists();
    }

    // Listar todos (com filtro por tipo)
    public function index(Request $request)
    {
        $query = Entidade::with('pais');

        if ($request->tipo && in_array($request->tipo, ['cliente', 'fornecedor'])) {
            if ($request->tipo === 'cliente') {
                $query->clientes();
            } else {
                $query->fornecedores();
            }
        }

        $entidades = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json($entidades);
    }

    // Consultar NIF no VIES
    public function consultarNif(Request $request)
    {
        $request->validate([
            'nif' => 'required|string',
            'pais_sigla' => 'required|string|size:2'
        ]);

        // Verificar se NIF já existe na base
        $existe = Entidade::where('nif', $request->nif)->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Este NIF já está registado no sistema'
            ], 422);
        }

        // Consultar VIES
        $result = $this->viesService->consultarVat($request->pais_sigla, $request->nif);

        return response()->json($result);
    }

    // Buscar dados VIES e preencher automaticamente
    public function buscarDadosVies(Request $request)
    {
        $request->validate([
            'nif' => 'required|string',
            'pais_sigla' => 'required|string|size:2'
        ]);

        $result = $this->viesService->consultarVat($request->pais_sigla, $request->nif);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'nome' => $result['nome'],
                'morada' => $result['morada']
            ]);
        }

        return response()->json($result, 422);
    }

    // Store nova entidade
    public function store(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:cliente,fornecedor,ambos',
            'nif' => 'required|string|size:9|unique:entidades,nif',
            'nome' => 'required|string|max:255',
            'morada' => 'nullable|string',
            'codigo_postal' => 'nullable|regex:/^\d{4}-\d{3}$/',
            'localidade' => 'nullable|string|max:255',
            'pais_id' => 'required|exists:paises,id',
            'telefone' => 'nullable|string|max:20',
            'telemovel' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'email' => 'nullable|email',
            'consentimento_rgpd' => 'boolean',
            'observacoes' => 'nullable|string',
            'estado' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $entidade = Entidade::create([
                'tipo' => $request->tipo,
                'numero' => $this->gerarNumero(),
                'nif' => $request->nif,
                'nome' => $request->nome,
                'morada' => $request->morada,
                'codigo_postal' => $request->codigo_postal,
                'localidade' => $request->localidade,
                'pais_id' => $request->pais_id ?? $this->defaultPaisId(),
                'telefone' => $request->telefone,
                'telemovel' => $request->telemovel,
                'website' => $request->website,
                'email' => $request->email,
                'consentimento_rgpd' => $request->consentimento_rgpd ?? false,
                'observacoes' => $request->observacoes,
                'estado' => $request->estado ?? true
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Entidade criada com sucesso',
                'data' => $entidade->load('pais')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar entidade: '.$e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Show entidade
    public function show($id)
    {
        $entidade = Entidade::with(['pais', 'contactos'])->findOrFail($id);
        return response()->json($entidade);
    }

    // Update entidade
    public function update(Request $request, $id)
    {
        $entidade = Entidade::findOrFail($id);

        $request->validate([
            'tipo' => 'sometimes|in:cliente,fornecedor,ambos',
            'nif' => "sometimes|string|size:9|unique:entidades,nif,{$id}",
            'nome' => 'sometimes|string|max:255',
            'morada' => 'nullable|string',
            'codigo_postal' => 'nullable|regex:/^\d{4}-\d{3}$/',
            'localidade' => 'nullable|string|max:255',
            'pais_id' => 'sometimes|exists:paises,id',
            'telefone' => 'nullable|string|max:20',
            'telemovel' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'email' => 'nullable|email',
            'consentimento_rgpd' => 'boolean',
            'observacoes' => 'nullable|string',
            'estado' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $entidade->update($request->all());

            DB::commit();

            return response()->json([
                'message' => 'Entidade atualizada com sucesso',
                'data' => $entidade->load('pais')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar entidade: '.$e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete (soft delete)
    public function destroy($id)
    {
        $entidade = Entidade::findOrFail($id);
        $entidade->delete();

        return response()->json([
            'message' => 'Entidade desativada com sucesso'
        ]);
    }

    // Restore
    public function restore($id)
    {
        $entidade = Entidade::withTrashed()->findOrFail($id);
        $entidade->restore();

        return response()->json([
            'message' => 'Entidade restaurada com sucesso',
            'data' => $entidade
        ]);
    }
}
