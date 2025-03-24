<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiSigvsaService
{
    protected $baseUrl;
    protected $email;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = env('SIGVSA_API_BASE_URL');
        $this->email = env('SIGVSA_API_EMAIL');
        $this->password = env('SIGVSA_API_PASSWORD');
    }

    /**
     * Obtém um token de autenticação na API SIGVSA
     */
    private function obterToken()
    {
        if (!$this->baseUrl || !$this->email || !$this->password) {
            Log::error("Configuração SIGVSA inválida. Verifique o .env");
            return null;
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/login", [
            'email' => $this->email,
            'password' => $this->password
        ]);

        if ($response->failed()) {
            Log::error("Erro ao autenticar na SIGVSA", ['response' => $response->body()]);
            return null;
        }

        return $response->json('token'); // Confirme se o retorno da API usa 'token'
    }

    /**
     * Busca uma pessoa na API SIGVSA
     */
    public function buscarPessoa($filtro)
    {
        $token = cache()->remember('sigvsa_api_token', 300, function () {
            return $this->obterToken();
        });

        if (!$token) {
            return response()->json(['error' => 'Autenticação falhou'], 401);
        }

        $termo = trim($filtro);

        if (preg_match('/^\d{3}/', $termo)) {
            $termo = preg_replace('/\D/', '', $termo);
        }

        $queryParam = ['termo' => $termo];

        $response = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/pessoas", $queryParam);

        if ($response->unauthorized()) {
            Log::warning("Token inválido na busca de pessoa. Tentando renovar...");

            cache()->forget('sigvsa_api_token'); // Remove token inválido
            $token = $this->obterToken();
            if (!$token) {
                return response()->json(['error' => 'Falha ao renovar token'], 401);
            }

            // Nova tentativa com o novo token
            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/pessoas", $queryParam);
        }

        if ($response->failed()) {
            return response()->json(['error' => 'Nenhuma pessoa encontrada'], 404);
        }

        return $response->json();
    }
}
