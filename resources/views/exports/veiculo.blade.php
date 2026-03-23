<table>
    <thead>
        <tr>
            <th colspan="10" style="font-size: 16px; font-weight: bold; text-align: center;">
                SISDP — Relatório de Veículos Apreendidos
            </th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 12px;">
                Período: {{ $dataInicio }} a {{ $dataFim }}
                @if($status) | Status: {{ $status }} @endif
                | Gerado em: {{ $geradoEm }} | Usuário: {{ $usuario }}
            </th>
        </tr>
        <tr>
            <th colspan="10"></th>
        </tr>
        <tr>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">#</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">DATA</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">BOE</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">SEI</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">IP</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">PESSOA</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">VEÍCULO</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">PLACA</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">CHASSI</th>
            <th style="background-color: #1a56db; color: white; font-weight: bold;">STATUS</th>
        </tr>
    </thead>
    <tbody>
        @if(count($registros) > 0)
            @foreach($registros as $i => $r)
            <tr style="{{ $i % 2 == 0 ? 'background-color: #f8f9fa;' : '' }}">
                <td>{{ $i + 1 }}</td>
                <td>{{ $r->data }}</td>
                <td>{{ $r->boe }}</td>
                <td>{{ $r->sei }}</td>
                <td>{{ $r->ip }}</td>
                <td>{{ $r->pessoa }}</td>
                <td>{{ $r->veiculo }}</td>
                <td>{{ $r->placa }}</td>
                <td>{{ $r->chassi }}</td>
                <td>{{ $r->status }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="10" style="text-align: center;">Nenhum registro encontrado no período.</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <th colspan="10" style="text-align: right; font-weight: bold;">
                Total de registros: {{ $registros->count() }}
            </th>
        </tr>
    </tfoot>
</table>
