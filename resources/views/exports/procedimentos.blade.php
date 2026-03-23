<table>
    <thead>
        <tr>
            <th colspan="5" style="text-align: center; font-weight: bold; font-size: 16px; height: 30px; vertical-align: middle;">
                RELATÓRIO DE PROCEDIMENTOS - SISDP
            </th>
        </tr>
        <tr>
            <th colspan="5" style="text-align: center; height: 25px; vertical-align: middle;">
                Gerado em: {{ now()->format('d/m/Y H:i:s') }}
            </th>
        </tr>
        <tr>
            <th style="background: #2c3e50; color: white; font-weight: bold; width: 100px; border: 1px solid #000000;">Data</th>
            <th style="background: #2c3e50; color: white; font-weight: bold; width: 120px; border: 1px solid #000000;">BOE</th>
            <th style="background: #2c3e50; color: white; font-weight: bold; width: 120px; border: 1px solid #000000;">IP</th>
            <th style="background: #2c3e50; color: white; font-weight: bold; width: 300px; border: 1px solid #000000;">Natureza</th>
            <th style="background: #2c3e50; color: white; font-weight: bold; width: 150px; border: 1px solid #000000;">Status</th>
        </tr>
    </thead>
    <tbody>
    @foreach($registros as $row)
        <tr>
            <td style="border: 1px solid #cccccc; vertical-align: middle;">{{ \Carbon\Carbon::parse($row->data)->format('d/m/Y') }}</td>
            <td style="border: 1px solid #cccccc; vertical-align: middle;">{{ $row->BOE ?? '-' }}</td>
            <td style="border: 1px solid #cccccc; vertical-align: middle;">{{ $row->IP ?? '-' }}</td>
            <td style="border: 1px solid #cccccc; vertical-align: middle;">{{ $row->incidencia_penal ?? '-' }}</td>
            <td style="border: 1px solid #cccccc; vertical-align: middle;">{{ $row->status ?? '-' }}</td>
        </tr>
    @endforeach
    @if($registros->isEmpty())
        <tr>
            <td colspan="6" style="text-align: center;">Nenhum registro encontrado para os filtros selecionados.</td>
        </tr>
    @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align: right; font-weight: bold; background: #f0f0f0;">
                Total de Registros: {{ $registros->count() }}
            </td>
        </tr>
    </tfoot>
</table>
