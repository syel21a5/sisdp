<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BoeExtractorService
{
    private function shouldBypassCache(array $dados, string $type): bool
    {
        // Evita reaproveitar cache antigo/incompleto para o tipo solicitado.
        if ($type === 'veiculo') {
            return empty($dados['veiculos']) || !is_array($dados['veiculos']);
        }

        if ($type === 'celular') {
            return empty($dados['celulares']) || !is_array($dados['celulares']);
        }

        if ($type === 'apfd' || $type === 'administrativo' || $type === 'intimacao') {
            // Se objetos apreendidos usar o formato velho (separado apenas por " / "), forçar bypass
            if (!empty($dados['objetos_apreendidos']) && strpos($dados['objetos_apreendidos'], "\n") === false && strpos($dados['objetos_apreendidos'], " / ") !== false) {
                return true;
            }
        }

        return false;
    }

    private function enrichObjetosProprietario(array $dados): array
    {
        $pickFirst = function (array $arr): ?string {
            foreach ($arr as $v) {
                if (is_string($v) && trim($v) !== '') {
                    return trim($v);
                }
            }
            return null;
        };

        $proprietarioPadrao =
            $pickFirst($dados['autores'] ?? []) ??
            $pickFirst($dados['condutor'] ?? []) ??
            $pickFirst($dados['vitimas'] ?? []) ??
            $pickFirst($dados['testemunhas'] ?? []) ??
            $pickFirst($dados['outros'] ?? []);

        if (!$proprietarioPadrao) {
            return $dados;
        }

        if (!empty($dados['veiculos']) && is_array($dados['veiculos'])) {
            foreach ($dados['veiculos'] as $i => $v) {
                if (!is_array($v)) {
                    continue;
                }
                $has = !empty($v['proprietario']) || !empty($v['pessoa']);
                if (!$has) {
                    $v['proprietario'] = $proprietarioPadrao;
                    $dados['veiculos'][$i] = $v;
                }
            }
        }

        if (!empty($dados['celulares']) && is_array($dados['celulares'])) {
            foreach ($dados['celulares'] as $i => $c) {
                if (!is_array($c)) {
                    continue;
                }
                $has = !empty($c['proprietario']) || !empty($c['pessoa']);
                if (!$has) {
                    $c['proprietario'] = $proprietarioPadrao;
                    $dados['celulares'][$i] = $c;
                }
            }
        }

        return $dados;
    }

    /**
     * Extrai dados de um BOE (PDF ou Texto) usando o script Python (Regex nativo).
     * Centraliza a lógica para evitar duplicação nos Controllers.
     * NÃO USA IA - É 100% local, rápido e gratuito.
     *
     * @param Request $request Acoplado para pegar 'pdfBOE' ou 'textoBOE'
     * @param string $type O tipo de extração ("veiculo", "celular", "administrativo", "intimacao", "apfd")
     * @return array
     */
    public function extract(Request $request, string $type): array
    {
        try {
            $tmpPath = '';

            // 1. Processa o Upload (PDF ou Texto) e gera um Hash para Cache
            $contentHash = '';
            if ($request->hasFile('pdfBOE') && $request->file('pdfBOE')->isValid()) {
                $pdf = $request->file('pdfBOE');
                $contentHash = md5_file($pdf->getRealPath());
                $tmpPath = sys_get_temp_dir() . "/boe_{$type}_upload_" . uniqid() . '.pdf';
                $pdf->move(sys_get_temp_dir(), basename($tmpPath));
            } else {
                $texto = $request->input('textoBOE', '');
                if (empty(trim($texto))) {
                    return ['success' => false, 'message' => 'Escolha um PDF ou cole o texto do BOE antes de processar.', 'status' => 400];
                }
                $contentHash = md5($texto);
                $tmpPath = sys_get_temp_dir() . "/boe_{$type}_texto_" . uniqid() . '.txt';
                file_put_contents($tmpPath, $texto);
            }

            // 2. VERIFICAÇÃO DE CACHE (Prioridade para o tipo Completo 'apfd')
            $cacheDir = storage_path('app/boe_cache');
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0755, true);
            }

            // SEMPRE verificar se existe um cache do tipo "apfd" (Completo), 
            // pois ele serve para todas as outras páginas.
            $cacheFileHashComplete = $cacheDir . "/hash_{$contentHash}_apfd.json";
            
            // Se o arquivo idêntico já foi processado no modo COMPLETO, usa ele
            if (file_exists($cacheFileHashComplete)) {
                $cachedData = json_decode(file_get_contents($cacheFileHashComplete), true);
                if ($cachedData && !$this->shouldBypassCache($cachedData, $type)) {
                    $cachedData = $this->enrichObjetosProprietario($cachedData);
                    @unlink($tmpPath);
                    return [
                        'success' => true,
                        'dados' => $cachedData,
                        'cached' => true,
                        'cache_type' => 'hash'
                    ];
                }
            }

            // 2.5 CACHE POR NÚMERO DO BOE (Busca rápida sem rodar extração completa)
            // Extrai só o número do BOE do arquivo para procurar no cache existente
            $boeFromFile = $this->quickExtractBoeNumber($tmpPath);
            if ($boeFromFile) {
                $boeLimpo = preg_replace('/[^A-Za-z0-9]/', '', $boeFromFile);
                
                $cacheFileBoePC = $cacheDir . "/boe_pcpe_{$boeLimpo}_apfd.json";
                $cacheFileBoePM = $cacheDir . "/boe_pm_{$boeLimpo}_apfd.json";
                // Retrocompatibilidade
                $cacheFileBoeAntigo = $cacheDir . "/boe_{$boeLimpo}_apfd.json";
                
                $cacheTarget = null;
                if (file_exists($cacheFileBoePC)) $cacheTarget = $cacheFileBoePC;
                elseif (file_exists($cacheFileBoePM)) $cacheTarget = $cacheFileBoePM;
                elseif (file_exists($cacheFileBoeAntigo)) $cacheTarget = $cacheFileBoeAntigo;

                if ($cacheTarget) {
                    $cachedData = json_decode(file_get_contents($cacheTarget), true);
                    if ($cachedData && !$this->shouldBypassCache($cachedData, $type)) {
                        $cachedData = $this->enrichObjetosProprietario($cachedData);
                        @unlink($tmpPath);
                        return [
                            'success' => true,
                            'dados' => $cachedData,
                            'cached' => true,
                            'cache_type' => 'boe'
                        ];
                    }
                }
            }

            // 3. Prepara e executa o comando via API FASTAPI (Python Microservice)
            // Em vez de invocar o processo python na mão, enviamos para o Motor local ou remoto.
            $motorUrl = env('MOTOR_URL', 'http://localhost:8001') . '/extract-boe';
            $response = \Illuminate\Support\Facades\Http::timeout(120)->post($motorUrl, [
                'file_path' => $tmpPath,
                'type' => 'apfd' // Forçamos o type 'apfd' para cache universal
            ]);
            
            // Limpeza
            @unlink($tmpPath);

            // 4. Verifica o output da requisição
            if (!$response->successful()) {
                Log::error("Microserviço Python falhou (apfd): " . $response->body());
                return ['success' => false, 'message' => "Falha ao conectar no Motor Python ou erro interno.", 'status' => 500];
            }

            // 5. Faz o parse do JSON do Python
            $json = $response->json();

            if (is_array($json) && isset($json['success'])) {

                if ($json['success']) {
                    $dados = $this->enrichObjetosProprietario($json['dados'] ?? []);
                    
                    // ✅ SALVAR NO CACHE (Sempre como apfd para ser o mestre)
                    file_put_contents($cacheFileHashComplete, json_encode($dados));
                    
                    if (!empty($dados['boe'])) {
                        $boeLimpo = preg_replace('/[^A-Za-z0-9]/', '', $dados['boe']);
                        $cacheFileBoe = $cacheDir . "/boe_pcpe_{$boeLimpo}_apfd.json";
                        file_put_contents($cacheFileBoe, json_encode($dados));
                    }
                    
                    if (!empty($dados['boe_pm'])) {
                        $boeLimpoPM = preg_replace('/[^A-Za-z0-9]/', '', $dados['boe_pm']);
                        $cacheFileBoePM = $cacheDir . "/boe_pm_{$boeLimpoPM}_apfd.json";
                        file_put_contents($cacheFileBoePM, json_encode($dados));
                    }

                    return [
                        'success' => true, 
                        'dados' => $dados,
                        'cached' => false
                    ];
                } else {
                    // Python não conseguiu extrair, mas retornamos sucesso com dados vazios
                    // para que o frontend possa notificar o usuário sem travar
                    $msg = $json['error'] ?? 'Erro desconhecido no script Python.';
                    Log::warning("Extração nativa parcial (apfd): " . $msg);
                    return [
                        'success' => true, 
                        'dados' => [],
                        'cached' => false,
                        'obs' => "Extração nativa não localizou todos os dados. Use a Inteligência Artificial para melhor resultado."
                    ];
                }
            } else {
                Log::error("Script Python falhou brutalmente (apfd):\n" . $response->body());
                return [
                    'success' => true, 
                    'dados' => [],
                    'cached' => false,
                    'obs' => "Falha estrutural ao executar Python. Por favor, preencha manualmente."
                ];
            }

        } catch (\Exception $e) {
            Log::error("Exceção na extração de BOE ({$type}): " . $e->getMessage());
            return ['success' => false, 'message' => "Erro Interno do Servidor: " . $e->getMessage(), 'status' => 500];
        }
    }

    /**
     * Extrai rapidamente APENAS o número do BOE de um arquivo (PDF ou TXT).
     * Usado para buscar cache por número do BOE antes de rodar extração completa.
     */
    private function quickExtractBoeNumber(string $filePath): ?string
    {
        try {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if ($ext === 'txt') {
                // Para texto, lê direto com PHP (instantâneo)
                $content = file_get_contents($filePath);
                if (preg_match('/N[^\d]+(\d+[A-Z]\d+)/i', $content, $m) || preg_match('/\b(\d{2,}[A-Z]\d{5,})\b/i', $content, $m) || preg_match('/BOLETIM DE OCORR[ÊE]NCIA N[ºO]?:\s*(\d{10,})\b/i', $content, $m)) {
                    return $m[1];
                }
                return null;
            }
            
            // Para PDF ou TXT complexo, usa a API FastAPI para ler só a primeira página ultra-rápido
            try {
                $motorUrl = env('MOTOR_URL', 'http://localhost:8001') . '/quick-extract';
                $response = \Illuminate\Support\Facades\Http::timeout(5)->post($motorUrl, [
                    'file_path' => $filePath
                ]);
                if ($response->successful() && $response->json('boe')) {
                    return $response->json('boe');
                }
            } catch (\Exception $apiEx) {
                Log::warning("API quick-extract indisponível. Fallback desativado: " . $apiEx->getMessage());
            }
            
            return null;
        } catch (\Exception $e) {
            Log::warning("quickExtractBoeNumber falhou: " . $e->getMessage());
            return null;
        }
    }
}
