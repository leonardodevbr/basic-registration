<?php
namespace App\Http\Controllers;

use App\Services\ApiSigvsaService;
use Illuminate\Http\Request;

class PersonController extends Controller
{
    protected $personService;

    public function __construct(ApiSigvsaService $personService)
    {
        $this->personService = $personService;
    }

    public function buscar(Request $request)
    {
        $filtro = $request->input('filtro');

        if (!$filtro) {
            return response()->json(['error' => 'Parâmetro de busca inválido'], 400);
        }

        $resultado = $this->personService->buscarPessoa($filtro);

        return response()->json($resultado);
    }
}
