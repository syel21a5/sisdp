<table>
    <thead>
        <tr>
            <th colspan="9" style="text-align: center; font-weight: bold; font-size: 16px;">
                RELATÓRIO GERAL - SISDP MÓDULO ADMINISTRATIVO
            </th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center;">
                Período: {{ $filtros['periodo'] ?? 'Todos' }} |
                Gerado em: {{ now()->format('d/m/Y H:i:s') }}
            </th>
        </tr>
        <tr>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Data Cadastro</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">BOE</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">IP</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Vítima</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Autor</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Crime</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Tipificação</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Apreensão</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Cartório</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $registro)
        <tr>
            <td>{{ \Carbon\Carbon::parse($registro->data_cadastro)->format('d/m/Y') }}</td>
            <td>{{ $registro->boe ?? '-' }}</td>
            <td>{{ $registro->ip ?? '-' }}</td>
            <td>{{ $registro->vitima ?? '-' }}</td>
            <td>{{ $registro->autor ?? '-' }}</td>
            <td>{{ $registro->crime ?? '-' }}</td>
            <td>{{ $registro->tipificacao ?? '-' }}</td>
            <td>{{ $registro->apreensao ?? '-' }}</td>
            <td>{{ $registro->cartorio ?? '-' }}</td>
        </tr>
        @endforeach
        @if($registros->count() === 0)
        <tr>
            <td colspan="9" style="text-align: center;">Nenhum registro encontrado</td>
        </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="9" style="text-align: right; font-weight: bold;">
                Total de Registros: {{ $registros->count() }}
            </td>
        </tr>
    </tfoot>
</table>

