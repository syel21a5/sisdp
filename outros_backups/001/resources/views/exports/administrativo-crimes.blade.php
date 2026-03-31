<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-weight: bold; font-size: 16px;">
                ANÁLISE DE CRIMES - SISDP MÓDULO ADMINISTRATIVO
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center;">
                Agrupado por: {{ $filtros['agrupar_por'] === 'tipificacao' ? 'Tipificação' : 'Crime' }} |
                Período: {{ $filtros['periodo'] ?? 'Todos' }} |
                Gerado em: {{ now()->format('d/m/Y H:i:s') }}
            </th>
        </tr>
        <tr>
            <th style="background: #c0392b; color: white; font-weight: bold;">
                {{ $filtros['agrupar_por'] === 'tipificacao' ? 'Tipificação' : 'Crime' }}
            </th>
            <th style="background: #c0392b; color: white; font-weight: bold;">Total Ocorrências</th>
            <th style="background: #c0392b; color: white; font-weight: bold;">Vítimas Únicas</th>
            <th style="background: #c0392b; color: white; font-weight: bold;">Autores Únicos</th>
            <th style="background: #c0392b; color: white; font-weight: bold;">Primeira Ocorrência</th>
            <th style="background: #c0392b; color: white; font-weight: bold;">Última Ocorrência</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultados as $resultado)
        <tr>
            <td>{{ $resultado->{$campoAgrupamento} ?? 'Não informado' }}</td>
            <td>{{ $resultado->total }}</td>
            <td>{{ $resultado->vitimas_unicas }}</td>
            <td>{{ $resultado->autores_unicos }}</td>
            <td>{{ $resultado->primeira_ocorrencia ? \Carbon\Carbon::parse($resultado->primeira_ocorrencia)->format('d/m/Y') : '-' }}</td>
            <td>{{ $resultado->ultima_ocorrencia ? \Carbon\Carbon::parse($resultado->ultima_ocorrencia)->format('d/m/Y') : '-' }}</td>
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
            <td style="font-weight: bold;">TOTAL GERAL</td>
            <td style="font-weight: bold;">{{ $resultados->sum('total') }}</td>
            <td style="font-weight: bold;">{{ $resultados->sum('vitimas_unicas') }}</td>
            <td style="font-weight: bold;">{{ $resultados->sum('autores_unicos') }}</td>
            <td colspan="2" style="font-weight: bold;">Tipos Diferentes: {{ $resultados->count() }}</td>
        </tr>
    </tfoot>
</table>
