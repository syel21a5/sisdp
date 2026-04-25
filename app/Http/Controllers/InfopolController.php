<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class InfopolController extends Controller
{
    private $pythonCommand;
    private $scriptPath;
    private $env;

    public function __construct()
    {
        $this->pythonCommand = PHP_OS_FAMILY === 'Windows' ? "C:\\Python313\\python.exe" : "python3";
        $this->scriptPath = base_path('scripts/python/baixar_boes.py');
        
        $this->env = getenv();
        $this->env['PYTHONUNBUFFERED'] = '1';
        $this->env['PYTHONIOENCODING'] = 'UTF-8';
        $this->env['PYTHONPATH'] = 'C:\\Users\\VGR\\AppData\\Roaming\\Python\\Python313\\site-packages';
    }

    public function index()
    {
        return view('infopol.index');
    }

    /**
     * Tenta realizar o login e salvar a sessão (auth state).
     */
    public function conectar(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'senha' => 'required'
        ]);

        $jobId = $request->jobId ?? 'sess_' . uniqid();
        $sessionFile = storage_path("app/public/infopol_sessions/{$jobId}/auth.json");
        
        // Garante diretório da sessão
        File::ensureDirectoryExists(dirname($sessionFile));

        $payload = [
            'action' => 'login',
            'session_file' => $sessionFile,
            'usuario' => $request->usuario,
            'senha' => $request->senha
        ];

        return $this->streamPythonExecution($payload, $jobId);
    }

    /**
     * Realiza a busca e retorna a lista de BOEs (JSON).
     */
    public function buscar(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'jobId' => 'required|string'
        ]);

        $jobId = $request->jobId;
        $sessionFile = storage_path("app/public/infopol_sessions/{$jobId}/auth.json");
        
        if (!File::exists($sessionFile)) {
            return response()->json(['success' => false, 'message' => 'Sessão não encontrada. Conecte-se novamente.', 'status' => 'expired'], 401);
        }

        $payload = [
            'action' => 'search',
            'nome' => $request->nome,
            'inicio' => $request->inicio ?? '',
            'fim' => $request->fim ?? '',
            'delegacia' => $request->delegacia ?? '',
            'session_file' => $sessionFile
        ];

        return $this->streamPythonExecution($payload, $jobId);
    }

    /**
     * Baixa os BOEs selecionados.
     */
    public function baixarSelecionados(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'jobId' => 'required|string',
            'indices' => 'required|string' // Ex: "0,1,5"
        ]);

        $jobId = $request->jobId;
        $sessionFile = storage_path("app/public/infopol_sessions/{$jobId}/auth.json");
        $outputDir = storage_path("app/public/infopol_temp/{$jobId}/PDFs");
        
        File::ensureDirectoryExists($outputDir);

        $payload = [
            'action' => 'download',
            'nome' => $request->nome,
            'inicio' => $request->inicio ?? '',
            'fim' => $request->fim ?? '',
            'indices' => $request->indices,
            'session_file' => $sessionFile,
            'output_dir' => $outputDir
        ];

        return $this->streamPythonExecution($payload, $jobId);
    }

    /**
     * Helper para executar o Python e realizar o streaming da saída.
     */
    private function streamPythonExecution(array $payload, ?string $jobId = null)
    {
        return response()->stream(function () use ($payload, $jobId) {
            try {
                $motorUrl = env('MOTOR_URL', 'http://localhost:8001') . '/infopol-robot';

                $ch = curl_init($motorUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: text/event-stream']);
                curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $buffer) use ($jobId) {
                    if (strpos($buffer, '{') !== false) {
                        $lines = explode("\n", $buffer);
                        foreach($lines as $line) {
                            $line = trim($line);
                            if ($line) {
                                $data = json_decode($line, true);
                                if ($data) {
                                    if (($data['status'] ?? '') === 'finished' && $jobId) {
                                        $data['download_url'] = route('infopol.download', ['jobId' => $jobId]);
                                    }
                                    echo json_encode($data) . "\n";
                                }
                            }
                        }
                    }
                    if (ob_get_level() > 0) ob_flush();
                    flush();
                    return strlen($buffer);
                });
                
                curl_exec($ch);
                
                if(curl_errno($ch)) {
                    $errorMsg = curl_error($ch);
                    echo json_encode(['success' => false, 'message' => "Erro de conexão com Motor Python: " . $errorMsg, 'status' => 'error']) . "\n";
                }
                
                curl_close($ch);

            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage(), 'status' => 'error']) . "\n";
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function download($jobId)
    {
        $baseDir = storage_path('app/public/infopol_temp');
        $jobRootDir = $baseDir . DIRECTORY_SEPARATOR . $jobId;
        $pdfDir = $jobRootDir . DIRECTORY_SEPARATOR . 'PDFs';
        $zipPath = $jobRootDir . DIRECTORY_SEPARATOR . "extracao_{$jobId}.zip";

        if (!File::exists($pdfDir)) {
            return abort(404, "Arquivos não encontrados.");
        }

        $files = File::files($pdfDir);
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $zip->addFile($file->getPathname(), $file->getFilename());
                }
            }
            $zip->close();
        }

        return response()->download($zipPath, "BOEs_{$jobId}.zip")->deleteFileAfterSend(true);
    }

    public function screenshot($jobId, $filename)
    {
        $path = storage_path("app/public/infopol_temp/{$jobId}/{$filename}");
        return File::exists($path) ? response()->file($path) : abort(404);
    }
}
