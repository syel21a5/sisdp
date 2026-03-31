<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>SisDP - Controle de Intimações</title>
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
                        <li><a href="{{ route('veiculo') }}"><i class="bi bi-car-front"></i>Veículos</a></li>
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
                <a href="{{ route('intimacao.index') }}" class="active">
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
                        SisDP - Controle de Intimações
                    </h1>
                </div>
                <div class="system-info">
                    <div class="system-date" id="currentDateTime">{{ date('d/m/Y H:i:s') }}</div>
                    <div class="system-user">Usuário: {{ Auth::user()->nome ?? 'Administrador' }}</div>
                </div>
            </div>

            <!-- Sub-abas de INTIMAÇÃO -->
            <ul class="nav nav-tabs mt-1" id="subAbasIntimacao" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#dados-intimacao" role="tab">
                        <i class="bi bi-file-earmark-text me-1"></i> Dados da Intimação
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#controle-intimacao" role="tab">
                        <i class="bi bi-calendar-check me-1"></i> Controle de Intimações
                    </a>
                </li>
            </ul>

            <style>
                /* Cores de chips personalizadas */
                .bg-chip-vitima { background-color: #f1c40f !important; color: #000 !important; }
                .bg-chip-testemunha { background-color: #2980b9 !important; color: #fff !important; }
                .bg-chip-autor { background-color: #c0392b !important; color: #fff !important; }
                .bg-chip-envolvido { background-color: #7f8c8d !important; color: #fff !important; }
                
                .chip-selecao:hover {
                    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
                    border-color: rgba(0,0,0,0.3) !important;
                    filter: none !important;
                    backface-visibility: hidden;
                    -webkit-font-smoothing: subpixel-antialiased;
                }
                .chip-selecao {
                   transition: transform 0.2s ease, box-shadow 0.2s ease !important;
                }
            </style>

            <div class="tab-content mt-2">
                <!-- DADOS DA INTIMAÇÃO -->
                <div class="tab-pane fade show active" id="dados-intimacao" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center sticky-top border-bottom" style="z-index: 1000;">
                            <h5 class="mb-0 text-primary"><i class="bi bi-person-badge me-2"></i>Cadastro de Intimação</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnNovoIntimacao" title="Novo Cadastro">
                                    <i class="bi bi-file-earmark-plus"></i> <span>NOVO</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-success" id="btnSalvarIntimacao" title="Salvar Cadastro">
                                    <i class="bi bi-save"></i> <span>SALVAR</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" id="btnEditarIntimacao" disabled title="Editar Cadastro">
                                    <i class="bi bi-pencil-square"></i> <span>EDITAR</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" id="btnExcluirIntimacao" disabled title="Excluir Cadastro">
                                    <i class="bi bi-trash"></i> <span>EXCLUIR</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" id="btnLimparIntimacao" title="Limpar Formulário">
                                    <i class="bi bi-x-circle"></i> <span>LIMPAR</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-info" id="btnImprimirIntimacao" style="background-color: #5bc0de; border-color: #5bc0de; color: black;" title="Imprimir">
                                    <i class="bi bi-printer"></i> <span>Imprimir</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body py-3">
                            <form id="formIntimacao">
                                @csrf
                                <input type="hidden" name="id" id="intimacao_id">
                                <input type="hidden" name="Tipo" id="inputTipoEnvolvidoIntimacao">

                                <!-- Linha 1: Datas e Horário -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-2">
                                        <label for="inputDataIntimacao" class="form-label">Data Cadastro</label>
                                        <input type="text" class="form-control flatpickr-date" placeholder="DD/MM/AAAA" name="data" id="inputDataIntimacao" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="inputDataCompIntimacao" class="form-label">Data por Extenso</label>
                                        <input type="text" class="form-control" placeholder="DATA COMPLETA" name="data_comp" id="inputDataCompIntimacao" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputDataOitivaIntimacao" class="form-label text-primary fw-bold">Data da Oitiva</label>
                                        <input type="text" class="form-control flatpickr-date border-primary shadow-sm" placeholder="DD/MM/AAAA" name="dataoitiva" id="inputDataOitivaIntimacao">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputHorarioIntimacao" class="form-label">Horário</label>
                                        <input type="text" class="form-control flatpickr-time" placeholder="HH:MM" name="hora" id="inputHorarioIntimacao">
                                    </div>
                                </div>

                                <!-- Linha 2: Delegado e Escrivão -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label for="inputDelegadoIntimacao" class="form-label">Delegado(a)</label>
                                        <input type="text" class="form-control" placeholder="DELEGADO(A)" name="delegado" id="inputDelegadoIntimacao" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="inputEscrivaoIntimacao" class="form-label">Escrivão</label>
                                        <input type="text" class="form-control" placeholder="ESCRIVÃO" name="escrivao" id="inputEscrivaoIntimacao">
                                    </div>
                                </div>

                                <!-- Linha 3: Delegacia e Cidade -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label for="inputDelegaciaIntimacao" class="form-label">Delegacia</label>
                                        <input type="text" class="form-control" placeholder="DELEGACIA" name="delegacia" id="inputDelegaciaIntimacao" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="inputCidadeIntimacao" class="form-label">Cidade</label>
                                        <input type="text" class="form-control" placeholder="CIDADE" name="cidade" id="inputCidadeIntimacao">
                                    </div>
                                </div>

                                <!-- Linha 4: BOE, Nome, Telefone e Situação -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-2">
                                        <label for="inputReferenciaIntimacao" class="form-label">Referência (BOE)</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="BOE" name="BOE" id="inputReferenciaIntimacao">
                                            <button class="btn btn-info text-dark" type="button" data-bs-toggle="modal" data-bs-target="#modalImportarBoeIntimacao" title="Importar Histórico do BOE">
                                                <i class="bi bi-upload"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="inputNomeIntimacao" class="form-label">Nome</label>
                                        <input type="text" class="form-control" placeholder="NOME" name="Nome" id="inputNomeIntimacao" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="inputTelefoneIntimacao" class="form-label">Telefone</label>
                                        <input type="text" class="form-control phone-mask" placeholder="TELEFONE" name="Telefone" id="inputTelefoneIntimacao">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputSituacaoIntimacao" class="form-label fw-bold">Situação / Presença</label>
                                        <select class="form-select border-primary" name="situacao" id="inputSituacaoIntimacao">
                                            <option value="PENDENTE" selected>⏳ PENDENTE</option>
                                            <option value="COMPARECEU">✅ COMPARECEU</option>
                                            <option value="NÃO COMPARECEU">❌ NÃO COMPARECEU</option>
                                            <option value="REMARCADO">📅 REMARCADO</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Linha 5: Endereço -->
                                <div class="row g-2 mb-2">
                                    <div class="col-md-12">
                                        <label for="inputEnderecoIntimacao" class="form-label">Endereço</label>
                                        <input type="text" class="form-control" placeholder="ENDEREÇO" name="Endereco" id="inputEnderecoIntimacao">
                                    </div>
                                </div>

                                <!-- Linha 6: Observações -->
                                <div class="row g-2 mb-3">
                                    <div class="col-md-12">
                                        <label for="inputObservacoesIntimacao" class="form-label">Observações / Motivo da Falta</label>
                                        <textarea class="form-control" name="observacoes" id="inputObservacoesIntimacao" rows="2" placeholder="Anote aqui detalhes importantes sobre a intimação ou o motivo pelo qual a pessoa não compareceu..."></textarea>
                                    </div>
                                </div>

                                <!-- Chips -->
                                <div id="chipsIntimacaoContainer" class="d-flex flex-wrap gap-2 mt-1"></div>
                            </form>
                        </div>
                    </div>

                    <!-- Pesquisa -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header bg-light py-2">
                            <h5 class="mb-0"><i class="bi bi-search me-2"></i>Pesquisa de Intimações</h5>
                        </div>
                        <div class="card-body py-3">
                            <div class="row g-2 mb-2">
                                <div class="col-md-3">
                                    <label for="filtroIntimacao" class="form-label">Filtrar por</label>
                                    <select class="form-select" id="filtroIntimacao">
                                        <option value="Nome">Nome</option>
                                        <option value="BOE">BOE</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <label for="termoPesquisaIntimacao" class="form-label">Termo de pesquisa</label>
                                    <input type="text" class="form-control" id="termoPesquisaIntimacao" placeholder="Digite para pesquisar...">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" id="btnPesquisarIntimacao">
                                        <i class="bi bi-search me-1"></i> Pesquisar
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive mt-3">
                                <table class="table table-striped table-hover" id="tabelaResultadosIntimacao">
                                    <thead class="table-light">
                                        <tr>
                                            <th>DATA</th>
                                            <th>BOE</th>
                                            <th>NOME</th>
                                            <th>DATA OITIVA</th>
                                            <th>SITUAÇÃO</th>
                                            <th>AÇÕES</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CONTROLE -->
                <div class="tab-pane fade" id="controle-intimacao" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white py-2">
                            <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Dashboard de Controle de Intimações</h5>
                        </div>
                        <div class="card-body">
                            <!-- Cards de Resumo -->
                            <div class="row mb-4 g-3">
                                <div class="col-md-2">
                                    <div class="card bg-danger text-white border-0 shadow-sm">
                                        <div class="card-body text-center py-2">
                                            <h4 class="card-title mb-0" id="contador-hoje">0</h4>
                                            <small>Hoje</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-warning text-dark border-0 shadow-sm">
                                        <div class="card-body text-center py-2">
                                            <h4 class="card-title mb-0" id="contador-amanha">0</h4>
                                            <small>Amanhã</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="card bg-primary text-white border-0 shadow-sm">
                                        <div class="card-body text-center py-2">
                                            <h4 class="card-title mb-0" id="contador-semana">0</h4>
                                            <small>7 Dias</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-dark text-white border-0 shadow-sm">
                                        <div class="card-body text-center py-2 border-start border-danger border-4">
                                            <h4 class="card-title mb-0" id="contador-faltas" style="color: #ff4d4d;">0</h4>
                                            <small>Faltas (Não Compareceu)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-secondary text-white border-0 shadow-sm">
                                        <div class="card-body text-center py-2">
                                            <h4 class="card-title mb-0" id="contador-total">0</h4>
                                            <small>Total Geral</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtros Controle -->
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Data Início</label>
                                    <input type="date" class="form-control" id="filtroDataInicio">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Data Fim</label>
                                    <input type="date" class="form-control" id="filtroDataFim">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-primary w-100" id="btnFiltrarIntimacoes">
                                        <i class="bi bi-funnel me-1"></i> Filtrar Período
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="tabelaControleIntimacao">
                                    <thead class="table-light">
                                        <tr>
                                            <th>DATA OITIVA</th>
                                            <th>QUANTIDADE</th>
                                            <th>DETALHES</th>
                                            <th>AÇÕES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="corpoTabelaControle"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmar Exclusão Intimação (Substituído por window.confirmarExclusaoGenerica no core.js) -->
    
    <!-- Modal Importar BOE Intimação - Premium -->
    <div class="modal fade" id="modalImportarBoeIntimacao" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold" id="modalImportarBoeLabel">Importar Dados de Intimação do BOE pelo Sistema</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="nav nav-tabs mb-3" id="intimacaoImportTabs">
                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#content-texto-intimacao" type="button"><i class="bi bi-card-text me-1"></i> 📝 Colar Texto (Rápido)</button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#content-pdf-intimacao" type="button"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> 📄 Enviar PDF</button>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="content-texto-intimacao">
                                    <div class="alert alert-info"><i class="bi bi-magic me-1"></i> <strong>Extração pelo Sistema:</strong> Copie o texto completo do Boletim de Ocorrência e cole abaixo.</div>
                                    <textarea class="form-control" id="textoBoeIntimacao" rows="10" placeholder="Cole o texto do BOE aqui..."></textarea>
                                </div>
                        <div class="tab-pane fade" id="content-pdf-intimacao">
                            <div class="alert alert-warning"><i class="bi bi-file-earmark-pdf me-1"></i> <strong>Leitura de PDF:</strong> O arquivo será analisado pelo sistema.</div>
                            <input class="form-control" type="file" id="pdfBoeIntimacao" accept=".pdf">
                        </div>
                    </div>

                    <div id="boeProgressWrapperIntimacao" class="mt-3" style="display:none;">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted fw-bold">🤖 O sistema está processando o BOE...</small>
                            <small class="text-muted" id="boeProgressPercentIntimacao">0%</small>
                        </div>
                        <div class="progress" style="height:10px; border-radius:10px;">
                            <div id="boeProgressBarIntimacao" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%; border-radius:10px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light rounded-bottom-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary fw-bold" id="btnProcessarBoeIntimacao">Processar pelo Sistema</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDetalhesData" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5 class="modal-title">Detalhes da Data</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body" id="conteudoDetalhesData"></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button></div></div></div></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
    
    <!-- JS Central do Sistema -->
    <script src="{{ asset('js/core.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".flatpickr-date", { dateFormat: "d/m/Y", allowInput: true, locale: "pt" });
            flatpickr(".flatpickr-time", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true, allowInput: true, locale: "pt" });
        });

        var rotasIntimacao = {
            pesquisar: "{{ route('intimacao.pesquisar') }}",
            salvar: "{{ route('intimacao.salvar') }}",
            buscar: "{{ route('intimacao.buscar', '') }}",
            buscarBoe: "{{ route('intimacao.buscarBoe', '') }}",
            atualizar: "{{ route('intimacao.atualizar', '') }}",
            excluir: "{{ route('intimacao.excluir', '') }}",
            controlePeriodo: "{{ route('intimacao.controle.periodo') }}",
            ultimos: "{{ route('intimacao.ultimos') }}",
            editor: "{{ route('intimacao.editor', '') }}"
        };
    </script>
    </script>

    <script src="{{ asset('js/script_intimacao.js') }}"></script>
    <script src="{{ asset('js/menu_lateral.js') }}"></script>

</body>
</html>
