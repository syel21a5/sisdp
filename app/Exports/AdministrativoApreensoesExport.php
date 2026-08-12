<?php

namespace App\Exports;

use App\Models\Administrativo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AdministrativoApreensoesExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function view(): View
    {
        $q = Administrativo::query();

        if (isset($this->filtros['periodo']) && $this->filtros['periodo'] !== 'todos') {
            $inicio = $this->getDataInicioPorPeriodo($this->filtros['periodo']);
            if ($inicio) {
                $q->where('data_cadastro', '>=', $inicio->format('Y-m-d'));
            }
        }

        if (!empty($this->filtros['tipo_apreensao'])) {
            $q->where('apreensao', 'LIKE', "%{$this->filtros['tipo_apreensao']}%");
        }

        $resultados = $q->whereNotNull('apreensao')->where('apreensao', '!=', '')
            ->orderBy('data_cadastro', 'desc')
            ->get(['id','data_cadastro','boe','crime','apreensao']);
        $resultados->load('pessoas');
        $resultados->each(function($r){
            $r->vitima = $r->pessoas->where('papel','VITIMA')->pluck('nome')->implode(', ');
            $r->autor = $r->pessoas->where('papel','AUTOR')->pluck('nome')->implode(', ');
        });

        return view('exports.administrativo-apreensoes', [
            'resultados' => $resultados,
            'filtros' => $this->filtros
        ]);
    }

    public function title(): string
    {
        return 'Relatorio_Apreensoes';
    }

    private function getDataInicioPorPeriodo($periodo)
    {
        switch ($periodo) {
            case 'hoje': return now()->today();
            case 'semana': return now()->startOfWeek();
            case 'mes': return now()->startOfMonth();
            case 'ano': return now()->startOfYear();
            default: return null;
        }
    }
}