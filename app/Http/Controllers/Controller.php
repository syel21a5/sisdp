<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Atualiza o timestamp updated_at do registro principal (BOE) 
     * para resetar o contador de inatividade (pendências).
     */
    protected function touchProcedure($boe)
    {
        if (empty($boe)) return false;

        return \Illuminate\Support\Facades\DB::table('cadprincipal')
            ->where('BOE', $boe)
            ->update(['updated_at' => now()]);
    }
}

