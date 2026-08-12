<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class LimparCacheBoe extends Command
{
    /**
     * Nome e assinatura do comando.
     * Uso: php artisan boe:limpar-cache
     * Opção --dias=90 para definir a idade mínima dos arquivos (padrão: 90 dias = ~3 meses)
     */
    protected $signature = 'boe:limpar-cache {--dias=90 : Idade mínima em dias para exclusão}';

    protected $description = 'Remove arquivos de cache/hash de BOEs mais antigos que o período especificado (padrão: 90 dias)';

    public function handle()
    {
        $dias = (int) $this->option('dias');
        $cacheDir = storage_path('app/boe_cache');

        if (!is_dir($cacheDir)) {
            $this->info('Pasta de cache não encontrada. Nada a limpar.');
            return 0;
        }

        $arquivos = glob($cacheDir . '/*.json');
        $totalArquivos = count($arquivos);
        $removidos = 0;
        $limiteTimestamp = now()->subDays($dias)->timestamp;

        foreach ($arquivos as $arquivo) {
            $modificadoEm = filemtime($arquivo);

            if ($modificadoEm < $limiteTimestamp) {
                $nomeArquivo = basename($arquivo);
                @unlink($arquivo);
                $removidos++;
                $this->line("  <fg=red>✗</> Removido: {$nomeArquivo} (modificado há " . round((time() - $modificadoEm) / 86400) . " dias)");
            }
        }

        $mantidos = $totalArquivos - $removidos;

        $this->newLine();
        $this->info("═══════════════════════════════════════");
        $this->info("  Limpeza de Cache de BOEs concluída!");
        $this->info("  Total de arquivos: {$totalArquivos}");
        $this->info("  Removidos (>{$dias} dias): {$removidos}");
        $this->info("  Mantidos (<{$dias} dias): {$mantidos}");
        $this->info("═══════════════════════════════════════");

        if ($removidos > 0) {
            Log::info("Cache BOE: {$removidos} arquivos antigos removidos (>{$dias} dias). {$mantidos} mantidos.");
        }

        return 0;
    }
}
