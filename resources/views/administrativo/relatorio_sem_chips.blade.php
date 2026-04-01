@extends('layouts.app')

@push('styles')
<style>
    /* Padrão de Cores SisDP */
    body {
        background-color: #f4f7f6 !important; /* Cinza claro de fundo */
        color: #333;
    }

    .main-container {
        padding: 20px;
    }

    /* Cabeçalho do Módulo */
    .module-header {
        background-color: #0d6efd; /* Azul Bootstrap Original */
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        margin-bottom: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .module-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    /* Cards Brancos */
    .content-card {
        background: white;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 20px;
        padding: 20px;
    }

    /* Tabela Profissional */
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
    }

    .table thead th {
        background-color: #0d6efd !important;
        color: white !important;
        border: none;
        padding: 12px 15px;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
    }

    .table tbody td {
        padding: 12px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
        color: #444;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #fcfcfc;
    }

    /* Badges e Botões Neutros */
    .badge-boe {
        background-color: #e9ecef;
        color: #495057;
        font-family: 'Courier New', monospace;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }

    .btn-action {
        background-color: #0d6efd;
        color: white;
        border: none;
        padding: 6px 16px;
        border-radius: 6px;
        font-weight: 500;
        transition: background 0.3s;
    }

    .btn-action:hover {
        background-color: #0b5ed7;
        color: white;
    }

    /* Filtros */
    .form-label {
        font-weight: 600;
        color: #555;
        font-size: 0.9rem;
    }

    .form-select, .form-control {
        border: 1px solid #ced4da;
    }

    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
</style>
@endpush

@section('content')
<div class="main-container">
    
    <!-- Cabeçalho Principal -->
    <div class="module-header shadow-sm">
        <div>
            <i class="bi bi-shield-check fs-4 me-2"></i>
            <span class="fs-4 fw-bold">SisDP - Auditoria de Procedimentos</span>
        </div>
        <a href="{{ route('administrativo.auditoria') }}" class="btn btn-light btn-sm fw-bold">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <!-- Seção de Filtros -->
    <div class="content-card shadow-sm">
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
                <button type="submit" class="btn btn-dark w-100 fw-bold shadow-sm py-2">
                    <i class="bi bi-funnel"></i> ATUALIZAR LISTA
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Resultados -->
    <div class="content-card shadow-sm p-0">
        <div class="p-3 border-bottom bg-light">
            <span class="text-muted fw-bold">PROCEDIMENTOS SEM CHIPS DE ENVOLVIDOS</span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 15%">DATA REGISTRO</th>
                        <th style="width: 20%">BOE</th>
                        <th style="width: 15%">IP</th>
                        <th style="width: 25%">INCIDÊNCIA PENAL</th>
                        <th style="width: 15%">RESPONSÁVEL</th>
                        <th style="width: 10%" class="text-center">AÇÃO</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $reg)
                        <tr>
                            <td class="text-muted small">
                                {{ \Carbon\Carbon::parse($reg->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <span class="badge-boe">{{ $reg->boe ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="text-primary fw-bold">{{ $reg->ip ?: '-' }}</span>
                            </td>
                            <td class="small">
                                {{ $reg->incidencia_penal ?: 'Não informada' }}
                            </td>
                            <td class="small">
                                {{ $reg->responsavel ?: 'Desconhecido' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ url('/ip-apfd?boe=') }}{{ $reg->boe }}" target="_blank" class="btn btn-action btn-sm">
                                    <i class="bi bi-pencil-square"></i> Resolver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-check-circle-fill text-success fs-1 mb-3 d-block"></i>
                                <h5 class="text-dark">Tudo em ordem!</h5>
                                <p class="text-muted">Nenhum procedimento pendente encontrado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(count($registros) >= 300)
            <div class="text-center p-2 bg-light border-top">
                <small class="text-muted">Mostrando os primeiros 300 registros.</small>
            </div>
        @endif
    </div>

</div>
@endsection
