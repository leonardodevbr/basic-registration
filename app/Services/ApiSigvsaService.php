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
        $token = cache()->remember('sigvsa_api_token', 300, fn () => $this->obterToken());

        if (!$token) {
            return response()->json(['error' => 'Autenticação falhou'], 401);
        }

        $termo = trim($filtro);

        if (preg_match('/^\d{3}/', $termo)) {
            $termo = preg_replace('/\D/', '', $termo);
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->get("{$this->baseUrl}/pessoas", ['termo' => $termo]);


        if ($response->unauthorized()) {
            cache()->forget('sigvsa_api_token');
            $token = $this->obterToken();
            if (!$token) return response()->json(['error' => 'Falha ao renovar token'], 401);

            $response = Http::withToken($token)
                ->acceptJson()
                ->get("{$this->baseUrl}/pessoas", ['termo' => $termo]);
        }

        if ($response->failed()) {
            return response()->json(['error' => 'Nenhuma pessoa encontrada'], 404);
        }

        $dados = $response->json();

        // Mapeia os dados e retorna
        $dadosFormatados = collect($dados)->map(fn ($pessoa) => $this->mapearDadosPessoa($pessoa));

        return response()->json($dadosFormatados);
    }

    public function mapearDadosPessoa(array $raw): array
    {
        $formatted = [
            'person' => [
                'cpf'            => $raw['num_cpf_pessoa'] ?? null,
                'name'           => $raw['nom_pessoa'] ?? null,
                'phone'          => ($raw['familia']['num_ddd_contato_1_fam'] ?? '') . ($raw['familia']['num_tel_contato_1_fam'] ?? ''),
                'mother_name'    => $raw['nom_completo_mae_pessoa'] ?? null,
                'father_name'    => $raw['nom_completo_pai_pessoa'] ?? null,
                'birth_date'     => $this->formatarData($raw['dta_nasc_pessoa'] ?? null),
                'gender'         => $this->maparGenero($raw['cod_sexo_pessoa'] ?? null),
                'nis'            => $raw['num_nis_pessoa_atual'] ?? null,
                'rg'             => $raw['num_identidade_pessoa'] ?? null,
                'issuing_agency' => $raw['sig_orgao_emissor_pessoa'] ?? null,
                'marital_status' => null, // não informado pela API
                'race_color'     => $this->mapearRaca($raw['cod_raca_cor_pessoa'] ?? null),
                'nationality'    => $raw['nom_pais_origem_pessoa'] ?? 'Brasileiro',
                'naturalness'    => $raw['nom_ibge_munic_nasc_pessoa'] ?? null,
            ],
            'address' => [
                'cep'          => $raw['familia']['num_cep_logradouro_fam'] ?? null,
                'street'       => $raw['familia']['nom_logradouro_fam'] ?? null,
                'number'       => $raw['familia']['num_logradouro_fam'] ?? null,
                'complement'   => $raw['familia']['des_complemento_fam'] ?? null,
                'neighborhood' => $raw['familia']['nom_localidade_fam'] ?? null,
                'reference'    => $raw['familia']['txt_referencia_local_fam'] ?? null,
                'city'         => $raw['familia']['nom_ibge_munic_nasc_pessoa'] ?? null,
                'state'        => $raw['familia']['sig_uf_munic_nasc_pessoa'] ?? null,
            ]
        ];

        return $formatted;
    }

    private function formatarData(?string $data): ?string
    {
        if (!$data || strlen($data) !== 8) return null;
        return substr($data, 0, 4) . '-' . substr($data, 4, 2) . '-' . substr($data, 6, 2);
    }

    private function maparGenero(?int $codigo): ?string
    {
        return match ($codigo) {
            1 => 'Masculino',
            2 => 'Feminino',
            default => null,
        };
    }

    private function mapearRaca(?int $codigo): ?string
    {
        return match ($codigo) {
            1 => 'Branca',
            2 => 'Preta',
            3 => 'Parda',
            4 => 'Amarela',
            5 => 'Indígena',
            default => null,
        };
    }
}
