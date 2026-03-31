<!-- resources/views/exports/administrativo-apreensoes.blade.php -->
<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-weight: bold; font-size: 16px;">
                RELATÓRIO DE APREENSÕES - SISDP MÓDULO ADMINISTRATIVO
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;">
                Período: {{ $filtros['periodo'] ?? 'Todos' }}
                @if(!empty($filtros['tipo_apreensao']))
                    | Tipo: {{ $filtros['tipo_apreensao'] }}
                @endif
                | Gerado em: {{ now()->format('d/m/Y H:i:s') }}
            </th>
        </tr>
        <tr>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Data</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">BOE</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Crime</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Vítima</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Autor</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Apreensão</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultados as $registro)
        <tr>
            <td>{{ \Carbon\Carbon::parse($registro->data_cadastro)->format('d/m/Y') }}</td>
            <td>{{ $registro->boe ?? '-' }}</td>
            <td>{{ $registro->crime ?? '-' }}</td>
            <td>{{ $registro->vitima ?? '-' }}</td>
            <td>{{ $registro->autor ?? '-' }}</td>
            <td>{{ $registro->apreensao ?? '-' }}</td>
        </tr>
        @endforeach
        @if($resultados->count() === 0)
        <tr>
            <td colspan="6" style="text-align: center;">Nenhum dado encontrado</td>
        </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align: right; font-weight: bold;">
                Total de Apreensões: {{ $resultados->count() }}
            </td>
        </tr>
    </tfoot>
</table>