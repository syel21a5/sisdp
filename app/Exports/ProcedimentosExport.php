<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class ProcedimentosExport implements FromView, ShouldAutoSize, WithTitle, WithColumnWidths
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function view(): View
    {
        $query = DB::table('cadprincipal');

        // Aplicar filtros (Mesma lógica do Controller)
        if (!empty($this->filtros['ano'])) {
            $query->whereYear('data', $this->filtros['ano']);
        }

        if (!empty($this->filtros['mes'])) {
            $query->whereMonth('data', $this->filtros['mes']);
        }

        if (!empty($this->filtros['status'])) {
            $query->where('status', $this->filtros['status']);
        }

        $registros = $query->orderBy('data', 'desc')->get();

        return view('exports.procedimentos', [
            'registros' => $registros
        ]);
    }

    public function title(): string
    {
        return 'Relatório Procedimentos';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Data
            'B' => 20, // BOE
            'C' => 15, // IP
            'D' => 50, // Natureza
            'E' => 25, // Status
        ];
    }
}
