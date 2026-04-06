<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class InfopolController extends Controller
{
    private $pythonCommand;
    private $scriptPath;
    private $env;

    public function __construct()
    {
        $this->pythonCommand = PHP_OS_FAMILY === 'Windows' ? "C:\\Python313\\python.exe" : "sudo /usr/local/bin/run_playwright.sh";
        $this->scriptPath = base_path('scripts/python/baixar_boes.py');
        
        $this->env = getenv();
        $this->env['PYTHONUNBUFFERED'] = '1';
        $this->env['PYTHONIOENCODING'] = 'UTF-8';
        $this->env['DEBUG'] = 'pw:browser*';
        $this->env['HOME'] = '/home/www';
        $this->env['PLAYWRIGHT_BROWSERS_PATH'] = '/home/www/.cache/ms-playwright';
        if (!isset($this->env['PATH'])) {
            $this->env['PATH'] = '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin';
        }
    }

    public function index()
    {
        return view('infopol.index');
    }

    /**
     * Realiza a conexão (login) e salva a sessão.
     */
    public function conectar(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'senha' => 'required|string',
        ]);

        $jobId = $request->jobId ?? 'sess_' . uniqid();
        $sessionDir = storage_path("app/public/infopol_sessions/{$jobId}");
        File::ensureDirectoryExists($sessionDir);

        $credentials = [
            'usuario' => $request->usuario,
            'senha' => $request->senha,
            'job_id' => $jobId
        ];
        
        return $this->dispatchGithubWorkflow('baixar_boes.yml', [
            'action' => 'login',
            'config_b64' => base64_encode(json_encode($credentials)),
            'job_id' => $jobId,
            'callback_url' => url('/api/github/callback')
        ], $jobId);
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
        $sessionData = File::exists($sessionFile) ? File::get($sessionFile) : null;

        $credentials = [
            'nome' => $request->nome,
            'session_data' => $sessionData,
            'job_id' => $jobId
        ];

        return $this->dispatchGithubWorkflow('baixar_boes.yml', [
            'action' => 'search',
            'nome' => $request->nome,
            'inicio' => $request->inicio ?? '',
            'fim' => $request->fim ?? '',
            'delegacia' => $request->delegacia ?? '',
            'config_b64' => base64_encode(json_encode($credentials)),
            'job_id' => $jobId,
            'callback_url' => url('/api/github/callback')
        ], $jobId);
    }

    /**
     * Baixa os BOEs selecionados.
     */
    public function baixarSelecionados(Request $request)
    {
        $request->validate([
            'nome' => 'required|string',
            'jobId' => 'required|string',
            'indices' => 'required|string'
        ]);

        $jobId = $request->jobId;
        $sessionFile = storage_path("app/public/infopol_sessions/{$jobId}/auth.json");
        $sessionData = File::exists($sessionFile) ? File::get($sessionFile) : null;

        $credentials = [
            'nome' => $request->nome,
            'session_data' => $sessionData,
            'job_id' => $jobId
        ];

        return $this->dispatchGithubWorkflow('baixar_boes.yml', [
            'action' => 'download',
            'nome' => $request->nome,
            'inicio' => $request->inicio ?? '',
            'fim' => $request->fim ?? '',
            'indices' => $request->indices,
            'config_b64' => base64_encode(json_encode($credentials)),
            'job_id' => $jobId,
            'callback_url' => url('/api/github/callback')
        ], $jobId);
    }

    private function dispatchGithubWorkflow($workflow, $inputs, $jobId)
    {
        $token = env('GITHUB_TOKEN');
        $repo = env('GITHUB_REPO');

        if (!$token || !$repo) {
            return response()->json(['success' => false, 'message' => 'GITHUB_TOKEN ou GITHUB_REPO não configurado no .env'], 500);
        }

        // Limpa o log antigo se existir
        $logFile = storage_path("app/public/jobs/{$jobId}/output.log");
        if (File::exists($logFile)) File::delete($logFile);
        File::ensureDirectoryExists(dirname($logFile));

        $response = Http::withToken($token)
            ->post("https://api.github.com/repos/{$repo}/actions/workflows/{$workflow}/dispatches", [
                'ref' => 'main',
                'inputs' => $inputs
            ]);

        if ($response->failed()) {
            return response()->json(['success' => false, 'message' => 'Falha ao disparar GitHub: ' . $response->body()], 500);
        }

        return $this->streamPythonExecution('', null, $jobId);
    }

    /**
     * Helper para executar o Python e realizar o streaming da saída.
     */
    private function streamPythonExecution(string $command, ?array $credentials = null, ?string $jobId = null)
    {
        return response()->stream(function () use ($jobId) {
            $logFile = storage_path("app/public/jobs/{$jobId}/output.log");
            $lastPos = 0;
            $maxWait = 180; // 3 minutos
            $startTime = time();
            $finished = false;

            echo json_encode(['success' => true, 'message' => 'Aguardando runner do GitHub iniciar...', 'status' => 'processing']) . "\n";
            if (ob_get_level() > 0) ob_flush();
            flush();

            while (time() - $startTime < $maxWait && !$finished) {
                if (File::exists($logFile)) {
                    $content = file_get_contents($logFile);
                    $lines = explode("\n", substr($content, $lastPos));
                    $lastPos = strlen($content);

                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!$line) continue;

                        $data = json_decode($line, true);
                        if ($data) {
                            if (($data['status'] ?? '') === 'finished' && $jobId) {
                                $data['download_url'] = route('infopol.download', ['jobId' => $jobId]);
                                $finished = true;
                            }
                            if (($data['status'] ?? '') === 'error' ) {
                                $finished = true;
                            }
                            echo $line . "\n";
                        }
                    }
                }
                
                if (ob_get_level() > 0) ob_flush();
                flush();
                
                if (!$finished) sleep(2);
            }

            if (!$finished && time() - $startTime >= $maxWait) {
                echo json_encode(['success' => false, 'message' => 'Tempo de espera do GitHub Actions esgotado.', 'status' => 'error']) . "\n";
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
