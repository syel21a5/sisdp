<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BoeExtractorService
{
    /**
     * Extrai dados de um BOE (PDF ou Texto) usando inteligência artificial via Python.
     * Centraliza a lógica para evitar duplicação nos Controllers.
     *
     * @param Request $request Acoplado para pegar 'pdfBOE' ou 'textoBOE'
     * @param string $type O tipo de extração ("veiculo", "celular", "administrativo", "intimacao", "apfd")
     * @return \Illuminate\Http\JsonResponse
     */
    public function extract(Request $request, string $type): array
    {
        try {
            // Aumenta o timeout do PHP para 5 minutos (a IA pode demorar no servidor)
            set_time_limit(300);
            ini_set('max_execution_time', '300');

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
            // Verificamos também se o arquivo não está corrompido (tamanho mínimo razoável)
            if (file_exists($cacheFileHashComplete) && filesize($cacheFileHashComplete) > 100) {
                $cachedData = json_decode(file_get_contents($cacheFileHashComplete), true);
                if ($cachedData) {
                    @unlink($tmpPath); 
                    return [
                        'success' => true,
                        'dados' => $cachedData,
                        'cached' => true,
                        'cache_type' => 'apfd'
                    ];
                }
            }

            // 3. Prepara e executa o comando Python (SEMPRE forçando 'apfd' para o cache ser universal)
            $scriptPath = base_path('scripts/python/boe_extractor.py');
            $pythonCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'python' : 'python3';
            
            // Forçamos o type 'apfd' para que a IA extraia tudo de uma vez e salve no cache
            $command = escapeshellcmd($pythonCmd) . " " . escapeshellarg($scriptPath) . " --type apfd " . escapeshellarg($tmpPath) . " 2>&1";
            
            Log::info("Executando Comando BOE: " . $command);
            Log::info("Arquivo Temporário existe? " . (file_exists($tmpPath) ? 'SIM' : 'NÃO'));
            
            $output = \shell_exec($command);
            
            // Limpeza
            @unlink($tmpPath);

            // 4. Verifica o output do shell
            Log::info("Saída do Python (primeiros 200 chars): " . substr($output ?? '(null)', 0, 200));

            if (!$output) {
                Log::error("shell_exec retornou null/vazio. Possível timeout ou função desabilitada.");
                return ['success' => false, 'message' => "Falha silenciosa ao executar o extrator Python.", 'status' => 500];
            }

            // 5. Faz o parse do JSON do Python
            // Usamos regex para encontrar o JSON caso o Python tenha cuspido algum aviso extra ou erro de site-packages
            $json = null;
            if (preg_match('/\{.*\}/s', $output, $matches)) {
                $json = json_decode($matches[0], true);
            }

            if (is_array($json) && isset($json['success'])) {
                if ($json['success']) {
                    $dados = $json['dados'] ?? [];
                    
                    // ✅ SALVAR NO CACHE (Apenas se tiver dados reais para não "viciar" o cache com erros)
                    if (!empty($dados['boe']) && count($dados) > 5) {
                        $jsonParaCache = json_encode($dados, JSON_UNESCAPED_UNICODE);
                        file_put_contents($cacheFileHashComplete, $jsonParaCache);
                        
                        $boeLimpo = preg_replace('/[^A-Za-z0-9]/', '', $dados['boe']);
                        $cacheFileBoe = $cacheDir . "/boe_{$boeLimpo}_apfd.json";
                        file_put_contents($cacheFileBoe, $jsonParaCache);
                    }

                    return [
                        'success' => true, 
                        'dados' => $dados,
                        'cached' => false
                    ];
                } else {
                    $msg = $json['error'] ?? 'Erro desconhecido no script Python.';
                    Log::error("IA retornou erro mapeado (apfd): " . $msg);
                    return ['success' => false, 'message' => "Falha na IA: " . $msg, 'status' => 500];
                }
            } else {
                Log::error("Script Python falhou brutalmente (apfd):\n" . $output);
                return ['success' => false, 'message' => "Falha estrutural ao executar Python:\n" . $output, 'status' => 500];
            }

        } catch (\Exception $e) {
            Log::error("Exceção na extração de BOE ({$type}): " . $e->getMessage());
            return ['success' => false, 'message' => "Erro Interno do Servidor: " . $e->getMessage(), 'status' => 500];
        }
    }
}
