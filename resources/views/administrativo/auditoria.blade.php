@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0 bg-dark text-white">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list-ul me-2"></i>Log de Auditoria do Sistema</h5>
            <span class="badge bg-light text-dark">{{ $logs->total() }} Registros</span>
        </div>
        
        <!-- Filtros de Pesquisa -->
        <div class="card-body bg-secondary bg-opacity-10 border-bottom border-secondary">
            <form action="{{ route('administrativo.auditoria') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Data Específica</label>
                    <input type="date" name="data" class="form-control form-control-sm bg-dark text-white border-secondary" value="{{ request('data') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Usuário</label>
                    <input type="text" name="usuario" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Nome ou username..." value="{{ request('usuario') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Ação</label>
                    <select name="acao" class="form-select form-select-sm bg-dark text-white border-secondary">
                        <option value="">Todas as ações</option>
                        @foreach($acoes as $acao)
                            <option value="{{ $acao }}" {{ request('acao') == $acao ? 'selected' : '' }}>{{ $acao }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <a href="{{ route('administrativo.auditoria') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Limpar
                    </a>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Data/Hora</th>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Detalhes</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td class="ps-3 text-secondary">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $log->username }}</span>
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($log->acao) {
                                        'LOGIN_SUCESSO' => 'bg-success',
                                        'LOGIN_FALHA', 'BLOQUEIO_LOGIN' => 'bg-danger',
                                        'LOGOUT' => 'bg-info',
                                        default => 'bg-primary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $log->acao }}</span>
                            </td>
                            <td class="text-secondary small">{{ $log->detalhe }}</td>
                            <td class="text-light">{{ $log->ip }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-dark border-top border-secondary py-3">
            <div class="d-flex justify-content-center">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .table-dark {
        --bs-table-bg: #1a1a1a;
        --bs-table-hover-bg: #2a2a2a;
        font-size: 0.9rem;
    }
    .card { border-radius: 12px; overflow: hidden; }
    .page-link { background-color: #333; border-color: #444; color: #fff; }
    .page-item.active .page-link { background-color: #0d6efd; border-color: #0d6efd; }
</style>
@endsection
