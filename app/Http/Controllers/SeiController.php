<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class SeiController extends Controller
{
    private string $pythonCommand;
    private string $scriptPath;
    private array $env;

    public function __construct()
    {
        $this->pythonCommand = PHP_OS_FAMILY === 'Windows' ? 'C:\\Python313\\python.exe' : 'python3';
        $this->scriptPath = \base_path('scripts/python/verificar_sei.py');

        $this->env = getenv();
        $this->env['PYTHONUNBUFFERED'] = '1';
        $this->env['PYTHONIOENCODING'] = 'UTF-8';
        $this->env['DEBUG'] = 'pw:browser*';
        if (!isset($this->env['HOME'])) {
            $this->env['HOME'] = '/home/www';
        }
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
            'base_url' => 'required|string',
            'usuario' => 'required|string',
            'senha' => 'required|string',
            'orgao' => 'nullable|string',
        ]);

        $jobId = $request->jobId ?? 'sess_' . uniqid();
        $sessionFile = \storage_path("app/public/sei_sessions/{$jobId}/auth.json");
        File::ensureDirectoryExists(dirname($sessionFile));

        $baseUrl = escapeshellarg($request->base_url);
        $orgao = $request->orgao ? ' --orgao ' . escapeshellarg($request->orgao) : '';
        $command = "\"{$this->pythonCommand}\" \"{$this->scriptPath}\" --action login --base_url {$baseUrl} --session_file " . escapeshellarg($sessionFile) . $orgao;

        $credentials = [
            'usuario' => $request->usuario,
            'senha' => $request->senha,
        ];

        return $this->streamPythonExecution($command, $credentials, $jobId);
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
        $baseUrl = $request->base_url;
        $keywords = (string) ($request->keywords ?? '');

        $sessionFile = \storage_path("app/public/sei_sessions/{$jobId}/auth.json");
        File::ensureDirectoryExists(dirname($sessionFile));

        $outputDir = \storage_path("app/public/sei_temp/{$jobId}");
        File::ensureDirectoryExists($outputDir);

        $seisFile = \storage_path("app/public/sei_temp/{$jobId}/seis.json");
        File::put($seisFile, json_encode(array_values($request->seis), JSON_UNESCAPED_UNICODE));

        $orgao = $request->orgao ? ' --orgao ' . escapeshellarg($request->orgao) : '';
        $command = "\"{$this->pythonCommand}\" \"{$this->scriptPath}\" --action check --base_url " . escapeshellarg($baseUrl) .
            ' --session_file ' . escapeshellarg($sessionFile) .
            ' --seis_file ' . escapeshellarg($seisFile) .
            ' --output_dir ' . escapeshellarg($outputDir) .
            ' --keywords ' . escapeshellarg($keywords) . 
            ' --job_id ' . escapeshellarg($jobId) . $orgao;

        $credentials = null;
        if ($request->usuario && $request->senha) {
            $credentials = [
                'usuario' => $request->usuario,
                'senha' => $request->senha,
            ];
        }

        return $this->streamPythonExecution($command, $credentials, $jobId);
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
            // Em Windows, usamos o wmic para localizar o processo pela linha de comando e terminá-lo
            // Filtrando especificamente pelo --job_id único da sessão
            $cmd = "wmic process where \"CommandLine like '%--job_id {$jobId}%'\" call terminate";
            @exec($cmd);
        } else {
            // Em Linux/Mac
            $cmd = "pkill -f \"--job_id {$jobId}\"";
            @exec($cmd);
        }

        return response()->json(['success' => true, 'message' => 'Comando de parada enviado']);
    }

    private function streamPythonExecution(string $command, ?array $credentials = null, ?string $jobId = null)
    {
        return \response()->stream(function () use ($command, $credentials, $jobId) {
            try {
                $process = Process::fromShellCommandline($command);
                $process->setTimeout(900);
                $process->setEnv($this->env);

                if ($credentials) {
                    $process->setInput(json_encode($credentials));
                }

                $process->run(function ($type, $buffer) use ($jobId) {
                    if ($type === Process::ERR) {
                        $msg = trim((string) $buffer);
                        if ($msg !== '') {
                            echo json_encode(['success' => false, 'message' => $msg, 'status' => 'error']) . "\n";
                        }
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                        return;
                    }
                    if (strpos($buffer, '{') !== false) {
                        $data = json_decode($buffer, true);
                        if ($data) {
                            if (($data['status'] ?? '') === 'screenshot' && isset($data['data']['filename']) && $jobId) {
                                $data['data']['url'] = \route('sei.screenshot', ['jobId' => $jobId, 'filename' => $data['data']['filename']]);
                            }
                            echo json_encode($data) . "\n";
                        }
                    }

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                });
            } catch (\Throwable $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage(), 'status' => 'error']) . "\n";
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}

