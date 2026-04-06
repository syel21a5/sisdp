<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class SeiController extends Controller
{
    private string $pythonCommand;
    private string $scriptPath;
    private array $env;

    public function __construct()
    {
        $this->pythonCommand = PHP_OS_FAMILY === 'Windows' ? 'C:\\Python313\\python.exe' : 'sudo /usr/local/bin/run_playwright.sh';
        $this->scriptPath = \base_path('scripts/python/verificar_sei.py');

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

    public function index(Request $request)
    {
        $tipo = $request->query('tipo', 'veiculo');
        $this->verificarPermissao($tipo);
        return \view('sei.index');
    }

    private function verificarPermissao(string $tipo)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Não autenticado.');
        }

        if ($user->nivel_acesso === 'administrador') {
            return true;
        }

        $permissions = $user->permissions ?? [];
        
        if (in_array($tipo, ['veiculo', 'veiculos']) && empty($permissions['veiculo'])) {
            abort(403, 'Acesso restrito. Você não possui permissão para o módulo de Veículos.');
        }

        if (in_array($tipo, ['celular', 'celulares']) && empty($permissions['celular'])) {
            abort(403, 'Acesso restrito. Você não possui permissão para o módulo de Celulares.');
        }

        if (in_array($tipo, ['outros', 'apreensao_outros']) && empty($permissions['apreensao_outros'])) {
            abort(403, 'Acesso restrito. Você não possui permissão para o módulo de Outros Itens.');
        }

        return true;
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
        $request->validate([
            'status' => 'nullable|string|max:50',
            'limit' => 'nullable|integer|min:1|max:1000',
            'tipo' => 'nullable|string|in:veiculo,celular',
        ]);

        $limit = (int) ($request->limit ?? 200);
        $status = $request->status;
        $tipo = $request->tipo ?? 'veiculo';
        
        $this->verificarPermissao($tipo);

        if ($tipo === 'celular') {
            $query = DB::table('cadcelular')
                ->leftJoin('usuario', 'cadcelular.user_id', '=', 'usuario.id')
                ->whereNotNull('cadcelular.processo')
                ->where('cadcelular.processo', '<>', '')
                ->orderByDesc('cadcelular.id');

            if ($status) {
                $query->where('cadcelular.status', $status);
            }

            $items = $query->limit($limit)->get([
                'cadcelular.id',
                'cadcelular.data',
                'cadcelular.boe',
                'cadcelular.pessoa',
                'cadcelular.processo as sei',
                'cadcelular.status',
                'usuario.nome as responsavel',
            ]);
        } else {
            $query = DB::table('cadveiculo')
                ->leftJoin('usuario', 'cadveiculo.user_id', '=', 'usuario.id')
                ->whereNotNull('cadveiculo.sei')
                ->where('cadveiculo.sei', '<>', '')
                ->orderByDesc('cadveiculo.id');

            if ($status) {
                $query->where('cadveiculo.status', $status);
            }

            $items = $query->limit($limit)->get([
                'cadveiculo.id',
                'cadveiculo.data',
                'cadveiculo.boe',
                'cadveiculo.pessoa',
                'cadveiculo.placa as identificador', // Para veículos, usamos a placa
                'cadveiculo.sei',
                'cadveiculo.status',
                'usuario.nome as responsavel',
            ]);
        }

        return \response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function verificar(Request $request)
    {
        $request->validate([
            'base_url' => 'required|string',
            'jobId' => 'required|string',
            'seis' => 'required|array|min:1',
            'seis.*' => 'required|string',
            'keywords' => 'nullable|string',
            'usuario' => 'nullable|string',
            'senha' => 'nullable|string',
            'orgao' => 'nullable|string',
        ]);

        $jobId = $request->jobId;
        $payload = [
            'action' => 'check',
            'base_url' => $request->base_url,
            'seis' => $request->seis,
            'keywords' => $request->keywords ?? '',
            'job_id' => $jobId,
            'callback_url' => url('/api/github/callback')
        ];

        if ($request->usuario && $request->senha) {
            $payload['config_b64'] = base64_encode(json_encode([
                'usuario' => $request->usuario,
                'senha' => $request->senha,
                'orgao' => $request->orgao
            ]));
        }

        return $this->dispatchGithubWorkflow('verificar_sei.yml', $payload, $jobId);
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

        return response()->json(['success' => true, 'message' => 'Comando de parada enviado']);
    }

    private function dispatchGithubWorkflow(string $workflow, array $inputs, string $jobId)
    {
        $response = Http::withToken(config('services.github.token'))
            ->post("https://api.github.com/repos/" . config('services.github.repo') . "/actions/workflows/{$workflow}/dispatches", [
                'ref' => 'main',
                'inputs' => $inputs
            ]);

        if ($response->successful()) {
            return $this->streamPythonExecution($jobId);
        }
        return response()->json(['success' => false, 'message' => 'Erro ao disparar workflow'], 500);
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
