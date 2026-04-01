@extends('layouts.app')

@push('styles')
<style>
    :root {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.1);
        --accent-color: #f59e0b;
        --accent-hover: #d97706;
    }

    body {
        background-color: #0f172a;
    }

    .premium-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        padding: 2rem;
        border-radius: 16px;
        border: 1px solid var(--glass-border);
        margin-bottom: 2rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
    }

    .premium-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .filter-section {
        background: rgba(15, 23, 42, 0.5);
        padding: 1.5rem;
        border-bottom: 1px solid var(--glass-border);
    }

    .table-container {
        padding: 0;
    }

    .table {
        margin-bottom: 0;
        color: #e2e8f0;
    }

    .table thead th {
        background: rgba(51, 65, 85, 0.8);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        font-weight: 700;
        padding: 1rem;
        border-bottom: 2px solid var(--glass-border);
        color: #94a3b8;
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.03);
    }

    .badge-boe {
        background: rgba(245, 158, 11, 0.1);
        color: var(--accent-color);
        border: 1px solid rgba(245, 158, 11, 0.2);
        padding: 0.5rem 0.75rem;
        font-family: 'Monaco', 'Consolas', monospace;
        font-weight: 600;
        border-radius: 6px;
    }

    .btn-resolve {
        background: var(--accent-color);
        border: none;
        color: #fff;
        font-weight: 600;
        transition: all 0.2s ease;
        padding: 0.5rem 1rem;
        border-radius: 8px;
    }

    .btn-resolve:hover {
        background: var(--accent-hover);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        color: #fff;
    }

    .empty-icon {
        font-size: 3rem;
        opacity: 0.3;
        margin-bottom: 1rem;
    }

    .form-select, .form-control {
        background-color: rgba(15, 23, 42, 0.8);
        border: 1px solid var(--glass-border);
        color: #fff;
    }

    .form-select:focus {
        background-color: #1e293b;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
        color: #fff;
    }

    .header-icon {
        background: rgba(245, 158, 11, 0.1);
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: var(--accent-color);
        font-size: 2rem;
        margin-right: 1.5rem;
    }

    @media (max-width: 768px) {
        .header-section {
            flex-direction: column;
            text-align: center;
        }
        .header-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="premium-header d-flex align-items-center header-section">
    <div class="header-icon">
        <i class="bi bi-diagram-3-fill"></i>
    </div>
    <div class="flex-grow-1">
        <h1 class="h3 mb-1 text-white fw-bold">Auditoria de Procedimentos Legado</h1>
        <p class="text-secondary mb-0">Listagem de registros que possuem BOE mas ainda não foram detalhados com Chips de Envolvidos.</p>
    </div>
    <div class="ms-lg-auto mt-3 mt-lg-0">
        <a href="{{ route('administrativo.auditoria') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Voltar
        </a>
    </div>
</div>

<div class="premium-card">
    <div class="filter-section">
        <form method="GET" action="{{ route('administrativo.auditoria_chips') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-secondary small fw-bold">USUÁRIO RESPONSÁVEL</label>
                <select name="usuario_id" class="form-select">
                    <option value="">Todos os usuários</option>
                    @foreach($usuarios as $user)
                        <option value="{{ $user->id }}" {{ request('usuario_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-bold">ANO DO BOE</label>
                <select name="ano" class="form-select">
                    <option value="">Qualquer ano</option>
                    @php
                        $anos = [
                            '26' => '2026',
                            '25' => '2025',
                            '24' => '2024',
                            '23' => '2023',
                            '22' => '2022',
                            '21' => '2021',
                        ];
                    @endphp
                    @foreach($anos as $val => $label)
                        <option value="{{ $val }}" {{ request('ano') == $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-resolve w-100 py-2">
                    <i class="bi bi-search me-2"></i> Atualizar Auditoria
                </button>
            </div>
        </form>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>DATA SISDP</th>
                        <th>Nº BOE</th>
                        <th>Nº IP</th>
                        <th>INCIDÊNCIA PENAL</th>
                        <th>RESPONSÁVEL ORIGINAL</th>
                        <th class="text-end">AÇÃO</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $reg)
                        <tr>
                            <td>
                                <div class="fw-bold text-white">{{ \Carbon\Carbon::parse($reg->created_at)->format('d/m/Y') }}</div>
                                <div class="small text-secondary">{{ \Carbon\Carbon::parse($reg->created_at)->format('H:i') }}</div>
                            </td>
                            <td>
                                <span class="badge-boe">{{ $reg->boe ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="text-info small">{{ $reg->ip ?: 'Sem IP' }}</div>
                            </td>
                            <td style="max-width: 250px;">
                                <div class="text-truncate" title="{{ $reg->incidencia_penal }}">
                                    {{ $reg->incidencia_penal ?: 'Não informada' }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary rounded-circle me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem;">
                                        {{ substr($reg->responsavel, 0, 1) }}
                                    </div>
                                    <span class="small">{{ $reg->responsavel ?: 'Desconhecido' }}</span>
                                </div>
                            </td>
                            <td class="text-end">
                                <a href="{{ url('/ip-apfd?boe=') }}{{ $reg->boe }}" target="_blank" class="btn btn-resolve btn-sm">
                                    <i class="bi bi-pencil-square"></i> Resolver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="empty-icon text-success">
                                    <i class="bi bi-check2-all"></i>
                                </div>
                                <h5 class="text-white">Tudo em ordem!</h5>
                                <p class="text-secondary mb-0">Nenhum procedimento pendente de chips foi encontrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(count($registros) >= 300)
        <div class="p-3 bg-dark text-center border-top border-secondary">
            <span class="text-secondary small">Limitação de performance: exibindo os primeiros 300 registros.</span>
        </div>
        @endif
    </div>
</div>
@endsection
