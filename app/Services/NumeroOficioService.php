<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NumeroOficioService
{
    /**
     * Gera o próximo número de ofício no formato 0167.000001.AAAA
     * Usa transação + lockForUpdate para evitar duplicidade em acessos simultâneos.
     */
    public function gerarProximo(): string
    {
        $ano = Carbon::now()->year;

        // Por enquanto deixamos fixo. Depois podemos mover para .env
        $delegacia = '0167';

        return DB::transaction(function () use ($ano, $delegacia) {
            // Bloqueia a linha do ano atual até o fim da transação
            $row = DB::table('sequencias_oficio')
                ->where('ano', $ano)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                // Se ainda não existe linha para o ano, começa em 1
                $proximo = 1;
                DB::table('sequencias_oficio')->insert([
                    'ano' => $ano,
                    'ultimo_numero' => $proximo,
                ]);
            } else {
                // Incrementa com segurança
                $proximo = $row->ultimo_numero + 1;
                DB::table('sequencias_oficio')
                    ->where('ano', $ano)
                    ->update(['ultimo_numero' => $proximo]);
            }

            // Formata com 6 dígitos
            $seq = str_pad((string) $proximo, 6, '0', STR_PAD_LEFT);

            return "{$delegacia}.{$seq}.{$ano}";
        });
    }
}
