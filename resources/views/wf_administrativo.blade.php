<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SISDP - Módulo Administrativo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 e Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- jQuery UI para autocomplete -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

    <!-- Flatpickr para seletores de data/hora -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

<style>
    .dashboard-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        border-radius: 12px;
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .btn-action {
        min-width: 110px; /* Largura uniforme para os botões */
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        padding: 0.375rem 0.75rem; /* Padding padrão do bootstrap */
    }
    .btn-action i {
        margin-right: 6px;
    }
    .metric-number {
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1;
    }
    .progress-thin {
        height: 6px;
    }
    .crime-tag {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .stat-badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
    }
    .filter-section {
        background: linear-gradient(135deg, #5d6d7e 0%, #85929e 100%);
        border-radius: 12px;
        color: white;
    }
    .filter-section .form-label {
        color: white !important;
        font-weight: 500;
    }
    .filter-section h6 {
        color: white;
    }
    .nav-tabs .nav-link.active {
        border-bottom: 3px solid #007bff;
        font-weight: 600;
    }
    .chart-container {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
    }
    .export-btn {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        color: white;
        font-weight: 600;
    }
    .export-btn:hover {
        background: linear-gradient(45deg, #218838, #1e9e80);
        transform: translateY(-2px);
    }
</style>
</head>
<body>
    <!-- Menu Lateral -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h5><i class="bi bi-menu-button-wide"></i> Menu do Sistema</h5>
            @php
                $userPermissions = Auth::user()->permissions ?? [];
                $hasMenuAccess = isset($userPermissions['menu_lateral']) ? $userPermissions['menu_lateral'] : true;
                $canApreensao = isset($userPermissions['apreensao']) ? $userPermissions['apreensao'] : true;
                $canAdministrativo = isset($userPermissions['administrativo']) ? $userPermissions['administrativo'] : true;
                $canCelular = isset($userPermissions['celular']) ? $userPermissions['celular'] : true;
                $canVeiculo = isset($userPermissions['veiculo']) ? $userPermissions['veiculo'] : true;
                $canIntimacao = isset($userPermissions['intimacao']) ? $userPermissions['intimacao'] : true;
                $canApreensaoOutros = isset($userPermissions['apreensao_outros']) ? $userPermissions['apreensao_outros'] : true;
                $canApfd = isset($userPermissions['apfd']) ? $userPermissions['apfd'] : true;
            @endphp
            @if(!$hasMenuAccess)
            @endif
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('geral') }}">
                    <i class="bi bi-house-door-fill"></i> Home
                </a>
            </li>

            @if($hasMenuAccess && $canApfd)
            <li class="{{ !$hasMenuAccess ? 'menu-disabled' : '' }}">
                <a href="{{ route('inicio') }}" class="{{ !$hasMenuAccess ? 'permission-tooltip' : '' }}">
                    <i class="bi bi-file-earmark-text"></i> Módulo APFD | IP
                </a>
            </li>
            @endif

            @if($hasMenuAccess && $canApreensao && $canCelular)
            <li>
                <div class="sidebar-group-card">
                    <button class="menu-toggle" type="button" onclick="toggleSubmenu('apreensao-submenu')">
                        <span><i class="bi bi-bag-check"></i>Apreensão</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="sidebar-submenu" id="apreensao-submenu">
                        <li><a href="{{ route('celular') }}" target="_blank"><i class="bi bi-phone"></i>Celulares</a></li>
                        <li><a href="{{ route('veiculo') }}" target="_blank"><i class="bi bi-car-front"></i>Veículos</a></li>
                        @if($canApreensaoOutros)
                        <li><a href="#"><i class="bi bi-box-seam"></i>Outros Itens</a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            @if($hasMenuAccess && $canIntimacao)
            <li>
                <a href="{{ route('intimacao.index') }}" target="_blank">
                    <i class="bi bi-envelope-paper"></i>Intimação
                </a>
            </li>
            @endif
            <li class="{{ !$hasMenuAccess ? 'menu-disabled' : '' }}">
                <a href="#" class="{{ !$hasMenuAccess ? 'permission-tooltip' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>Relatórios
                </a>
            </li>

            <!-- BOTÃO DE SAIR -->
            <li class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout-sidebar">
                        <i class="bi bi-box-arrow-right"></i> Sair do Sistema
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Botão para recolher/expandir menu -->
    <button class="menu-collapse-btn" id="menuCollapseBtn">
        <i class="bi bi-list"></i>
    </button>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div class="container">

            <!-- Cabeçalho Profissional -->
            <div class="header-container">
                <div class="page-header">
                    <h1 class="page-title">
                        <img src="{{ asset('images/police_avatar.ico') }}" alt="Logo" class="me-2">
                        SisDP - Módulo Administrativo
                    </h1>
                </div>
                <div class="system-info">
                    <div class="system-date" id="currentDateTime">{{ date('d/m/Y H:i:s') }}</div>
                    <div class="system-user">Usuário: {{ Auth::user()->nome ?? 'Administrador' }}</div>
                </div>
            </div>

            <!-- Abas de Administrativo -->
            <ul class="nav nav-tabs mt-4" id="abasAdministrativo" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#dados-administrativo" role="tab">
                        <i class="bi bi-file-earmark-text me-1"></i> Dados Administrativos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#dashboard-administrativo" role="tab">
                        <i class="bi bi-graph-up me-1"></i> Dashboard Geral
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#relatorio-crimes" role="tab">
                        <i class="bi bi-shield-shaded me-1"></i> Análise de Crimes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#relatorio-pessoas" role="tab">
                        <i class="bi bi-people me-1"></i> Relatório de Pessoas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#relatorio-apreensoes" role="tab">
                        <i class="bi bi-bag-check me-1"></i> Apreensões
                    </a>
                </li>
            </ul>

            <!-- Conteúdo das abas -->
            <div class="tab-content mt-3">

                <!-- ABA DE DADOS (MANTIDA ORIGINAL) -->
                <div class="tab-pane fade show active" id="dados-administrativo" role="tabpanel">
                    <div class="card border-0 shadow-sm dashboard-card">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center sticky-top border-bottom" style="z-index: 1000;">
                            <h5 class="mb-0 text-primary"><i class="bi bi-archive me-2"></i>Cadastro Administrativo</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-action" id="btnNovoAdministrativo" title="Novo Registro (Ctrl+N)">
                                    <i class="bi bi-file-earmark-plus"></i> <span>Novo</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-success btn-action" id="btnSalvarAdministrativo" title="Salvar Registro (Ctrl+S)">
                                    <i class="bi bi-save"></i> <span>Salvar</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning btn-action" id="btnEditarAdministrativo" disabled title="Editar Registro">
                                    <i class="bi bi-pencil-square"></i> <span>Editar</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger btn-action" id="btnExcluirAdministrativo" disabled title="Excluir Registro">
                                    <i class="bi bi-trash"></i> <span>Excluir</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary btn-action" id="btnLimparAdministrativo" title="Limpar Formulário">
                                    <i class="bi bi-x-circle"></i> <span>Limpar</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-dark btn-action" id="btnImportarBoeTexto" title="Importar BOE (Ctrl+I)">
                                    <i class="bi bi-file-text"></i> <span>Importar</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <form id="formAdministrativo">
                                @csrf
                                <input type="hidden" name="id" id="administrativo_id">

                                <!-- Primeira linha -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-3">
                                        <label for="inputDataCadastro" class="form-label">Data do Cadastro</label>
                                        <input type="text" class="form-control flatpickr-date" placeholder="DD/MM/AAAA" name="data_cadastro" id="inputDataCadastro" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputBoe" class="form-label">BOE</label>
                                        <input type="text" class="form-control" placeholder="BOE" name="boe" id="inputBoe" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputIp" class="form-label">IP</label>
                                        <input type="text" class="form-control" placeholder="IP" name="ip" id="inputIp">
                                    </div>
                                     <div class="col-md-3">
                                        <label for="inputCartorio" class="form-label">Cartório</label>
                                        <input type="text" class="form-control" placeholder="Cartório" name="cartorio" id="inputCartorio" list="listCartorios">
                                    </div>
                                </div>

                                <!-- Terceira linha -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label for="inputCrime" class="form-label">Crime</label>
                                        <input type="text" class="form-control" placeholder="Tipo de Crime" name="crime" id="inputCrime" list="listCrimes">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="inputTipificacao" class="form-label">Tipificação</label>
                                        <input type="text" class="form-control" placeholder="Tipificação do Crime" name="tipificacao" id="inputTipificacao" list="listTipificacoes">
                                    </div>
                                </div>

                                <!-- Quarta linha -->
                                <div class="row g-2 mb-3">
                                    <div class="col-12">
                                        <label for="inputApreensao" class="form-label">Apreensão</label>
                                        <textarea class="form-control" placeholder="Itens apreendidos..." name="apreensao" id="inputApreensao" rows="2"></textarea>
                                    </div>
                                </div>

                                <!-- Seção de Envolvidos Unificada -->
                                <div class="card bg-light border-0 mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3"><i class="bi bi-people me-2"></i>Gestão de Envolvidos</h6>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-5">
                                                <label for="inputNomeEnvolvido" class="form-label">Nome Completo</label>
                                                <input type="text" class="form-control" id="inputNomeEnvolvido" placeholder="Digite o nome do envolvido">
                                            </div>
                                            <div class="col-md-3">
                                                <label for="selectTipoEnvolvido" class="form-label">Tipo de Envolvimento</label>
                                                <select class="form-select" id="selectTipoEnvolvido">
                                                    <option value="vitimas">Vítima</option>
                                                    <option value="autores">Autor</option>
                                                    <option value="testemunhas">Testemunha</option>
                                                    <option value="capturados">Capturado</option>
                                                    <option value="adolescentes">Adolescente Apreendido</option>
                                                    <option value="outros">Outro</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-primary w-100" id="btnAddEnvolvido">
                                                    <i class="bi bi-plus-lg me-1"></i> Adicionar
                                                </button>
                                            </div>
                                        </div>

                                        <div class="table-responsive mt-3">
                                            <table class="table table-sm table-hover bg-white rounded shadow-sm" id="tabelaEnvolvidos">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 40%">Nome</th>
                                                        <th style="width: 30%">Tipo</th>
                                                        <th style="width: 30%" class="text-end">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Itens adicionados via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <!-- Campos ocultos para compatibilidade com o backend -->
                                        <input type="hidden" name="envolvidos" id="inputEnvolvidos">
                                        <input type="hidden" name="vitima" id="inputVitima">
                                        <input type="hidden" name="autor" id="inputAutor">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Área de Pesquisa -->
                    <div class="card border-0 shadow-sm dashboard-card mt-4">
                        <div class="card-header bg-light py-3">
                            <h5 class="mb-0"><i class="bi bi-search me-2"></i>Pesquisa de Registros</h5>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-2 mb-2">
                                <div class="col-md-3">
                                    <select class="form-select" id="filtroAdministrativo">
                                        <option value="boe">BOE</option>
                                        <option value="ip">IP</option>
                                        <option value="vitima">Vítima</option>
                                        <option value="autor">Autor</option>
                                        <option value="envolvido">Envolvido</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="termoPesquisaAdministrativo" placeholder="Digite para pesquisar...">
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" id="btnPesquisarAdministrativo"><i class="bi bi-search me-1"></i> Pesquisar</button>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table class="table table-striped table-hover" id="tabelaResultadosAdministrativo">
                                    <thead class="table-light">
                                        <tr>
                                            <th>BOE</th>
                                            <th>IP</th>
                                            <th>Vítima</th>
                                            <th>Autor</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Resultados via JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ABA DASHBOARD GERAL -->
                <div class="tab-pane fade" id="dashboard-administrativo" role="tabpanel">
                    <div class="card border-0 shadow-sm dashboard-card">
                        <div class="card-header bg-primary text-white py-3">
                            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Dashboard Geral - Análise Completa</h5>
                        </div>
                        <div class="card-body">

                            <!-- Filtros Avançados -->
                            <div class="row mb-3 justify-content-center">
                                <div class="col-md-12">
                                    <div class="card border-0 shadow-sm filter-section text-white">
                                        <div class="card-body py-2">
                                            <h6 class="mb-2"><i class="bi bi-funnel me-1"></i> Filtros Avançados</h6>
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-3">
                                                    <label class="form-label">Período</label>
                                                    <select class="form-select form-select-sm" id="filtroPeriodo">
                                                        <option value="hoje">Hoje</option>
                                                        <option value="semana">Esta Semana</option>
                                                        <option value="mes">Este Mês</option>
                                                        <option value="ano">Este Ano</option>
                                                        <option value="personalizado" selected>Personalizado</option>
                                                        <option value="todos">Todos os Registros</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Tipo de Análise</label>
                                                    <select class="form-select form-select-sm" id="filtroTipo">
                                                        <option value="resumo">Resumo Geral</option>
                                                        <option value="crime">Por Crime</option>
                                                        <option value="vitima">Por Vítima</option>
                                                        <option value="autor">Por Autor</option>
                                                        <option value="cartorio">Por Cartório</option>
                                                        <option value="apreensao">Por Apreensão</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Data Início</label>
                                                    <input type="date" class="form-control form-control-sm" id="filtroDataInicio">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Data Fim</label>
                                                    <input type="date" class="form-control form-control-sm" id="filtroDataFim">
                                                </div>
                                            </div>
                                            <div class="row g-2 mt-2 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label">Crime</label>
                                                    <input type="text" class="form-control form-control-sm" id="filtroCrime" placeholder="Filtrar por crime...">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Cartório</label>
                                                    <input type="text" class="form-control form-control-sm" id="filtroCartorio" placeholder="Filtrar por cartório...">
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <button type="button" class="btn btn-light btn-sm me-2" id="btnGerarRelatorio">
                                                        <i class="bi bi-gear me-1"></i> Gerar Relatório
                                                    </button>
                                                    <button type="button" class="btn export-btn btn-sm" id="btnExportarGeral">
                                                        <i class="bi bi-download me-1"></i> Exportar Dashboard
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Métricas Principais -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white dashboard-card">
                                        <div class="card-body text-center py-4">
                                            <div class="metric-number" id="metric-total">0</div>
                                            <p class="card-text mb-0">Total Registros</p>
                                            <small class="opacity-75">Período selecionado</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-success text-white dashboard-card">
                                        <div class="card-body text-center py-4">
                                            <div class="metric-number" id="metric-hoje">0</div>
                                            <p class="card-text mb-0">Hoje</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-warning text-dark dashboard-card">
                                        <div class="card-body text-center py-4">
                                            <div class="metric-number" id="metric-semana">0</div>
                                            <p class="card-text mb-0">Esta Semana</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-info text-white dashboard-card">
                                        <div class="card-body text-center py-4">
                                            <div class="metric-number" id="metric-mes">0</div>
                                            <p class="card-text mb-0">Este Mês</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-secondary text-white dashboard-card">
                                        <div class="card-body text-center py-4">
                                            <div class="metric-number" id="metric-ano">0</div>
                                            <p class="card-text mb-0">Este Ano</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Métricas Secundárias -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm dashboard-card">
                                        <div class="card-body text-center py-3">
                                            <div class="metric-number text-primary" id="metric-vitimas">0</div>
                                            <p class="card-text mb-0">Vítimas Únicas</p>
                                            <small class="text-muted">Pessoas diferentes</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm dashboard-card">
                                        <div class="card-body text-center py-3">
                                            <div class="metric-number text-danger" id="metric-autores">0</div>
                                            <p class="card-text mb-0">Autores Únicos</p>
                                            <small class="text-muted">Pessoas diferentes</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm dashboard-card">
                                        <div class="card-body text-center py-3">
                                            <div class="metric-number text-success" id="metric-apreensoes">0</div>
                                            <p class="card-text mb-0">Com Apreensão</p>
                                            <small class="text-muted" id="metric-taxa-apreensao">0% dos casos</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Gráficos e Estatísticas -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm dashboard-card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bi bi-bar-chart me-1"></i> Top 10 Crimes Mais Comuns</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="graficoCrimes" style="min-height: 300px;">
                                                <p class="text-center text-muted my-5">Use os filtros para gerar o relatório</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm dashboard-card h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bi bi-calendar-event me-1"></i> Evolução Mensal (Últimos 6 Meses)</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="graficoMensal" style="min-height: 300px;">
                                                <p class="text-center text-muted my-5">Use os filtros para gerar o relatório</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabela de Relatório -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card border-0 shadow-sm dashboard-card">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="bi bi-table me-1"></i> Relatório Detalhado</h6>
                                            <div>
                                                <span class="badge bg-primary me-2" id="contadorRegistros">0 registros</span>
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnExportarTabela">
                                                    <i class="bi bi-download me-1"></i> Exportar
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped table-hover" id="tabelaRelatorio">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>BOE</th>
                                                            <th>Vítima</th>
                                                            <th>Autor</th>
                                                            <th>Crime</th>
                                                            <th>Ações</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="corpoTabelaRelatorio">
                                                        <tr>
                                                            <td colspan="5" class="text-center">Use os filtros acima para gerar o relatório</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ABA ANÁLISE DE CRIMES -->
                <div class="tab-pane fade" id="relatorio-crimes" role="tabpanel">
                    <div class="card border-0 shadow-sm dashboard-card">
                        <div class="card-header bg-danger text-white py-3">
                            <h5 class="mb-0"><i class="bi bi-shield-shaded me-2"></i>Análise Específica de Crimes</h5>
                        </div>
                        <div class="card-body">
                            <!-- Conteúdo será carregado via JavaScript -->
                            <div id="conteudo-relatorio-crimes" class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>Carregando análise de crimes...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ABA RELATÓRIO DE PESSOAS -->
                <div class="tab-pane fade" id="relatorio-pessoas" role="tabpanel">
                    <div class="card border-0 shadow-sm dashboard-card">
                        <div class="card-header bg-info text-white py-3">
                            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Relatório de Pessoas (Vítimas e Autores)</h5>
                        </div>
                        <div class="card-body">
                            <!-- Conteúdo será carregado via JavaScript -->
                            <div id="conteudo-relatorio-pessoas" class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>Carregando relatório de pessoas...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ABA APREENSÕES -->
                <div class="tab-pane fade" id="relatorio-apreensoes" role="tabpanel">
                    <div class="card border-0 shadow-sm dashboard-card">
                        <div class="card-header bg-warning text-dark py-3">
                            <h5 class="mb-0"><i class="bi bi-bag-check me-2"></i>Relatório de Apreensões</h5>
                        </div>
                        <div class="card-body">
                            <!-- Conteúdo será carregado via JavaScript -->
                            <div id="conteudo-relatorio-apreensoes" class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p>Carregando relatório de apreensões...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modais Básicos -->
            <div class="modal fade" id="modalSucessoAdministrativo" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title">Sucesso</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p id="sucessoMensagemAdministrativo"></p></div><div class="modal-footer"><button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button></div></div></div></div>
            <div class="modal fade" id="modalErroAdministrativo" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Erro</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p id="erroMensagemAdministrativo"></p></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button></div></div></div></div>
            <div class="modal fade" id="modalConfirmacaoAdministrativo" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-warning"><h5 class="modal-title">Confirmação</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Tem certeza que deseja excluir este registro?</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-danger" id="btnConfirmarExclusaoAdministrativo">Excluir</button></div></div></div></div>

            <!-- Modal para Detalhes do Relatório -->
            <div class="modal fade" id="modalDetalhesRelatorio" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalDetalhesRelatorioTitulo">Detalhes do Registro</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="conteudoDetalhesRelatorio">
                                <p><strong>BOE:</strong> <span id="detalhe-boe"></span></p>
                                <p><strong>IP:</strong> <span id="detalhe-ip"></span></p>
                                <p><strong>Vítima:</strong> <span id="detalhe-vitima"></span></p>
                                <p><strong>Autor:</strong> <span id="detalhe-autor"></span></p>
                                <p><strong>Crime:</strong> <span id="detalhe-crime"></span></p>
                                <p><strong>Tipificação:</strong> <span id="detalhe-tipificacao"></span></p>
                                <p><strong>Cartório:</strong> <span id="detalhe-cartorio"></span></p>
                                <p><strong>Apreensão:</strong></p>
                                <pre id="detalhe-apreensao" class="bg-light p-2 rounded"></pre>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Exportação -->
            <div class="modal fade" id="modalExportacao" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">Exportar Relatório</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="formatoExportacao" class="form-label">Formato de Exportação</label>
                                <select class="form-select" id="formatoExportacao">
                                    <option value="excel">Excel (.xlsx)</option>
                                    <option value="pdf">PDF (.pdf)</option>
                                    <option value="csv">CSV (.csv)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="tipoRelatorioExport" class="form-label">Tipo de Relatório</label>
                                <select class="form-select" id="tipoRelatorioExport">
                                    <option value="geral">Relatório Geral</option>
                                    <option value="crimes">Análise de Crimes</option>
                                    <option value="pessoas">Relatório de Pessoas</option>
                                    <option value="apreensoes">Relatório de Apreensões</option>
                                </select>
                            </div>
                            <div class="alert alert-info">
                                <small><i class="bi bi-info-circle me-1"></i> O arquivo será gerado com base nos filtros atuais aplicados.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success" id="btnConfirmarExportacao">
                                <i class="bi bi-download me-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Importar BOE Híbrido -->
            <div class="modal fade" id="modalImportarBoeTexto" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="bi bi-robot me-2"></i>Importar BOE com IA</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Abas de Navegação -->
                            <ul class="nav nav-tabs mb-3" id="boeImportTabsAdmin" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-texto-admin" data-bs-toggle="tab" data-bs-target="#content-texto-admin" type="button" role="tab"><i class="bi bi-card-text me-1"></i> 📝 Colar Texto (Rápido)</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-pdf-admin" data-bs-toggle="tab" data-bs-target="#content-pdf-admin" type="button" role="tab"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> 📄 Enviar Arquivo PDF</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="boeImportTabsContentAdmin">
                                <!-- Aba de Texto -->
                                <div class="tab-pane fade show active" id="content-texto-admin" role="tabpanel">
                                    <div class="alert alert-info">
                                        <i class="bi bi-magic me-1"></i> <strong>Extração Inteligente:</strong> Copie o texto completo do Boletim de Ocorrência e cole abaixo.
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control" id="textoBoeImportacao" rows="11" placeholder="Cole o texto aqui..."></textarea>
                                    </div>
                                </div>
                                
                                <!-- Aba de PDF -->
                                <div class="tab-pane fade" id="content-pdf-admin" role="tabpanel">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-cpu me-1"></i> <strong>Processamento Nativo:</strong> O arquivo PDF será aberto internamente e mapeado pela inteligência do sistema.
                                    </div>
                                    <div class="mb-3">
                                        <label for="pdfBoeImportacao" class="form-label fw-bold">Selecione o arquivo PDF do BOE:</label>
                                        <input class="form-control form-control-lg" type="file" id="pdfBoeImportacao" accept=".pdf">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Progresso da IA -->
                        <div id="boeProgressWrapperAdmin" class="px-3 pb-2" style="display:none;">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted fw-bold" id="boeProgressLabelAdmin">🤖 A IA está lendo o documento...</small>
                                <small class="text-muted" id="boeProgressPercentAdmin">0%</small>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 10px;">
                                <div id="boeProgressBarAdmin" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%; border-radius: 10px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-info" id="btnProcessarImportacaoBoe">
                                <i class="bi bi-gear me-1"></i> Processar com IA
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bibliotecas principais -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>

    <!-- Scripts específicos da página -->
    <script>
    var rotasAdministrativo = {
        pesquisar: "{{ route('administrativo.pesquisar') }}",
        salvar: "{{ route('administrativo.salvar') }}",
        importarBoeTexto: "{{ route('administrativo.importar_boe_texto') }}",
        buscar: "{{ url('/administrativo/buscar') }}",
        atualizar: "{{ url('/administrativo/atualizar') }}",
        excluir: "{{ url('/administrativo/excluir') }}",
        ultimos: "{{ route('administrativo.ultimos') }}",
        relatorio: "{{ route('administrativo.relatorio') }}",
        relatorioCrimes: "{{ route('administrativo.relatorio.crimes') }}",
        relatorioPessoas: "{{ route('administrativo.relatorio.pessoas') }}",
        relatorioApreensoes: "{{ route('administrativo.relatorio.apreensoes') }}",
        exportar: "{{ route('administrativo.exportar') }}"
    };
    </script>

    <script src="{{ asset('js/script_administrativo.js') }}"></script>
    <script src="{{ asset('js/menu_lateral.js') }}"></script>

    <!-- Datalists para Autocomplete -->
    <datalist id="listCrimes">
        <option value="Homicídio">
        <option value="Roubo">
        <option value="Furto">
        <option value="Ameaça">
        <option value="Lesão Corporal">
        <option value="Estelionato">
        <option value="Tráfico de Drogas">
        <option value="Posse de Drogas">
        <option value="Maria da Penha">
        <option value="Dano">
        <option value="Vias de Fato">
        <option value="Injúria">
    </datalist>

    <datalist id="listTipificacoes">
        <option value="Consumado">
        <option value="Tentado">
    </datalist>

    <datalist id="listCartorios">
        <option value="Cartório Central">
        <option value="Cartório de Homicídios">
        <option value="Cartório de Roubos">
        <option value="Cartório de Furtos">
        <option value="Cartório de Vulneráveis">
    </datalist>

    <!-- Container de Toasts -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>

</body>
</html>
