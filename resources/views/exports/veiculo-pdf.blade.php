<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Veículos Apreendidos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            background: #fff;
        }
        .header {
            background: linear-gradient(135deg, #1a56db 0%, #1e40af 100%);
            color: white;
            padding: 18px 24px;
            margin-bottom: 16px;
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .header h1 { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 10px; opacity: 0.85; margin-top: 3px; }
        .header .meta { text-align: right; font-size: 9px; opacity: 0.9; line-height: 1.6; }

        .summary-wrapper {
            margin: 0 14px 16px 14px;
        }
        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
        }
        .summary-table td {
            width: 25%;
            border-radius: 6px;
            padding: 12px 10px;
            text-align: center;
        }
        .card-danger  { background: #fdf2f2; border: 1px solid #f8b4b4; border-top: 3px solid #e02424; }
        .card-success { background: #f3faf7; border: 1px solid #b4e4d0; border-top: 3px solid #057a55; }
        .card-warning { background: #fffcf0; border: 1px solid #fce96a; border-top: 3px solid #d08112; }
        .card-gray    { background: #f9fafb; border: 1px solid #e5e7eb; border-top: 3px solid #4b5563; }
        .summary-table .number { font-size: 22px; font-weight: 700; color: #1f2937; }
        .summary-table .label { font-size: 9px; color: #6b7280; margin-top: 4px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold; }

        .filter-info {
            margin: 0 24px 12px 24px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 9.5px;
            color: #1e40af;
        }
        .filter-info strong { font-weight: 700; }

        .table-wrapper { margin: 0 24px 24px 24px; }
        .table-title {
            font-size: 11px; font-weight: 700; color: #1f2937;
            margin-bottom: 6px; padding-bottom: 4px;
            border-bottom: 2px solid #1a56db;
        }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: #1a56db; color: white; }
        thead th { padding: 7px 6px; text-align: left; font-weight: 700; letter-spacing: 0.3px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd)  { background: #ffffff; }
        tbody td { padding: 6px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
        .status-badge {
            display: inline-block; padding: 2px 7px; border-radius: 10px;
            font-size: 8px; font-weight: 700; text-transform: uppercase;
        }
        .status-apreendido { background: #fee2e2; color: #b91c1c; }
        .status-devolvido  { background: #dcfce7; color: #15803d; }
        .status-pericia    { background: #fef9c3; color: #854d0e; }
        .status-outros     { background: #f3f4f6; color: #374151; }

        .footer {
            margin: 0 24px; padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: center; font-size: 8.5px; color: #9ca3af;
        }
        .no-records { text-align: center; padding: 20px; color: #6b7280; font-style: italic; }
    </style>
</head>
<body>

<div class="header">
    <div class="header-top">
        <div>
            <h1>🚗 SISDP — Módulo IP | APFD</h1>
            <div class="subtitle">Relatório de Veículos Apreendidos</div>
        </div>
        <div class="meta">
            Gerado em: {{ $geradoEm }}<br>
            Usuário: {{ $usuario }}<br>
            Período: {{ $dataInicio }} a {{ $dataFim }}
            @if($status)<br>Status: {{ $status }}@endif
        </div>
    </div>
</div>

<div class="summary-wrapper">
    <table class="summary-table">
        <tr>
            <td class="card-danger">
                <div class="number">{{ $registros->where('status', 'APREENDIDO')->count() }}</div>
                <div class="label">Apreendidos</div>
            </td>
            <td class="card-success">
                <div class="number">{{ $registros->where('status', 'DEVOLVIDO')->count() }}</div>
                <div class="label">Devolvidos</div>
            </td>
            <td class="card-warning">
                <div class="number">{{ $registros->where('status', 'EM PERÍCIA')->count() }}</div>
                <div class="label">Em Perícia</div>
            </td>
            <td class="card-gray">
                <div class="number">{{ $registros->count() }}</div>
                <div class="label">Total</div>
            </td>
        </tr>
    </table>
</div>

<div class="filter-info">
    📅 <strong>Período consultado:</strong> {{ $dataInicio }} a {{ $dataFim }}
    @if($status) &nbsp;|&nbsp; 🔖 <strong>Status:</strong> {{ $status }} @endif
    &nbsp;|&nbsp; 📋 <strong>Total de registros:</strong> {{ $registros->count() }}
</div>

<div class="table-wrapper">
    <div class="table-title">Listagem Detalhada de Veículos</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Data</th>
                <th>BOE</th>
                <th>SEI</th>
                <th>IP</th>
                <th>Pessoa</th>
                <th>Veículo</th>
                <th>Placa</th>
                <th>Chassi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @if(count($registros) > 0)
                @foreach($registros as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->data }}</td>
                    <td><strong>{{ $r->boe }}</strong></td>
                    <td>{{ $r->sei }}</td>
                    <td>{{ $r->ip }}</td>
                    <td>{{ $r->pessoa }}</td>
                    <td>{{ $r->veiculo }}</td>
                    <td style="font-weight: bold;">{{ $r->placa }}</td>
                    <td style="font-size: 8px;">{{ $r->chassi }}</td>
                    <td>
                        @php
                            $st = strtoupper($r->status ?? '');
                            $cls = 'status-outros';
                            if ($st == 'APREENDIDO') $cls = 'status-apreendido';
                            elseif ($st == 'DEVOLVIDO') $cls = 'status-devolvido';
                            elseif (strpos($st, 'PERÍCIA') !== false || strpos($st, 'PERICIA') !== false) $cls = 'status-pericia';
                        @endphp
                        <span class="status-badge {{ $cls }}">{{ $r->status }}</span>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="no-records">Nenhum veículo encontrado no período informado.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="footer">
    SISDP — Sistema Integrado de Segurança &amp; Defesa Pública &nbsp;|&nbsp;
    Documento gerado automaticamente em {{ $geradoEm }} &nbsp;|&nbsp;
    Este relatório é de uso interno e confidencial.
</div>

</body>
</html>

