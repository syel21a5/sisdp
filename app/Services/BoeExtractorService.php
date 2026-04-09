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
            
            $output = \shell_exec($command);
            
            // Limpeza
            @unlink($tmpPath);

            // 4. Verifica o output do shell
            if (!$output) {
                return ['success' => false, 'message' => "Falha silenciosa ao executar o extrator Python.", 'status' => 500];
            }

            // 5. Faz o parse do JSON do Python (usa regex para ignorar linhas de log como [BOE-IA])
            $json = null;
            if (preg_match('/\{.*\}/s', $output, $matches)) {
                $json = json_decode($matches[0], true);
            }

            if (is_array($json) && isset($json['success'])) {

                if ($json['success']) {
                    $dados = $json['dados'] ?? [];
                    
                    // ✅ SALVAR NO CACHE (Sempre como apfd para ser o mestre)
                    file_put_contents($cacheFileHashComplete, json_encode($dados));
                    
                    if (!empty($dados['boe'])) {
                        $boeLimpo = preg_replace('/[^A-Za-z0-9]/', '', $dados['boe']);
                        $cacheFileBoe = $cacheDir . "/boe_{$boeLimpo}_apfd.json";
                        file_put_contents($cacheFileBoe, json_encode($dados));
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
