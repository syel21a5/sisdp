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

            // 1. Processa o Upload (PDF ou Texto)
            if ($request->hasFile('pdfBOE') && $request->file('pdfBOE')->isValid()) {
                $pdf = $request->file('pdfBOE');
                $tmpPath = sys_get_temp_dir() . "/boe_{$type}_upload_" . uniqid() . '.pdf';
                $pdf->move(sys_get_temp_dir(), basename($tmpPath));
            } else {
                $texto = $request->input('textoBOE', '');
                if (empty(trim($texto))) {
                    return ['success' => false, 'message' => 'Escolha um PDF ou cole o texto do BOE antes de processar.', 'status' => 400];
                }
                $tmpPath = sys_get_temp_dir() . "/boe_{$type}_texto_" . uniqid() . '.txt';
                file_put_contents($tmpPath, $texto);
            }

            // 2. Prepara e executa o comando Python
            $scriptPath = base_path('scripts/python/boe_extractor.py');
            $pythonCmd = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'python' : 'python3';
            
            // O tipo 'apfd' usa o prompt padrão do script quando não se passa nada ou apenas passa na mão. 
            // Para não quebrar o python se o script não suportar a flag explicitamente, o passamos:
            // Mas o boe_extractor.py foi atualizado hoje cedo ($args->type default='apfd').
            $command = escapeshellcmd($pythonCmd) . " " . escapeshellarg($scriptPath) . " --type " . escapeshellarg($type) . " " . escapeshellarg($tmpPath) . " 2>&1";
            
            $output = \shell_exec($command);
            
            // Limpeza
            @unlink($tmpPath);

            // 3. Verifica o output do shell
            if (!$output) {
                return ['success' => false, 'message' => "Falha silenciosa ao executar o extrator Python.", 'status' => 500];
            }

            // 4. Faz o parse do JSON do Python
            $json = json_decode($output, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($json['success'])) {
                if ($json['success']) {
                    return [
                        'success' => true, 
                        'dados' => $json['dados'] ?? []
                    ];
                } else {
                    $msg = $json['error'] ?? 'Erro desconhecido no script Python.';
                    Log::error("IA retornou erro mapeado ({$type}): " . $msg);
                    return ['success' => false, 'message' => "Falha na IA: " . $msg, 'status' => 500];
                }
            } else {
                Log::error("Script Python falhou brutalmente ({$type}):\n" . $output);
                return ['success' => false, 'message' => "Falha estrutural ao executar Python:\n" . $output, 'status' => 500];
            }

        } catch (\Exception $e) {
            Log::error("Exceção na extração de BOE ({$type}): " . $e->getMessage());
            return ['success' => false, 'message' => "Erro Interno do Servidor: " . $e->getMessage(), 'status' => 500];
        }
    }
}
