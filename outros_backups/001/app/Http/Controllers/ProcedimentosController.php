<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ProcedimentosExport;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProcedimentosController extends Controller
{
    public function index()
    {
        // Verifica permissões (mesma lógica do InicioController)
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('relatorios.procedimentos');
    }

    public function buscarDados(Request $request)
    {
        // Filtros
        $ano = $request->input('ano');
        $mes = $request->input('mes');
        $status = $request->input('status');

        $query = DB::table('cadprincipal');

        // Aplicar Filtro de Ano
        if ($ano) {
            $query->whereYear('data', $ano);
        }

        // Aplicar Filtro de Mês
        if ($mes) {
            $query->whereMonth('data', $mes);
        }

        // Aplicar Filtro de Status
        if ($status) {
            $query->where('status', $status);
        }

        // --- SEPARAÇÃO DE QUERIES ---
// 1. Query para Estatísticas e Ranking (Sem Paginação)
        $queryStats = clone $query;

        // Otimização: Selecionar apenas colunas necessárias para agregação
        $dadosStats = $queryStats->select('status', 'incidencia_penal')->get();

        // Contadores
        $total = $dadosStats->count();
        // query separada para garantir contagem correta via banco
        $statsAgrupados = (clone $query)->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();

        $emAndamento = 0;
        $concluidos = 0;
        $parados = 0;
        $remetidos = 0;
        $arquivados = 0;

        foreach ($statsAgrupados as $stat) {
            $st = mb_strtolower($stat->status);

            if (str_contains($st, 'andamento'))
                $emAndamento += $stat->total;
            elseif (str_contains($st, 'conclu'))
                $concluidos += $stat->total;
            elseif (str_contains($st, 'parado') || str_contains($st, 'dilig'))
                $parados += $stat->total;
            elseif (str_contains($st, 'remetido'))
                $remetidos += $stat->total;
            elseif (str_contains($st, 'arquivado'))
                $arquivados += $stat->total;
        }


        // Ranking de Naturezas (Top 5)
        $rankingNaturezas = $dadosStats
            ->groupBy(function ($item) {
                return trim(strtoupper($item->incidencia_penal ?? '')) ?: 'NÃO INFORMADO';
            })
            ->map(function ($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(5)
            ->map(function ($count, $natureza) {
                return ['nome' => $natureza, 'total' => $count];
            })
            ->values();

        // 2. Query para Tabela (Com Paginação)
        $registros = $query
            ->select('id', 'BOE', 'IP', 'data', 'status', 'incidencia_penal', 'prioridade')
            ->orderBy('data', 'desc')
            ->paginate(10); // 10 registros por página

        // Transformar dados da paginação
        $tabela = $registros->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'boe' => $item->BOE ?? '-',
                'ip' => $item->IP ?? '-',
                'data' => $item->data ? Carbon::parse($item->data)->format('d/m/Y') : '-',
                'status' => $item->status ?? 'Não Informado',
                'natureza' => $item->incidencia_penal ?? '-',
                'prioridade' => $item->prioridade ?? '-'
            ];
        });

        return response()->json([
            'success' => true,
            'contadores' => [
                'total' => $total,
                'em_andamento' => $emAndamento,
                'concluidos' => $concluidos,
                'parados' => $parados,
                'remetidos' => $remetidos,
                'arquivados' => $arquivados
            ],
            'ranking' => $rankingNaturezas,
            'registros' => $tabela,
            'paginacao' => [
                'total' => $registros->total(),
                'current_page' => $registros->currentPage(),
                'last_page' => $registros->lastPage(),
                'per_page' => $registros->perPage()
            ]
        ]);
    }
    public function exportar(Request $request)
    {
        $filtros = $request->only(['ano', 'mes', 'status']);
        $formato = $request->input('formato', 'excel');
        $nomeArquivo = 'relatorio_procedimentos_' . date('Y-m-d_His');

        if ($formato === 'excel') {
            return Excel::download(new ProcedimentosExport($filtros), $nomeArquivo . '.xlsx');
        } elseif ($formato === 'pdf') {
            return $this->exportarPdf($filtros, $nomeArquivo);
        }

        return redirect()->back()->with('error', 'Formato inválido');
    }

    private function exportarPdf($filtros, $nomeArquivo)
    {
        $query = DB::table('cadprincipal');

        if (!empty($filtros['ano'])) {
            $query->whereYear('data', $filtros['ano']);
        }
        if (!empty($filtros['mes'])) {
            $query->whereMonth('data', $filtros['mes']);
        }
        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        $registros = $query->orderBy('data', 'desc')->get();

        // Configurar DomPDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Gerar HTML usando a mesma View
        $html = '
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
        }

        table {
            border-collapse: collapse;
            margin-top: 20px;
            margin-left: auto;
            margin-right: auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 9px;
            text-align: center;
            color: #777;
        }
    </style>
</head>

<body>
    <h2>Relatório de Procedimentos - SisDP</h2>
    <p>Gerado em: ' . date('d/m/Y H:i:s') . '</p>
    ' . view('exports.procedimentos', ['registros' => $registros])->render() . '
    <div class="footer">Sistema de Delegacia de Polícia - SisDP</div>
</body>

</html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->stream($nomeArquivo . '.pdf', ['Attachment' => true]);
    }
}