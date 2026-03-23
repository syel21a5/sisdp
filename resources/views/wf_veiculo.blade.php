<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SisDP - Controle de Veículos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 e Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- jQuery UI para autocomplete -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

    <!-- Flatpickr para seletores de data/hora -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
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
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('geral') }}">
                    <i class="bi bi-house-door-fill"></i> Home
                </a>
            </li>

            @if($hasMenuAccess && $canApfd)
            <li>
                <a href="{{ route('inicio') }}">
                    <i class="bi bi-file-earmark-text"></i> Módulo APFD | IP
                </a>
            </li>
            @endif

            @if($hasMenuAccess && $canAdministrativo)
            <li>
                <a href="{{ route('administrativo.index') }}">
                    <i class="bi bi-archive"></i> Administrativo
                </a>
            </li>
            @endif

            @if($hasMenuAccess && $canApreensao && ($canCelular || $canVeiculo))
            <li>
                <div class="sidebar-group-card">
                    <button class="menu-toggle active" type="button" onclick="toggleSubmenu('apreensao-submenu')">
                        <span><i class="bi bi-bag-check"></i>Apreensão</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="sidebar-submenu expanded" id="apreensao-submenu">
                        @if($canCelular)
                        <li><a href="{{ route('celular') }}"><i class="bi bi-phone"></i>Celulares</a></li>
                        @endif
                        @if($canVeiculo)
                        <li><a href="{{ route('veiculo') }}" class="active"><i class="bi bi-car-front"></i>Veículos</a></li>
                        @endif
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
                        SisDP - Controle de Veículos
                    </h1>
                </div>
                <div class="system-info">
                    <div class="system-date" id="currentDateTime">{{ date('d/m/Y H:i:s') }}</div>
                    <div class="system-user">Usuário: {{ Auth::user()->nome ?? 'Administrador' }}</div>
                </div>
            </div>

            <!-- Sub-abas de VEÍCULO -->
            <ul class="nav nav-tabs mt-4" id="subAbasVeiculo" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#dados-veiculo" role="tab">
                        <i class="bi bi-car-front me-1"></i> Dados do Veículo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#controle-veiculo" role="tab">
                        <i class="bi bi-bar-chart-line me-1"></i> Controle de Veículos
                    </a>
                </li>
            </ul>

            <!-- Conteúdo das sub-abas de VEÍCULO -->
            <div class="tab-content mt-3">
                <!-- DADOS DO VEÍCULO -->
                <div class="tab-pane fade show active" id="dados-veiculo" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white py-2">
                            <h5 class="mb-0"><i class="bi bi-car-front me-2"></i>Cadastro de Veículo</h5>
                        </div>
                        <div class="card-body py-3">
                            <form id="formVeiculo">
                                @csrf
                                <input type="hidden" name="id" id="veiculo_id">

                                <!-- Primeira linha: Data, IP, BOE, SEI -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-3">
                                        <label for="inputDataVeiculo" class="form-label">Data</label>
                                        <input type="text" class="form-control flatpickr-date" placeholder="DD/MM/AAAA" name="data" id="inputDataVeiculo" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputIpVeiculo" class="form-label">IP</label>
                                        <input type="text" class="form-control" placeholder="IP" name="ip" id="inputIpVeiculo">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputBoeVeiculo" class="form-label">BOE</label>
                                        <input type="text" class="form-control" placeholder="BOE" name="boe" id="inputBoeVeiculo" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputSeiVeiculo" class="form-label">SEI</label>
                                        <input type="text" class="form-control" placeholder="0000000000.000000/0000-00" name="sei" id="inputSeiVeiculo">
                                    </div>
                                </div>

                                <!-- Segunda linha: Pessoa e Veículo -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label for="inputPessoaVeiculo" class="form-label">Pessoa</label>
                                        <input type="text" class="form-control" placeholder="PESSOA" name="pessoa" id="inputPessoaVeiculo">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="inputVeiculoVeiculo" class="form-label">Veículo</label>
                                        <input type="text" class="form-control" placeholder="ESPECIFICAÇÃO (MARCA/MODELO/COR...)" name="veiculo" id="inputVeiculoVeiculo">
                                    </div>
                                </div>

                                <!-- Terceira linha: Placa, Chassi e Status -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-4">
                                        <label for="inputPlacaVeiculo" class="form-label">Placa</label>
                                        <input type="text" class="form-control" placeholder="PLACA" name="placa" id="inputPlacaVeiculo">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="inputChassiVeiculo" class="form-label">Chassi</label>
                                        <input type="text" class="form-control" placeholder="CHASSI" name="chassi" id="inputChassiVeiculo">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="inputStatusVeiculo" class="form-label">Status</label>
                                        <select class="form-select" name="status" id="inputStatusVeiculo">
                                            <option value="">STATUS</option>
                                            <option value="APREENDIDO">APREENDIDO</option>
                                            <option value="DEVOLVIDO">DEVOLVIDO</option>
                                            <option value="EM PERÍCIA">EM PERÍCIA</option>
                                            <option value="OUTROS">OUTROS</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Botões -->
                                <div class="button-group d-flex flex-wrap gap-2 mt-3 mb-2">
                                    <button type="button" class="btn btn-primary" id="btnNovoVeiculo">
                                        <i class="bi bi-file-earmark-plus me-1"></i> NOVO
                                    </button>
                                    <button type="button" class="btn btn-success" id="btnSalvarVeiculo">
                                        <i class="bi bi-save me-1"></i> SALVAR
                                    </button>
                                    <button type="button" class="btn btn-warning" id="btnEditarVeiculo" disabled>
                                        <i class="bi bi-pencil-square me-1"></i> EDITAR
                                    </button>
                                    <button type="button" class="btn btn-danger" id="btnExcluirVeiculo" disabled>
                                        <i class="bi bi-trash me-1"></i> EXCLUIR
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="btnLimparVeiculo">
                                        <i class="bi bi-x-circle me-1"></i> LIMPAR
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Área de Pesquisa -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-light py-2">
                            <h5 class="mb-0"><i class="bi bi-search me-2"></i>Pesquisa de Veículos</h5>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-2 mb-2">
                                <div class="col-md-3">
                                    <select class="form-select" id="filtroVeiculo">
                                        <option value="boe" selected>BOE</option>
                                        <option value="pessoa">Pessoa</option>
                                        <option value="sei">SEI</option>
                                        <option value="placa">Placa</option>
                                        <option value="chassi">Chassi</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="termoPesquisaVeiculo" placeholder="Digite para pesquisar...">
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary w-100" id="btnPesquisarVeiculo">
                                        <i class="bi bi-search me-1"></i> Pesquisar
                                    </button>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div id="emptyStatePesquisaVeiculo" class="text-center py-5 rounded bg-light border border-dashed text-muted mt-3">
                                <i class="bi bi-search display-4 text-secondary mb-3 opacity-50"></i>
                                <h5 class="fw-normal">Nenhum resultado exibido</h5>
                                <p class="mb-0 small">Utilize o campo acima para buscar por veículos cadastrados.</p>
                            </div>

                            <div id="conteinerTabelaPesquisaVeiculo" style="display: none;">
                                <!-- Paginação info -->
                                <div id="infoPaginacaoVeiculo" class="d-flex justify-content-between align-items-center mb-2" style="display:none!important;">
                                    <small class="text-muted" id="textoTotalVeiculo"></small>
                                    <div class="d-flex gap-1" id="paginacaoVeiculo"></div>
                                </div>

                                <div class="table-responsive mt-2">
                                <table class="table table-striped table-hover mt-3 w-100" id="tabelaResultadosVeiculo" style="font-size: 0.95rem; white-space: nowrap; table-layout: fixed; min-width: 1100px;">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 25%;">PESSOA</th>
                                            <th style="width: 20%;">SEI</th>
                                            <th style="width: 15%;">BOE</th>
                                            <th style="width: 10%;">PLACA</th>
                                            <th style="width: 12%;">STATUS</th>
                                            <th style="width: 11%;">RESPONSÁVEL</th>
                                            <th style="width: 7%;">AÇÕES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- CONTROLE DE VEÍCULOS -->
                <div class="tab-pane fade" id="controle-veiculo" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Controle de Veículos por Status</h5>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-light text-success fw-semibold" id="btnExportarExcelVeiculo" title="Baixar planilha Excel">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                                </button>
                                <button type="button" class="btn btn-sm btn-light text-danger fw-semibold" id="btnExportarPdfVeiculo" title="Baixar relatório PDF">
                                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                                </button>
                            </div>
                        </div>
                        <div class="card-body">

                            <!-- Cards de Resumo -->
                            <div class="row mb-4 g-3">
                                <div class="col-6 col-md-3">
                                    <div class="card bg-danger text-white h-100 shadow-sm card-status-clicavel" data-status="APREENDIDO" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Clique para ver detalhes">
                                        <div class="card-body text-center py-3">
                                            <h2 class="card-title mb-1 fw-bold" id="contador-apreendido">0</h2>
                                            <p class="card-text mb-0 small">Apreendidos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card bg-success text-white h-100 shadow-sm card-status-clicavel" data-status="DEVOLVIDO" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Clique para ver detalhes">
                                        <div class="card-body text-center py-3">
                                            <h2 class="card-title mb-1 fw-bold" id="contador-devolvido">0</h2>
                                            <p class="card-text mb-0 small">Devolvidos</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card bg-warning text-dark h-100 shadow-sm card-status-clicavel" data-status="EM PERÍCIA" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Clique para ver detalhes">
                                        <div class="card-body text-center py-3">
                                            <h2 class="card-title mb-1 fw-bold" id="contador-analise">0</h2>
                                            <p class="card-text mb-0 small">Em Perícia</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="card bg-secondary text-white h-100 shadow-sm card-status-clicavel" data-status="TODOS" style="cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" title="Clique para limpar os filtros e listar todos">
                                        <div class="card-body text-center py-3">
                                            <h2 class="card-title mb-1 fw-bold" id="contador-total">0</h2>
                                            <p class="card-text mb-0 small">Total</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros -->
                            <div class="row mb-3 g-2 align-items-end">
                                <div class="col-md-3">
                                    <label for="filtroDataInicioVeiculo" class="form-label fw-semibold">Data Início</label>
                                    <input type="date" class="form-control" id="filtroDataInicioVeiculo">
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroDataFimVeiculo" class="form-label fw-semibold">Data Fim</label>
                                    <input type="date" class="form-control" id="filtroDataFimVeiculo">
                                </div>
                                <div class="col-md-3">
                                    <label for="filtroStatusVeiculo" class="form-label fw-semibold">Status</label>
                                    <select class="form-select" id="filtroStatusVeiculo">
                                        <option value="">Todos</option>
                                        <option value="APREENDIDO">Apreendido</option>
                                        <option value="DEVOLVIDO">Devolvido</option>
                                        <option value="EM PERÍCIA">Em Perícia</option>
                                        <option value="ARQUIVADO">Arquivado</option>
                                        <option value="OUTROS">Outros</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary w-100" id="btnFiltrarVeiculos">
                                        <i class="bi bi-funnel me-1"></i> Filtrar
                                    </button>
                                </div>
                            </div>

                            <!-- Gráfico + Tabela lado a lado -->
                            <div class="row g-3">
                                <!-- Gráfico de Rosca -->
                                <div class="col-md-4">
                                    <div class="card border shadow-sm h-100">
                                        <div class="card-header bg-light py-2">
                                            <h6 class="mb-0"><i class="bi bi-pie-chart me-1"></i>Distribuição por Status</h6>
                                        </div>
                                        <div class="card-body d-flex align-items-center justify-content-center" style="min-height:220px;">
                                            <canvas id="graficoVeiculo" style="max-height:200px;"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tabela de Veículos por Status -->
                                <div class="col-md-8">
                                    <div class="card border shadow-sm h-100">
                                        <div class="card-header bg-light py-2">
                                            <h6 class="mb-0"><i class="bi bi-table me-1"></i>Detalhamento por Status</h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-hover mb-0" id="tabelaControleVeiculo">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>STATUS</th>
                                                            <th class="text-center">QTD</th>
                                                            <th>DETALHES</th>
                                                            <th class="text-center">AÇÕES</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="corpoTabelaControleVeiculo">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumo narrativo -->
                            <div class="alert alert-info mt-3 mb-0 py-2" id="resumoNarrativoVeiculo" style="display:none;">
                                <i class="bi bi-info-circle me-1"></i>
                                <span id="textoResumoNarrativoVeiculo"></span>
                            </div>

                            <!-- Modal para Detalhes do Status -->
                            <div class="modal fade" id="modalDetalhesStatusVeiculo" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title" id="modalDetalhesStatusVeiculoTitulo">Detalhes dos Veículos</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="conteudoDetalhesStatusVeiculo">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modais para feedback -->
            <div class="modal fade" id="modalSucessoVeiculo" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-success text-white"><h5 class="modal-title">Sucesso</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p id="sucessoMensagemVeiculo"></p></div><div class="modal-footer"><button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button></div></div></div></div>
            <div class="modal fade" id="modalErroVeiculo" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-danger text-white"><h5 class="modal-title">Erro</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p id="erroMensagemVeiculo"></p></div><div class="modal-footer"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button></div></div></div></div>
            <div class="modal fade" id="modalConfirmacaoVeiculo" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-warning"><h5 class="modal-title">Confirmação</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p>Tem certeza que deseja excluir este veículo?</p></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" class="btn btn-danger" id="btnConfirmarExclusaoVeiculo">Excluir</button></div></div></div></div>

        </div>
    </div>

    <!-- Bibliotecas principais -->
    <!-- Dados globais de Auth para o JS -->
    <script>
        window.currentUserId = {{ $userId ?? 'null' }};
        window.isAdminUser = {{ isset($isAdmin) && $isAdmin ? 'true' : 'false' }};
    </script>
    <!-- / Dados globais de Auth para o JS -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>

    <!-- Chart.js para os gráficos de Distribuição de Status -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Rotas do Veículo -->
    <script>
        // Inicializa o flatpickr
        document.addEventListener('DOMContentLoaded', function() {
            if (window.flatpickr) {
                var localeCfg = (flatpickr.l10ns && flatpickr.l10ns.pt) ? flatpickr.l10ns.pt : 'default';
                flatpickr('.flatpickr-date', {
                    dateFormat: 'd/m/Y',
                    allowInput: true,
                    locale: localeCfg
                });
            }
        });

        // Definir as rotas para o veículo
        var rotasVeiculo = {
            pesquisar: "{{ route('veiculo.pesquisar') }}",
            salvar: "{{ route('veiculo.salvar') }}",
            buscar: "{{ url('/veiculo/buscar') }}",
            atualizar: "{{ url('/veiculo/atualizar') }}",
            excluir: "{{ url('/veiculo/excluir') }}",
            controleStatus: "{{ route('veiculo.controle.status') }}",
            ultimos: "{{ route('veiculo.ultimos') }}",
            exportarExcel: "{{ route('veiculo.exportar.excel') }}",
            exportarPdf: "{{ route('veiculo.exportar.pdf') }}"
        };
    </script>

    <!-- JavaScript específico do veículo -->
    <script src="{{ asset('js/veiculo.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/menu_lateral.js') }}"></script>

</body>
</html>
