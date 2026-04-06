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

        $command = "\"{$this->pythonCommand}\" \"{$this->scriptPath}\" --action login --session_file ".escapeshellarg($sessionFile);

        $credentials = [
            'usuario' => $request->usuario,
            'senha' => $request->senha
        ];

        return $this->streamPythonExecution($command, $credentials, $jobId);
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

        $nome = escapeshellarg($request->nome);
        $inicio = escapeshellarg($request->inicio ?? '');
        $fim = escapeshellarg($request->fim ?? '');
        $delegacia = escapeshellarg($request->delegacia ?? '');

        $command = "\"{$this->pythonCommand}\" \"{$this->scriptPath}\" --action search --nome {$nome} --inicio {$inicio} --fim {$fim} --delegacia {$delegacia} --session_file ".escapeshellarg($sessionFile);

        return $this->streamPythonExecution($command, null, $jobId);
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

        $nome = escapeshellarg($request->nome);
        $inicio = escapeshellarg($request->inicio ?? '');
        $fim = escapeshellarg($request->fim ?? '');
        $indices = escapeshellarg($request->indices);
        $outputDirEscaped = escapeshellarg($outputDir);

        $command = "\"{$this->pythonCommand}\" \"{$this->scriptPath}\" --action download --nome {$nome} --inicio {$inicio} --fim {$fim} --indices {$indices} --session_file ".escapeshellarg($sessionFile)." --output_dir {$outputDirEscaped}";

        return $this->streamPythonExecution($command, null, $jobId);
    }

    /**
     * Helper para executar o Python e realizar o streaming da saída.
     */
    private function streamPythonExecution($command, $credentials = null, $jobId = null)
    {
        return response()->stream(function () use ($command, $credentials, $jobId) {
            try {
                $process = Process::fromShellCommandline($command);
                $process->setTimeout(600);
                $process->setEnv($this->env);
                
                if ($credentials) {
                    $process->setInput(json_encode($credentials));
                }

                $process->run(function ($type, $buffer) use ($jobId) {
                    // Se for JSON de progresso/resultado, repassa
                    if (strpos($buffer, '{') !== false) {
                        $data = json_decode($buffer, true);
                        if ($data) {
                            if (($data['status'] ?? '') === 'finished') {
                                $data['download_url'] = route('infopol.download', ['jobId' => $jobId]);
                            }
                            echo json_encode($data) . "\n";
                        } else {
                            echo $buffer;
                        }
                    } else {
                        // Log de texto comum para debug (opcional)
                        // echo json_encode(['message' => $buffer, 'status' => 'raw']) . "\n";
                    }
                    
                    if (ob_get_level() > 0) ob_flush();
                    flush();
                });

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
