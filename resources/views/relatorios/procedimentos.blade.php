<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Relatório de Procedimentos - SisDP</title>
    
    <!-- CSS (Mantendo consistência com wf_inicio) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    
    <!-- Reutilizando estilos principais -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        /* Padrão de Cores Moderno Dark Mode - SisDP (Baseado na Auditoria) */
        body, .main-content {
            background-color: #12141c !important;
            background: #12141c !important;
            color: #e2e8f0 !important;
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Força fundo dark em qualquer utilitário ou card claro */
        .bg-white, .bg-light, .card.bg-white, .card.bg-light {
            background-color: transparent !important;
            background: transparent !important;
        }

        /* Sidebar Dark Slate */
        .sidebar {
            background: #0f172a !important;
            border-right: 1px solid #1e293b;
            color: #94a3b8;
        }
        .sidebar-header {
            border-bottom: 1px solid #1e293b;
            background-color: transparent !important;
            color: #fff;
        }
        .sidebar-menu li a { color: #94a3b8; }
        .sidebar-menu li a:hover, .sidebar-menu li .active-submenu {
            background: #1e293b !important;
            color: #38bdf8 !important;
        }
        .active-submenu {
            border-left: 3px solid #38bdf8 !important;
        }
        .menu-toggle { color: #94a3b8; }

        /* Overriding Light Bootstrap Utilities */
        .bg-white, .bg-light { background-color: transparent !important; }
        .text-dark { color: #f8fafc !important; }
        .text-muted { color: #94a3b8 !important; }

        /* Module Header (Faixa do Título) */
        .module-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #334155;
            color: #f8fafc;
            padding: 20px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        .module-header h2 { margin: 0; color: #fff !important; }

        /* Filter bar matching Auditoria select boxes */
        .filter-bar {
            background: #1e293b;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #334155;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            margin-bottom: 2rem;
        }
        .form-label {
            color: #94a3b8 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .form-select, .form-control {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #f8fafc !important;
            border-radius: 8px;
        }
        .form-select:focus, .form-control:focus {
            border-color: #38bdf8 !important;
            box-shadow: 0 0 0 0.25rem rgba(56, 189, 248, 0.25) !important;
        }

        /* Report Cards */
        .report-card {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
            border-radius: 12px !important;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
        }
        .report-card:hover {
            transform: translateY(-5px);
            border-color: #38bdf8 !important;
            box-shadow: 0 8px 25px rgba(0,0,0,0.5) !important;
        }
        .card-icon {
            background: #0f172a !important;
            border-radius: 10px;
        }
        .count-value { color: #fff !important; font-weight: 700; font-size: 2.2rem !important; }

        /* Ranking and Detalhamento Cards */
        .card.border-0 {
            background: #1e293b !important;
            border: 1px solid #334155 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3) !important;
            overflow: hidden !important;
        }
        .card-header {
            background: #0f172a !important;
            border-bottom: 1px solid #334155 !important;
            padding: 15px 25px !important;
        }
        .card-header h5 { color: #e2e8f0 !important; font-weight: 600 !important; margin: 0; }
        
        .card-footer {
            background: #0f172a !important;
            border-top: 1px solid #334155 !important;
        }

        /* Tables matching Auditoria table style */
        .table { color: #e2e8f0 !important; }
        .table thead th {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
            border-bottom: 2px solid #334155 !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            font-size: 0.75rem !important;
            padding: 12px 20px !important;
            letter-spacing: 0.5px;
        }
        .table tbody td {
            background-color: transparent !important;
            border-bottom: 1px solid #334155 !important;
            padding: 15px 20px !important;
            color: #e2e8f0 !important;
            vertical-align: middle !important;
        }
        .table-striped tbody tr:nth-of-type(odd) td {
            background-color: rgba(255, 255, 255, 0.02) !important;
        }
        .table-hover tbody tr:hover td {
            background-color: rgba(56, 189, 248, 0.05) !important;
            color: #fff !important;
        }

        /* Progress Bars */
        .progress {
            background-color: #0f172a !important;
            border-radius: 10px !important;
            border: 1px solid #334155;
        }

        /* Export Button */
        .btn-success {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            border: none !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            padding: 10px 20px !important;
        }
        .btn-success:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4) !important;
        }

        /* Modal Dark */
        .modal-content {
            background-color: #1e293b !important;
            border: 1px solid #475569 !important;
            color: #e2e8f0 !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5) !important;
        }
        .modal-header { border-bottom: 1px solid #334155 !important; }
        .modal-footer { border-top: 1px solid #334155 !important; }

        /* Estilização da Paginação Dark */
        #paginacaoContainer .pagination { margin-top: 10px; }
        #paginacaoContainer .page-link {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.2s;
        }
        #paginacaoContainer .page-link:hover {
            background-color: #334155 !important;
            color: #fff !important;
        }
        #paginacaoContainer .page-item.disabled .page-link {
            background-color: #0f172a !important;
            color: #475569 !important;
            border-color: #1e293b !important;
        }

        /* Suavização das Badges de Prioridade Média (Amarelo Forte) */
        .badge.bg-warning {
            background-color: #ca8a04 !important; /* Amber mais fechado/premium */
            color: #fff !important; /* Texto branco para melhor contraste no dark */
            font-weight: 600 !important;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .badge.bg-danger {
            background-color: #991b1b !important; /* Vermelho mais sóbrio */
            color: #fff !important;
        }
        .badge.bg-success {
            background-color: #065f46 !important; /* Verde mais escuro e elegante */
            color: #fff !important;
        }
        .badge.bg-primary {
            background-color: #1e40af !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
    <!-- Menu Lateral (Estrutura idêntica ao wf_inicio) -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h5><i class="bi bi-menu-button-wide"></i> Menu do Sistema</h5>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('inicio') }}">
                    <i class="bi bi-file-earmark-text"></i> Módulo APFD | IP
                </a>
            </li>
            
            @if(Auth::user()->permissoes['administrativo'] ?? true)
            <li>
                <a href="{{ route('administrativo.index') }}">
                    <i class="bi bi-archive"></i> Administrativo
                </a>
            </li>
            @endif

            <!-- Menu Apreensão -->
            <li>
                <div class="sidebar-group-card">
                    <button class="menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#submenuApreensao" aria-expanded="false">
                        <span><i class="bi bi-bag-check"></i>Apreensão</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="collapse sidebar-submenu list-unstyled ps-3" id="submenuApreensao">
                        <li><a href="{{ route('celular') }}"><i class="bi bi-phone"></i>Celulares</a></li>
                        <li><a href="{{ route('veiculo') }}"><i class="bi bi-car-front"></i>Veículos</a></li>
                        <li><a href="{{ route('apreensao.outros') }}"><i class="bi bi-box-seam"></i>Outros Itens</a></li>
                    </ul>
                </div>
            </li>

            <li>
                <a href="{{ route('intimacao.index') }}" target="_blank">
                    <i class="bi bi-envelope-paper"></i>Intimação
                </a>
            </li>

            <!-- MENU RELATÓRIOS -->
            @if(Auth::user()->permissions['menu_lateral'] ?? true)
            <li>
                <div class="sidebar-group-card">
                    <button class="menu-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#submenuRelatorios" aria-expanded="true">
                        <span><i class="bi bi-file-earmark-bar-graph-fill"></i>Relatórios</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="collapse show sidebar-submenu list-unstyled ps-3" id="submenuRelatorios">
                        <li>
                            <a href="{{ url('/relatorios/procedimentos') }}" class="active-submenu">
                                <i class="bi bi-list-check"></i>Procedimentos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('consulta.pessoa.index') }}">
                                <i class="bi bi-person-bounding-box"></i>Antecedentes
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            @endif

            <li class="sidebar-footer mt-auto">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout-sidebar">
                        <i class="bi bi-box-arrow-right"></i> Sair do Sistema
                    </button>
                </form>
            </li>
        </ul>
    </div>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div class="container">
            <!-- Header da Página -->
            <div class="module-header d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-file-earmark-bar-graph me-2 text-info"></i>Relatório de Procedimentos</h2>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Acompanhamento e estatísticas de procedimentos policiais.</p>
                </div>
                <div class="text-end text-muted">
                    <small><i class="bi bi-clock"></i> Atualizado em: {{ date('d/m/Y H:i') }}</small>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-bar">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">ANO</label>
                        <select class="form-select border-0" id="filtroAno" style="background-color: #0f172a !important; color: white !important;">
                            <!-- Preenchido via JS -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">MÊS</label>
                        <select class="form-select border-0" id="filtroMes" style="background-color: #0f172a !important; color: white !important;">
                            <option value="">Todos os Meses</option>
                            <option value="1">Janeiro</option>
                            <option value="2">Fevereiro</option>
                            <option value="3">Março</option>
                            <option value="4">Abril</option>
                            <option value="5">Maio</option>
                            <option value="6">Junho</option>
                            <option value="7">Julho</option>
                            <option value="8">Agosto</option>
                            <option value="9">Setembro</option>
                            <option value="10">Outubro</option>
                            <option value="11">Novembro</option>
                            <option value="12">Dezembro</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">STATUS</label>
                        <select class="form-select border-0" id="filtroStatus" style="background-color: #0f172a !important; color: white !important;">
                            <option value="">Todos os Status</option>
                            <option value="Em andamento">Em Andamento</option>
                            <option value="Concluído">Concluído - Aguardando Relatório</option>
                            <option value="Remetido à Justiça">Remetido à Justiça</option>
                            <option value="Arquivado">Arquivado</option>
                            <option value="Parado">Aguardando Diligência</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-success w-100" onclick="abrirModalExportacao()">
                            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar Relatório
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cards de Resumo -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card report-card shadow-sm h-100 border-start border-4 border-primary" style="cursor: pointer;" onclick="filtrarPorStatus('')" title="Clique para ver todos">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center mb-1">
                                <div class="card-icon bg-primary bg-opacity-10 text-primary me-2" style="width: 32px; height: 32px; font-size: 1rem;">
                                    <i class="bi bi-files"></i>
                                </div>
                                <span class="small fw-bold text-muted">Total</span>
                            </div>
                            <div class="count-value text-primary fs-3" id="countTotal">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card report-card shadow-sm h-100 border-start border-4 border-info" style="cursor: pointer;" onclick="filtrarPorStatus('Em andamento')" title="Filtrar por Em Andamento">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center mb-1">
                                <div class="card-icon bg-info bg-opacity-10 text-info me-2" style="width: 32px; height: 32px; font-size: 1rem;">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <span class="small fw-bold text-muted">Em Andamento</span>
                            </div>
                            <div class="count-value text-info fs-3" id="countAndamento">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card report-card shadow-sm h-100 border-start border-4 border-success" style="cursor: pointer;" onclick="filtrarPorStatus('Concluído')" title="Filtrar por Concluídos">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center mb-1">
                                <div class="card-icon bg-success bg-opacity-10 text-success me-2" style="width: 32px; height: 32px; font-size: 1rem;">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <span class="small fw-bold text-muted">Concluído - Ag. Relatório</span>
                            </div>
                            <div class="count-value text-success fs-3" id="countConcluidos">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card report-card shadow-sm h-100 border-start border-4 border-secondary" style="cursor: pointer;" onclick="filtrarPorStatus('Remetido à Justiça')" title="Filtrar por Remetidos">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center mb-1">
                                <div class="card-icon bg-secondary bg-opacity-10 text-secondary me-2" style="width: 32px; height: 32px; font-size: 1rem;">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <span class="small fw-bold text-muted">Remetido à Justiça</span>
                            </div>
                            <div class="count-value text-secondary fs-3" id="countRemetidos">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card report-card shadow-sm h-100 border-start border-4 border-dark" style="cursor: pointer;" onclick="filtrarPorStatus('Arquivado')" title="Filtrar por Arquivados">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center mb-1">
                                <div class="card-icon bg-dark bg-opacity-10 text-dark me-2" style="width: 32px; height: 32px; font-size: 1rem;">
                                    <i class="bi bi-archive"></i>
                                </div>
                                <span class="small fw-bold text-muted">Arquivado</span>
                            </div>
                            <div class="count-value text-dark fs-3" id="countArquivados">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card report-card shadow-sm h-100 border-start border-4 border-warning" style="cursor: pointer;" onclick="filtrarPorStatus('Parado')" title="Filtrar por Parados">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center mb-1">
                                <div class="card-icon bg-warning bg-opacity-10 text-warning me-2" style="width: 32px; height: 32px; font-size: 1rem;">
                                    <i class="bi bi-pause-circle"></i>
                                </div>
                                <span class="small fw-bold text-muted">Aguardando Diligência</span>
                            </div>
                            <div class="count-value text-warning fs-3" id="countParados">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção de Análise Estratégica -->
            <div class="row g-4 mb-4">
                <div class="col-md-12">
                    <div class="card border-0">
                        <div class="card-header border-bottom-0 py-3 rounded-top-4">
                            <h5 class="mb-0 fw-bold"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Ranking de Naturezas (Top 5)</h5>
                            <small class="text-muted">Quais são os crimes mais frequentes neste período?</small>
                        </div>
                        <div class="card-body">
                            <div id="rankingContainer" class="row g-3">
                                <!-- Preenchido via JS -->
                                <div class="col-12 text-center text-muted py-3">Carregando dados...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Resultados -->
            <div class="card border-0">
                <div class="card-header border-bottom-0 py-3 rounded-top-4">
                    <h5 class="mb-0 fw-bold">Detalhamento</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="tabelaResultados">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 ps-4 border-0 rounded-start-4">Data</th>
                                <th class="py-3 border-0">BOE</th>
                                <th class="py-3 border-0">IP</th>
                                <th class="py-3 border-0">Status</th>
                                <th class="py-3 border-0">Natureza</th>
                                <th class="py-3 pe-4 border-0 rounded-end-4">Prioridade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Preenchido via JS -->
                        </tbody>
                    </table>
                </div>
                <!-- Rodapé da Tabela com Paginação -->
                <div class="card-footer border-top-0 py-3">
                    <div id="paginacaoContainer" class="d-flex justify-content-center"></div>
                </div>
            </div>

        </div>

        <!-- Modal Exportação -->
        <div class="modal fade" id="modalExportacao" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="bi bi-cloud-download me-2"></i>Exportar Relatório</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label for="formatoExportacao" class="form-label fw-bold">Escolha o formato:</label>
                            <select class="form-select" id="formatoExportacao">
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="pdf">PDF (.pdf)</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-light border">
                            <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle me-1"></i> Resumo da Exportação:</h6>
                            <ul class="mb-0 small text-muted">
                                <li><span fw-bold>Filtro de Ano:</span> <span id="resumoAno">Todos</span></li>
                                <li><span fw-bold>Filtro de Mês:</span> <span id="resumoMes">Todos</span></li>
                                <li><span fw-bold>Filtro de Status:</span> <span id="resumoStatus">Todos</span></li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="confirmarExportacao()">
                            <i class="bi bi-download me-1"></i> Baixar Arquivo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script do Relatório -->
    <script src="{{ asset('js/relatorios/procedimentos.js') }}?v={{ time() }}"></script>
</body>
</html>
