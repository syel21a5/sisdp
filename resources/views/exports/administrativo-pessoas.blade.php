<!-- resources/views/exports/administrativo-pessoas.blade.php -->
<table>
    <thead>
        <tr>
            <th colspan="7" style="text-align: center; font-weight: bold; font-size: 16px;">
                RELATÓRIO DE PESSOAS - SISDP MÓDULO ADMINISTRATIVO
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">
                Tipo: {{ $filtros['tipo'] ?? 'ambos' }} |
                Período: {{ $filtros['periodo'] ?? 'Todos' }} |
                Limite: {{ $filtros['limite'] ?? 50 }} |
                Gerado em: {{ now()->format('d/m/Y H:i:s') }}
            </th>
        </tr>
        <tr>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Nome</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Tipo</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Total Ocorrências</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Crimes Diferentes</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Primeira Ocorrência</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Última Ocorrência</th>
            <th style="background: #2c3e50; color: white; font-weight: bold;">Crimes Envolvidos</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultados as $item)
        <tr>
            <td>{{ $item->nome }}</td>
            <td>{{ $item->tipo }}</td>
            <td>{{ $item->total_ocorrencias }}</td>
            <td>{{ $item->crimes_diferentes }}</td>
            <td>{{ $item->primeira_ocorrencia ? \Carbon\Carbon::parse($item->primeira_ocorrencia)->format('d/m/Y') : '-' }}</td>
            <td>{{ $item->ultima_ocorrencia ? \Carbon\Carbon::parse($item->ultima_ocorrencia)->format('d/m/Y') : '-' }}</td>
            <td>{{ isset($item->crimes_array) && count($item->crimes_array) ? implode(', ', $item->crimes_array) : '-' }}</td>
        </tr>
        @endforeach
        @if($resultados->count() === 0)
        <tr>
            <td colspan="7" style="text-align: center;">Nenhum dado encontrado</td>
        </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" style="text-align: right; font-weight: bold;">
                Total de Pessoas: {{ $resultados->count() }}
            </td>
        </tr>
    </tfoot>
</table>