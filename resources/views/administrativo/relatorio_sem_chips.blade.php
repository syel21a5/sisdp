@extends('layouts.app')

@push('styles')
<style>
    /* Padrão de Cores Moderno Dark Mode - SisDP Auditoria */
    body {
        background-color: #12141c !important; /* Fundo super escuro que não dói a vista */
        color: #e2e8f0;
    }

    .main-container {
        padding: 20px;
        max-width: 1050px;
        margin: 0 auto;
    }

    /* Cabeçalho do Módulo */
    .module-header {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border: 1px solid #334155;
        color: #f8fafc;
        padding: 15px 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .module-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
        color: #e2e8f0;
    }

    .btn-light {
        background-color: #334155;
        border-color: #475569;
        color: #f8fafc;
        transition: all 0.2s ease;
    }

    .btn-light:hover {
        background-color: #475569;
        border-color: #64748b;
        color: white;
    }

    /* Cards Dark */
    .content-card {
        background: #1e293b;
        border-radius: 12px;
        border: 1px solid #334155;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        margin-bottom: 20px;
        padding: 20px;
    }

    .content-card-header {
        background: #0f172a;
        color: #94a3b8;
        padding: 15px 20px;
        border-bottom: 1px solid #334155;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Tabela Profissional Dark */
    .table-responsive {
        border-radius: 0 0 12px 12px;
        overflow: hidden;
    }

    .table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(255, 255, 255, 0.02);
        --bs-table-hover-bg: rgba(56, 189, 248, 0.05);
        color: #e2e8f0;
        margin-bottom: 0;
        table-layout: fixed;
    }

    .td-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table > :not(caption) > * > * {
        background-color: transparent !important;
        border-bottom: 1px solid #334155;
        color: #e2e8f0;
        padding: 15px;
        vertical-align: middle;
    }

    .table thead th {
        background-color: #0f172a !important;
        color: #94a3b8 !important;
        border: none;
        border-bottom: 2px solid #334155;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.80rem;
        letter-spacing: 0.5px;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(255, 255, 255, 0.02) !important;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(56, 189, 248, 0.05) !important;
    }

    /* Textos e Badges */
    .text-muted {
        color: #94a3b8 !important;
    }

    .text-primary {
        color: #38bdf8 !important; /* Azul claro moderno */
    }

    .text-dark {
        color: #f8fafc !important; /* Invertendo texto dark para claro */
    }

    .badge-boe {
        background-color: #0f172a;
        color: #38bdf8;
        font-family: 'JetBrains Mono', 'Courier New', monospace;
        font-weight: 700;
        padding: 8px 14px;
        border-radius: 6px;
        border: 1px solid #334155;
        display: inline-block;
    }

    /* Botão de Ação */
    .btn-action {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: white;
        border: none;
        padding: 8px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(2, 132, 199, 0.3);
        color: white;
    }

    /* Filtros Dark */
    .form-label {
        font-weight: 600;
        color: #94a3b8;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .form-select, .form-control {
        background-color: #0f172a;
        border: 1px solid #334155;
        color: #f8fafc;
        border-radius: 8px;
        padding: 10px 15px;
    }

    .form-select:focus, .form-control:focus {
        background-color: #0f172a;
        border-color: #38bdf8;
        color: white;
        box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25);
    }

    /* Botão Atualizar */
    .btn-dark {
        background-color: #0ea5e9;
        border-color: #0284c7;
        color: white;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-dark:hover {
        background-color: #0284c7;
        border-color: #0369a1;
        transform: translateY(-1px);
    }

    /* Personalização da Paginação Padrão no Dark Mode */
    .pagination {
        margin: 0;
    }
    .page-link {
        background-color: #0f172a;
        border-color: #334155;
        color: #94a3b8;
    }
    .page-link:hover {
        background-color: #334155;
        color: white;
        border-color: #475569;
    }
    .page-item.active .page-link {
        background-color: #0284c7;
        border-color: #0284c7;
        color: white;
    }
    .page-item.disabled .page-link {
        background-color: #0f172a;
        border-color: #334155;
        color: #475569;
    }
</style>
@endpush

@section('content')
<div class="main-container">
    
    <!-- Cabeçalho Principal -->
    <div class="module-header">
        <div>
            <i class="bi bi-shield-check fs-4 me-2 text-info"></i>
            <span class="fs-4 fw-bold">SisDP - Auditoria de Procedimentos</span>
            <p class="mb-0 mt-1 text-muted" style="font-size: 0.9rem;">Gerencie os registros pendentes de forma clara e visual.</p>
        </div>
        <a href="{{ route('administrativo.auditoria') }}" class="btn btn-light btn-sm fw-bold px-3 py-2">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Seção de Filtros -->
    <div class="content-card">
        <form method="GET" action="{{ route('administrativo.auditoria_chips') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">FILTRAR POR USUÁRIO</label>
                <select name="usuario_id" class="form-select shadow-sm">
                    <option value="">Todos os usuários</option>
                    @foreach($usuarios as $user)
                        <option value="{{ $user->id }}" {{ request('usuario_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">ANO DO BOE</label>
                <select name="ano" class="form-select shadow-sm">
                    <option value="">Selecione o Ano</option>
                    @php
                        $anos = [
                            '26' => '2026',
                            '25' => '2025',
                            '24' => '2024',
                            '23' => '2023',
                            '22' => '2022',
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
                <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm py-2" style="height: 44px;">
                    <i class="bi bi-funnel me-1"></i> ATUALIZAR LISTA
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Resultados -->
    <div class="content-card p-0" style="border: none;">
        <div class="content-card-header">
            <i class="bi bi-exclamation-triangle ms-1 me-2 text-warning"></i> 
            PROCEDIMENTOS SEM CHIPS DE ENVOLVIDOS PENDENTES
        </div>
        <div class="table-responsive" style="border-left: 1px solid #334155; border-right: 1px solid #334155;">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 20%">DATA REGISTRO</th>
                        <th class="text-center" style="width: 20%">BOE</th>
                        <th class="text-center" style="width: 20%">IP</th>
                        <th class="text-center" style="width: 20%">RESPONSÁVEL</th>
                        <th class="text-center" style="width: 20%">AÇÃO</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $reg)
                        <tr>
                            <td class="text-muted small text-center">
                                {{ \Carbon\Carbon::parse($reg->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-center">
                                <span class="badge-boe">{{ $reg->boe ?: 'N/A' }}</span>
                            </td>
                            <td class="text-center td-truncate" title="{{ $reg->ip }}">
                                <span class="text-primary fw-bold">{{ $reg->ip ?: '-' }}</span>
                            </td>
                            <td class="small text-muted text-center td-truncate" title="{{ $reg->responsavel }}">
                                <i class="bi bi-person-fill me-1"></i>{{ $reg->responsavel ?: 'Desconhecido' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ url('/ip-apfd?abrir_id=') }}{{ $reg->id }}" target="_blank" class="btn btn-action btn-sm">
                                    <i class="bi bi-pencil-square"></i> Resolver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5" style="border-bottom: 1px solid #334155;">
                                <i class="bi bi-check-circle-fill text-success fs-1 mb-3 d-block" style="opacity: 0.8;"></i>
                                <h5 class="text-dark">Tudo em ordem!</h5>
                                <p class="text-muted mb-0">Nenhum procedimento pendente encontrado para os filtros informados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Area da Paginação -->
        @if($registros->hasPages())
        <div class="p-3 d-flex justify-content-between align-items-center" style="background: #1e293b; border: 1px solid #334155; border-top: none; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <div class="text-muted small">
                Exibindo <strong>{{ $registros->firstItem() }}</strong> até <strong>{{ $registros->lastItem() }}</strong> de <strong>{{ $registros->total() }}</strong> registros
            </div>
            <div>
                {{ $registros->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @else
        <div class="p-3 text-center" style="background: #1e293b; border: 1px solid #334155; border-top: none; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
            <small class="text-muted">Mostrando todos os {{ $registros->total() }} registros encontrados.</small>
        </div>
        @endif
    </div>

</div>
@endsection
