<?php

namespace App\Exports;

use App\Models\Administrativo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AdministrativoGeralExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function view(): View
    {
        $query = Administrativo::query();

        // Aplicar filtros
        if (isset($this->filtros['periodo']) && $this->filtros['periodo'] !== 'todos') {
            $dataInicio = $this->getDataInicioPorPeriodo($this->filtros['periodo']);
            $dataFim = now();

            if (isset($this->filtros['data_inicio']) && $this->filtros['data_inicio']) {
                $dataInicio = $this->filtros['data_inicio'];
            }
            if (isset($this->filtros['data_fim']) && $this->filtros['data_fim']) {
                $dataFim = $this->filtros['data_fim'];
            }

            $query->whereBetween('data_cadastro', [$dataInicio, $dataFim]);
        }

        if (isset($this->filtros['crime']) && $this->filtros['crime']) {
            $query->where('crime', 'LIKE', "%{$this->filtros['crime']}%");
        }

        $registros = $query->orderBy('data_cadastro', 'desc')->get();
        $registros->load('pessoas');
        $registros->each(function($r){
            $r->vitima = $r->pessoas->where('papel','VITIMA')->pluck('nome')->implode(', ');
            $r->autor = $r->pessoas->where('papel','AUTOR')->pluck('nome')->implode(', ');
        });

        return view('exports.administrativo-geral', [
            'registros' => $registros,
            'filtros' => $this->filtros
        ]);
    }

    public function title(): string
    {
        return 'Relatório_Geral';
    }

    private function getDataInicioPorPeriodo($periodo)
    {
        return match($periodo) {
            'hoje' => now()->today(),
            'semana' => now()->startOfWeek(),
            'mes' => now()->startOfMonth(),
            'ano' => now()->startOfYear(),
            default => now()->subYears(10) // Período longo para "todos"
        };
    }
}
