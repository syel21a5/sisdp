<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Serviço para registrar mudanças de status (Remetido/Concluído) em arquivo JSON.
 * Evita necessidade de criar colunas no banco de dados.
 * 
 * Formato do log: [
 *   { "id": 123, "boe": "BOE-XXX", "status": "Remetido a Justiça", "data": "2026-04-11", "mes": 4, "ano": 2026 },
 *   ...
 * ]
 */
class StatusLogService
{
    private const LOG_FILE = 'status_log.json';

    /**
     * Status que devem ser rastreados pelo log.
     */
    private const STATUS_RASTREADOS = [
        'Remetido a Justiça',
        'Remetido à Justiça',
        'Concluído',
    ];

    /**
     * Normaliza o status para comparação (remove acentos de "à" -> "a").
     */
    private static function normalizarStatus(string $status): string
    {
        return str_replace('à', 'a', $status);
    }

    /**
     * Registra uma mudança de status no log JSON.
     * Se já existir um registro para o mesmo ID e status, atualiza a data.
     */
    public static function registrar(int $id, string $boe, string $status, ?string $dataStatus = null): void
    {
        // Só registra se for um dos status rastreados
        if (!self::isStatusRastreado($status)) {
            return;
        }

        $logs = self::carregarLogs();
        
        if ($dataStatus) {
            try {
                $agora = \Carbon\Carbon::parse($dataStatus);
            } catch (\Exception $e) {
                $agora = now();
            }
        } else {
            $agora = now();
        }

        $statusNorm = self::normalizarStatus($status);

        // Remove entrada anterior do mesmo ID + status normalizado (para atualizar)
        $logs = array_values(array_filter($logs, function ($entry) use ($id, $statusNorm) {
            return !($entry['id'] === $id && self::normalizarStatus($entry['status']) === $statusNorm);
        }));

        // Adiciona nova entrada
        $logs[] = [
            'id'     => $id,
            'boe'    => $boe,
            'status' => $status,
            'data'   => $agora->format('Y-m-d'),
            'mes'    => (int) $agora->format('m'),
            'ano'    => (int) $agora->format('Y'),
        ];

        self::salvarLogs($logs);
    }

    /**
     * Remove registros de log para um ID específico (usado quando o status muda para outro não-rastreado).
     */
    public static function remover(int $id, string $status): void
    {
        $logs = self::carregarLogs();
        $statusNorm = self::normalizarStatus($status);

        $logs = array_values(array_filter($logs, function ($entry) use ($id, $statusNorm) {
            return !($entry['id'] === $id && self::normalizarStatus($entry['status']) === $statusNorm);
        }));

        self::salvarLogs($logs);
    }

    /**
     * Busca IDs de procedimentos que tiveram mudança de status em um mês/ano específico.
     * 
     * @param string $status Status a filtrar (e.g., "Remetido a Justiça")
     * @param int|null $mes Mês (1-12) ou null para todos
     * @param int|null $ano Ano ou null para todos
     * @return array Lista de IDs dos procedimentos
     */
    public static function buscarIdsPorPeriodo(string $status, ?int $mes = null, ?int $ano = null): array
    {
        $logs = self::carregarLogs();
        $statusNorm = self::normalizarStatus($status);

        $filtrados = array_filter($logs, function ($entry) use ($statusNorm, $mes, $ano) {
            if (self::normalizarStatus($entry['status']) !== $statusNorm) {
                return false;
            }
            if ($mes !== null && $entry['mes'] !== $mes) {
                return false;
            }
            if ($ano !== null && $entry['ano'] !== $ano) {
                return false;
            }
            return true;
        });

        return array_values(array_unique(array_column($filtrados, 'id')));
    }

    /**
     * Verifica se um status deve ser rastreado pelo log.
     */
    public static function isStatusRastreado(string $status): bool
    {
        return in_array($status, self::STATUS_RASTREADOS);
    }

    /**
     * Carrega os logs do arquivo JSON.
     */
    private static function carregarLogs(): array
    {
        if (!Storage::exists(self::LOG_FILE)) {
            return [];
        }

        $conteudo = Storage::get(self::LOG_FILE);
        $dados = json_decode($conteudo, true);

        return is_array($dados) ? $dados : [];
    }

    /**
     * Salva os logs no arquivo JSON.
     */
    private static function salvarLogs(array $logs): void
    {
        Storage::put(self::LOG_FILE, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
