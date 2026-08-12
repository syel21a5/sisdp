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
        $this->env['PYTHONPATH'] = 'C:\\Users\\VGR\\AppData\\Roaming\\Python\\Python313\\site-packages';
    }

    public function index()
    {
        return \view('sei.index');
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

        $payload = [
            'action' => 'login',
            'base_url' => $request->base_url,
            'session_file' => $sessionFile,
            'orgao' => $request->orgao,
            'usuario' => $request->usuario,
            'senha' => $request->senha,
        ];

        return $this->streamPythonExecution($payload, $jobId);
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

        $payload = [
            'action' => 'check',
            'base_url' => $baseUrl,
            'session_file' => $sessionFile,
            'seis_file' => $seisFile,
            'output_dir' => $outputDir,
            'keywords' => $keywords,
            'job_id' => $jobId,
            'orgao' => $request->orgao,
        ];

        if ($request->usuario && $request->senha) {
            $payload['usuario'] = $request->usuario;
            $payload['senha'] = $request->senha;
        }

        return $this->streamPythonExecution($payload, $jobId);
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

    private function streamPythonExecution(array $payload, ?string $jobId = null)
    {
        return \response()->stream(function () use ($payload, $jobId) {
            try {
                $motorUrl = env('MOTOR_URL', 'http://localhost:8001') . '/sei-robot';

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
                                    if (($data['status'] ?? '') === 'screenshot' && isset($data['data']['filename']) && $jobId) {
                                        $data['data']['url'] = \route('sei.screenshot', ['jobId' => $jobId, 'filename' => $data['data']['filename']]);
                                    }
                                    echo json_encode($data) . "\n";
                                }
                            }
                        }
                    }
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                    return strlen($buffer);
                });
                
                curl_exec($ch);
                
                if(curl_errno($ch)) {
                    $errorMsg = curl_error($ch);
                    echo json_encode(['success' => false, 'message' => "Erro de conexão com Motor Python: " . $errorMsg, 'status' => 'error']) . "\n";
                }
                
                curl_close($ch);

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

