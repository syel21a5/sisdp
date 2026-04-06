<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class SeiController extends Controller
{
    private $scriptPath;
    private $pythonCommand;
    private $env;

    public function __construct()
    {
        $this->scriptPath = base_path('scripts/python/verificar_sei.py');
        $this->pythonCommand = PHP_OS_FAMILY === 'Windows' ? 'python' : 'python3';
        
        $this->env = [
            'PLAYWRIGHT_BROWSERS_PATH' => '0',
            'PATH' => getenv('PATH')
        ];
    }

    public function index(Request $request)
    {
        $tipo = $request->query('tipo', 'veiculo');
        $tipos = ['veiculo', 'celular', 'apreensao_outros'];
        
        if (!in_array($tipo, $tipos)) {
            $tipo = 'veiculo';
        }

        $moduloLabel = '';
        if ($tipo === 'veiculo') $moduloLabel = 'Veículos';
        if ($tipo === 'celular') $moduloLabel = 'Celulares';
        if ($tipo === 'apreensao_outros') $moduloLabel = 'Apreensão de Outros';

        return view('sei.index', compact('tipo', 'moduloLabel'));
    }

    public function conectar(Request $request)
    {
        $request->validate([
            'usuario' => 'required',
            'senha' => 'required',
            'orgao' => 'required'
        ]);

        $jobId = $request->jobId ?? 'sei_' . uniqid();
        $sessionDir = storage_path("app/public/sei_sessions/{$jobId}");
        File::ensureDirectoryExists($sessionDir);

        $credentials = [
            'usuario' => $request->usuario,
            'senha' => $request->senha,
            'orgao' => $request->orgao,
            'job_id' => $jobId
        ];
        
        return $this->dispatchGithubWorkflow('verificar_sei.yml', [
            'action' => 'login',
            'base_url' => $request->url_sei ?? 'https://sei.pe.gov.br/sei/',
            'config_b64' => base64_encode(json_encode($credentials)),
            'job_id' => $jobId,
            'callback_url' => url('/api/github/callback')
        ], $jobId);
    }

    public function listarSeis(Request $request)
    {
        $tipo = $request->query('tipo');
        if (!$tipo) abort(400, "Tipo não informado");

        $query = DB::connection('sisdp_syel')
            ->table('boletins')
            ->select('numero', 'envolvido', 'objetos_apreendidos', 'placa')
            ->whereNotNull('sei')
            ->where('sei', '!=', '')
            ->where(function ($q) {
                $q->whereNull('status_verificacao')
                  ->orWhere('status_verificacao', '!=', 'concluido');
            });

        if ($tipo === 'veiculo') {
            $query->whereNotNull('placa')->where('placa', '!=', '');
        } elseif ($tipo === 'celular') {
            $query->where(function ($q) {
                $q->where('objetos_apreendidos', 'like', '%celular%')
                  ->orWhere('objetos_apreendidos', 'like', '%smartphone%')
                  ->orWhere('objetos_apreendidos', 'like', '%iphone%')
                  ->orWhere('objetos_apreendidos', 'like', '%motorola%')
                  ->orWhere('objetos_apreendidos', 'like', '%samsung%');
            });
        } elseif ($tipo === 'apreensao_outros') {
            $query->whereNotNull('objetos_apreendidos')
                  ->where('objetos_apreendidos', '!=', '')
                  ->where('objetos_apreendidos', 'not like', '%celular%')
                  ->where('objetos_apreendidos', 'not like', '%smartphone%');
        }

        $registros = $query->limit(500)->get();

        $seis = [];
        foreach ($registros as $reg) {
            $seis_list = array_map('trim', explode(',', $reg->numero));
            foreach ($seis_list as $sei) {
                if ($sei) {
                    $seis[] = [
                        'sei' => $sei,
                        'boe' => preg_replace('/[^0-9]/', '', $reg->numero),
                        'envolvido' => $reg->envolvido ?? '',
                        'placa' => $reg->placa ?? ''
                    ];
                }
            }
        }

        return response()->json($seis);
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'jobId' => 'required',
            'seis' => 'required',
            'tipo' => 'required'
        ]);

        $jobId = $request->jobId;
        $sessionFile = storage_path("app/public/sei_sessions/{$jobId}/auth.json");
        $sessionData = File::exists($sessionFile) ? File::get($sessionFile) : null;

        $credentials = [
            'session_data' => $sessionData,
            'job_id' => $jobId
        ];

        return $this->dispatchGithubWorkflow('verificar_sei.yml', [
            'action' => 'check',
            'base_url' => $request->url_sei ?? 'https://sei.pe.gov.br/sei/',
            'seis' => is_array($request->seis) ? json_encode($request->seis) : $request->seis,
            'keywords' => $request->palavras_chave ?? '',
            'config_b64' => base64_encode(json_encode($credentials)),
            'job_id' => $jobId,
            'callback_url' => url('/api/github/callback')
        ], $jobId);
    }

    public function screenshot($jobId, $filename)
    {
        $path = \storage_path("app/public/sei_temp/{$jobId}/{$filename}");
        return File::exists($path) ? \response()->file($path) : \abort(404);
    }

    public function parar(Request $request)
    {
        $jobId = $request->jobId;
        if (!$jobId) {
            return response()->json(['success' => false, 'message' => 'JobId não informado']);
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = "wmic process where \"CommandLine like '%--job_id {$jobId}%'\" call terminate";
            @exec($cmd);
        } else {
            $cmd = "pkill -f \"--job_id {$jobId}\"";
            @exec($cmd);
        }

        return response()->json(['success' => true, 'message' => 'Comando de parada enviado (Apenas Local)']);
    }

    private function dispatchGithubWorkflow(string $workflow, array $inputs, string $jobId)
    {
        $token = env('GITHUB_TOKEN') ?: config('services.github.token');
        $repo = env('GITHUB_REPO') ?: config('services.github.repo');

        if (!$token || !$repo) {
            return response()->json(['success' => false, 'message' => 'GITHUB_TOKEN ou GITHUB_REPO não configurado no .env', 'status' => 'error'], 500);
        }

        $logFile = storage_path("app/public/jobs/{$jobId}/output.log");
        if (File::exists($logFile)) File::delete($logFile);
        File::ensureDirectoryExists(dirname($logFile));

        $response = Http::withToken($token)
            ->post("https://api.github.com/repos/{$repo}/actions/workflows/{$workflow}/dispatches", [
                'ref' => 'main',
                'inputs' => $inputs
            ]);

        if ($response->successful()) {
            return $this->streamPythonExecution($jobId);
        }
        return response()->json(['success' => false, 'message' => 'Erro ao disparar workflow: ' . $response->body(), 'status' => 'error'], 500);
    }

    private function streamPythonExecution(string $jobId)
    {
        return response()->stream(function () use ($jobId) {
            $logFile = storage_path("app/public/jobs/{$jobId}/output.log");
            $lastPos = 0;
            $maxWait = 180;
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
                            if (($data['status'] ?? '') === 'finished' || ($data['status'] ?? '') === 'error') {
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
}
