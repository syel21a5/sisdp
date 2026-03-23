<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CelularExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    protected $dataInicio;
    protected $dataFim;
    protected $status;
    protected $userId;

    public function __construct($dataInicio, $dataFim, $status = null)
    {
        $this->dataInicio = $dataInicio;
        $this->dataFim    = $dataFim;
        $this->status     = $status;
        $this->userId     = Auth::id();
    }

    public function view(): View
    {
        $query = DB::table('cadcelular');

        if ($this->userId != 4) {
            $query->where('user_id', $this->userId);
        }

        if ($this->dataInicio && $this->dataFim) {
            $query->whereBetween('data', [
                Carbon::parse($this->dataInicio)->format('Y-m-d'),
                Carbon::parse($this->dataFim)->format('Y-m-d'),
            ]);
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        $registros = $query->orderBy('data', 'desc')
            ->get(['id', 'data', 'boe', 'processo', 'ip', 'pessoa', 'telefone', 'imei1', 'imei2', 'status']);

        // Formatar datas para exibição
        $registros->transform(function ($r) {
            $r->data = $r->data ? Carbon::parse($r->data)->format('d/m/Y') : '';
            return $r;
        });

        return view('exports.celular', [
            'registros'   => $registros,
            'dataInicio'  => $this->dataInicio ? Carbon::parse($this->dataInicio)->format('d/m/Y') : 'Início',
            'dataFim'     => $this->dataFim ? Carbon::parse($this->dataFim)->format('d/m/Y') : 'Hoje',
            'status'      => $this->status,
            'geradoEm'    => now()->format('d/m/Y H:i'),
            'usuario'     => Auth::user()->name ?? 'Sistema',
        ]);
    }

    public function title(): string
    {
        return 'Celulares_Apreendidos';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Linha do cabeçalho da tabela (linha 7 aprox.)
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}
