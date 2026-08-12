<?php

namespace App\Exports;

use App\Models\Administrativo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Facades\DB;

class AdministrativoPessoasExport implements FromView, ShouldAutoSize, WithTitle
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

        $limite = isset($this->filtros['limite']) ? (int)$this->filtros['limite'] : 50;
        $resultados = collect();

        if (!isset($this->filtros['tipo']) || $this->filtros['tipo'] === 'vitima' || $this->filtros['tipo'] === 'ambos') {
            $vitimas = DB::table('administrativo')
                ->join('administrativo_pessoas as ap', 'ap.administrativo_id', '=', 'administrativo.id')
                ->where('ap.papel', 'VITIMA')
                ->when(isset($this->filtros['periodo']) && $this->filtros['periodo'] !== 'todos', function($qq){
                    $inicio = $this->getDataInicioPorPeriodo($this->filtros['periodo']);
                    if ($inicio) { $qq->where('administrativo.data_cadastro', '>=', $inicio->format('Y-m-d')); }
                })
                ->selectRaw('ap.nome as nome, COUNT(*) as total_ocorrencias, COUNT(DISTINCT administrativo.crime) as crimes_diferentes, GROUP_CONCAT(DISTINCT administrativo.crime) as crimes, MIN(administrativo.data_cadastro) as primeira_ocorrencia, MAX(administrativo.data_cadastro) as ultima_ocorrencia')
                ->groupBy('ap.nome')
                ->orderBy('total_ocorrencias', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($i){ $i->tipo = 'Vítima'; $i->crimes_array = $i->crimes ? explode(',', $i->crimes) : []; return $i; });
            $resultados = $resultados->merge($vitimas);
        }

        if (!isset($this->filtros['tipo']) || $this->filtros['tipo'] === 'autor' || $this->filtros['tipo'] === 'ambos') {
            $autores = DB::table('administrativo')
                ->join('administrativo_pessoas as ap', 'ap.administrativo_id', '=', 'administrativo.id')
                ->where('ap.papel', 'AUTOR')
                ->when(isset($this->filtros['periodo']) && $this->filtros['periodo'] !== 'todos', function($qq){
                    $inicio = $this->getDataInicioPorPeriodo($this->filtros['periodo']);
                    if ($inicio) { $qq->where('administrativo.data_cadastro', '>=', $inicio->format('Y-m-d')); }
                })
                ->selectRaw('ap.nome as nome, COUNT(*) as total_ocorrencias, COUNT(DISTINCT administrativo.crime) as crimes_diferentes, GROUP_CONCAT(DISTINCT administrativo.crime) as crimes, MIN(administrativo.data_cadastro) as primeira_ocorrencia, MAX(administrativo.data_cadastro) as ultima_ocorrencia')
                ->groupBy('ap.nome')
                ->orderBy('total_ocorrencias', 'desc')
                ->limit($limite)
                ->get()
                ->map(function($i){ $i->tipo = 'Autor'; $i->crimes_array = $i->crimes ? explode(',', $i->crimes) : []; return $i; });
            $resultados = $resultados->merge($autores);
        }

        $resultados = $resultados->sortByDesc('total_ocorrencias')->values();

        return view('exports.administrativo-pessoas', [
            'resultados' => $resultados,
            'filtros' => $this->filtros
        ]);
    }

    public function title(): string
    {
        return 'Relatorio_Pessoas';
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