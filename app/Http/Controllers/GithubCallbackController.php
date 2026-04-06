<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GithubCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $jobId = $payload['job_id'] ?? null;
        
        if (!$jobId) {
            return response()->json(['success' => false, 'message' => 'job_id missing']);
        }
        
        // As sessões podem ser de infopol ou sei, vamos salvar num log geral para aquele jobId
        $logDir = storage_path("app/public/jobs/{$jobId}");
        File::ensureDirectoryExists($logDir);
        
        $logFile = "{$logDir}/output.log";
        
        // Extração de arquivos base64 se houver
        if (isset($payload['data']) && is_array($payload['data']) && isset($payload['data']['file_base64'])) {
            $filename = $payload['data']['filename'] ?? 'file_' . time() . '.pdf';
            $fileData = base64_decode($payload['data']['file_base64']);
            
            // Verifica de qual módulo é baseado se tá enviando pdf (Infopol)
            $pdfDir = storage_path("app/public/infopol_temp/{$jobId}/PDFs");
            File::ensureDirectoryExists($pdfDir);
            
            File::put("{$pdfDir}/{$filename}", $fileData);
            
            // Remove o base64 brutal do log de progresso para não pesar o JSON enviado para o frontend
            unset($payload['data']['file_base64']);
            $payload['message'] .= " (Arquivo {$filename} transferido)";
        }

        // Extração de sessão (cookies)
        if (isset($payload['data']) && is_array($payload['data']) && isset($payload['data']['session_data'])) {
            $sessionContent = $payload['data']['session_data'];
            
            // Tenta detectar se é Infopol ou SEI (pelo job_id ou status)
            // Por segurança, vamos salvar em ambos os locais se houver diretório ou guardar no job_dir
            $infopolSessionDir = storage_path("app/public/infopol_sessions/{$jobId}");
            if (File::exists($infopolSessionDir)) {
                File::put("{$infopolSessionDir}/auth.json", $sessionContent);
            }

            $seiSessionDir = storage_path("app/public/sei_sessions/{$jobId}");
            if (File::exists($seiSessionDir)) {
                File::put("{$seiSessionDir}/auth.json", $sessionContent);
            }
            
            // Também guarda no diretório do Job para backup
            File::put("{$logDir}/auth.json", $sessionContent);

            unset($payload['data']['session_data']);
            $payload['message'] .= " (Sessão sincronizada)";
        }
        
        $jsonStr = json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n";
        File::append($logFile, $jsonStr);
        
        return response()->json(['success' => true]);
    }
}
