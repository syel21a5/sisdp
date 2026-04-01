@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h2 class="mb-0 text-white"><i class="bi bi-diagram-3-fill text-warning me-2"></i> Auditoria: Procedimentos sem Chips (Legado)</h2>
        <p class="text-secondary mt-1">Identifique Boletins de Ocorrência antigos que ainda não possuem os vínculos detalhados de envolvidos no sistema inteligente.</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('administrativo.auditoria') }}" class="btn btn-outline-light">
            <i class="bi bi-arrow-left"></i> Voltar para Auditoria
        </a>
    </div>
</div>

<div class="card bg-dark text-white border-secondary mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('administrativo.auditoria_chips') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="usuario_id" class="form-label text-light">Filtrar por Usuário (Responsável originário)</label>
                <select name="usuario_id" id="usuario_id" class="form-select bg-dark text-white border-secondary">
                    <option value="">TODOS</option>
                    @foreach($usuarios as $user)
                        <option value="{{ $user->id }}" {{ request('usuario_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filtrar Procedimentos Órfãos
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card bg-dark text-white border-secondary shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover table-striped mb-0">
                <thead class="table-secondary text-dark">
                    <tr>
                        <th>Data Registro SisDP</th>
                        <th>Nº BOE</th>
                        <th>Nº IP</th>
                        <th>Incidência Penal</th>
                        <th>Responsável Original</th>
                        <th class="text-center">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $reg)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($reg->created_at)->format('d/m/Y H:i') }}</td>
                            <td><span class="badge bg-secondary">{{ $reg->boe ?: 'N/A' }}</span></td>
                            <td>{{ $reg->ip ?: '-' }}</td>
                            <td>{{ $reg->incidencia_penal ?: 'Não informada' }}</td>
                            <td>{{ $reg->responsavel ?: 'Desconhecido' }}</td>
                            <td class="text-center">
                                <!-- Botão abre no módulo de INÍCIO, mandando pesquisar pelo ID local ou diretamente editar o BOE -->
                                <!-- Assumindo que a maneira de corrigir o chip é abrir o BOE no Módulo IP-APFD. -->
                                <a href="{{ url('/ip-apfd?boe=') }}{{ $reg->boe }}" target="_blank" class="btn btn-sm btn-warning" title="Abrir no Módulo Inteligente para inserir Chips">
                                    <i class="bi bi-pencil-square"></i> Resolver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 bg-dark text-muted">
                                <i class="bi bi-check-circle fs-3 d-block mb-2 text-success"></i>
                                Nenhum procedimento sem chips foi encontrado para este filtro! Todos os BOEs estão organizados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(count($registros) == 300)
        <div class="card-footer bg-dark border-top border-secondary text-center text-muted">
            <small>Exibindo no máximo os 300 primeiros registros por questões de performance.</small>
        </div>
        @endif
    </div>
</div>
@endsection
