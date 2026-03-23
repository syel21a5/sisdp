<?php

namespace App\Exports;

use App\Models\Administrativo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class AdministrativoCrimesExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function view(): View
    {
        $query = Administrativo::query();

        // Aplicar filtro de período
        if (isset($this->filtros['periodo']) && $this->filtros['periodo'] !== 'todos') {
            $dataInicio = $this->getDataInicioPorPeriodo($this->filtros['periodo']);
            $query->where('data_cadastro', '>=', $dataInicio);
        }

        // Agrupar por tipo de crime
        $campoAgrupamento = $this->filtros['agrupar_por'] === 'tipificacao' ? 'tipificacao' : 'crime';

        $resultados = $query->leftJoin('administrativo_pessoas as ap', 'ap.administrativo_id', '=', 'administrativo.id')
            ->selectRaw("{$campoAgrupamento} as agrupamento,
                COUNT(DISTINCT administrativo.id) as total,
                COUNT(DISTINCT CASE WHEN ap.papel = 'VITIMA' THEN ap.nome END) as vitimas_unicas,
                COUNT(DISTINCT CASE WHEN ap.papel = 'AUTOR' THEN ap.nome END) as autores_unicos,
                MIN(administrativo.data_cadastro) as primeira_ocorrencia,
                MAX(administrativo.data_cadastro) as ultima_ocorrencia")
            ->whereNotNull($campoAgrupamento)
            ->where($campoAgrupamento, '!=', '')
            ->groupBy('agrupamento')
            ->orderBy('total', 'desc')
            ->get();

        return view('exports.administrativo-crimes', [
            'resultados' => $resultados,
            'filtros' => $this->filtros,
            'campoAgrupamento' => 'agrupamento'
        ]);
    }

    public function title(): string
    {
        return 'Análise_Crimes';
    }

    private function getDataInicioPorPeriodo($periodo)
    {
        return match($periodo) {
            'hoje' => now()->today(),
            'semana' => now()->startOfWeek(),
            'mes' => now()->startOfMonth(),
            'ano' => now()->startOfYear(),
            default => null
        };
    }
}
