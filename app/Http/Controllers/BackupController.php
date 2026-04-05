<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    /**
     * Gera um backup completo do banco de dados e envia como download ZIP.
     */
    public function download()
    {
        // Proteção: apenas admin
        $user = Auth::user();
        if (!$user || $user->nivel_acesso !== 'administrador') {
            abort(403, 'Acesso restrito a administradores.');
        }

        // Credenciais do banco (lidas do .env via config)
        $host     = config('database.connections.mysql.host', '127.0.0.1');
        $port     = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Nomes dos arquivos temporários
        $timestamp = now()->format('Y-m-d_His');
        $sqlFile   = sys_get_temp_dir() . "/backup_{$database}_{$timestamp}.sql";
        $zipFile   = sys_get_temp_dir() . "/backup_{$database}_{$timestamp}.zip";

        try {
            // ========== PASSO 1: Executar mysqldump ==========
            $mysqldumpPath = $this->findMysqldump();

            // Montar o comando (escapando a senha)
            $command = sprintf(
                '%s --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --add-drop-table %s > %s 2>&1',
                escapeshellcmd($mysqldumpPath),
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($sqlFile)
            );

            // Executar
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            // Verificar se o arquivo foi criado e tem conteúdo
            if ($returnCode !== 0 || !file_exists($sqlFile) || filesize($sqlFile) === 0) {
                $errorMsg = implode("\n", $output);
                Log::error("Backup falhou: code={$returnCode} | {$errorMsg}");
                
                // Limpar arquivo vazio se existir
                @unlink($sqlFile);
                
                return back()->with('error', 'Falha ao gerar backup do banco de dados. Verifique se o mysqldump está disponível no servidor.');
            }

            // ========== PASSO 2: Comprimir em ZIP ==========
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                @unlink($sqlFile);
                return back()->with('error', 'Não foi possível criar o arquivo ZIP.');
            }

            // Adicionar o .sql dentro do ZIP com nome legível
            $zip->addFile($sqlFile, "backup_{$database}_{$timestamp}.sql");
            $zip->close();

            // ========== PASSO 3: Limpar arquivo .sql temporário ==========
            @unlink($sqlFile);

            // ========== PASSO 4: Enviar download e apagar ZIP depois ==========
            $downloadName = "backup_sisdp_{$timestamp}.zip";

            Log::info("Backup realizado com sucesso por {$user->nome} ({$user->username}). Arquivo: {$downloadName}");

            return response()->download($zipFile, $downloadName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Limpar arquivos em caso de erro
            @unlink($sqlFile);
            @unlink($zipFile);

            Log::error("Erro no backup: " . $e->getMessage());
            return back()->with('error', 'Erro ao gerar backup: ' . $e->getMessage());
        }
    }

    /**
     * Tenta encontrar o caminho do mysqldump no sistema.
     */
    private function findMysqldump(): string
    {
        // Caminhos comuns onde o mysqldump pode estar
        $possiblePaths = [
            'mysqldump',                                    // Se está no PATH
            '/usr/bin/mysqldump',                           // Linux padrão
            '/usr/local/bin/mysqldump',                     // Linux alternativo
            '/usr/local/mysql/bin/mysqldump',               // macOS
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',          // XAMPP Windows
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe', // Laragon
        ];

        foreach ($possiblePaths as $path) {
            // Testa com --version para ver se existe
            $testOutput = [];
            $testCode = 0;
            @exec("{$path} --version 2>&1", $testOutput, $testCode);
            
            if ($testCode === 0 && !empty($testOutput)) {
                return $path;
            }
        }

        // Fallback: tenta o comando direto (pode funcionar se está no PATH)
        return 'mysqldump';
    }
}
