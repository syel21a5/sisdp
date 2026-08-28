<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Módulo IP | APFD - SYS-DP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 e Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- jQuery UI para autocomplete -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    
    <!-- SweetAlert2 para notificações e loadings -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dark_mode.css') }}?v={{ time() }}">

    <!-- Flatpickr para seletores de data/hora -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Cropper.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" />

    <style>
        /* Estilo para abas da PM */
        .nav-link-pm {
            color: #198754 !important;
        }
        .nav-link-pm.active {
            color: #fff !important;
            background-color: #198754 !important;
            border-color: #198754 !important;
        }
        .nav-link-pm:hover:not(.active) {
            color: #157347 !important;
        }
    </style>

    <!-- Previne o flash branco lendo o tema antes do render -->
    <script>
        if (localStorage.getItem('sisdp_theme') === 'dark') {
            document.documentElement.setAttribute('data-bs-theme', 'dark');
        }
    </script>
</head>

<body>
    <!-- Menu Lateral -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h5><i class="bi bi-menu-button-wide"></i> Menu do Sistema</h5>
            @php
                // Verifica permissões do usuário
                $userPermissions = Auth::user()->permissions ?? [];
                $hasMenuAccess = isset($userPermissions['menu_lateral']) ? $userPermissions['menu_lateral'] : true;
                $canApreensao = isset($userPermissions['apreensao']) ? $userPermissions['apreensao'] : true;
                $canAdministrativo = isset($userPermissions['administrativo']) ? $userPermissions['administrativo'] : true;
                $canCelular = isset($userPermissions['celular']) ? $userPermissions['celular'] : true;
                $canVeiculo = isset($userPermissions['veiculo']) ? $userPermissions['veiculo'] : true;
                $canIntimacao = isset($userPermissions['intimacao']) ? $userPermissions['intimacao'] : true;
                $canApreensaoOutros = isset($userPermissions['apreensao_outros']) ? $userPermissions['apreensao_outros'] : true;
                $canApfd = isset($userPermissions['apfd']) ? $userPermissions['apfd'] : true;
                $canAuditoriaChips = isset($userPermissions['auditoria_chips']) ? $userPermissions['auditoria_chips'] : false;
                $canInfopol = isset($userPermissions['infopol']) ? $userPermissions['infopol'] : true;
                $canRelatorios = isset($userPermissions['relatorios']) ? $userPermissions['relatorios'] : true;
            @endphp
            @if(!$hasMenuAccess)
                <!-- <span class="access-indicator">Acesso Restrito</span> -->
            @endif
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('geral') }}">
                    <i class="bi bi-house-door-fill"></i> Home
                </a>
            </li>

            <!-- NOVO ITEM: Módulo APFD -->
            @if($hasMenuAccess && $canApfd)
                <li class="{{ !$hasMenuAccess ? 'menu-disabled' : '' }}">
                    <a href="{{ route('inicio') }}"
                        class="{{ !$hasMenuAccess ? 'permission-tooltip' : '' }}">
                        <i class="bi bi-file-earmark-text"></i> Módulo APFD | IP
                    </a>
                </li>
            @endif

            @if($hasMenuAccess && $canAdministrativo)
                <li>
                    <a href="{{ route('administrativo.index') }}" target="_blank">
                        <i class="bi bi-archive"></i> Administrativo
                    </a>
                </li>
            @endif

            @if($hasMenuAccess && $canApreensao && ($canCelular || $canVeiculo))
                <li>
                    <div class="sidebar-group-card">
                        <button class="menu-toggle" type="button" onclick="toggleSubmenu('apreensao-submenu')">
                            <span><i class="bi bi-bag-check"></i>Apreensão</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <ul class="sidebar-submenu" id="apreensao-submenu">
                            @if($canCelular)
                                <li><a href="{{ route('celular') }}" target="_blank"><i class="bi bi-phone"></i>Celulares</a>
                                </li>
                            @endif
                            @if($canVeiculo)
                                <li><a href="{{ route('veiculo') }}" target="_blank"><i
                                            class="bi bi-car-front"></i>Veículos</a></li>
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
            <!-- NOVO MENU RELATÓRIOS (Dropdown) -->
            @if($hasMenuAccess && $canRelatorios)
            <li class="nav-item">
                <a class="nav-link collapsed" href="#submenuRelatorios" data-bs-toggle="collapse" 
                   aria-expanded="false" role="button">
                    <i class="bi bi-file-earmark-bar-graph-fill"></i>Relatórios
                    <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
                </a>
                <div class="collapse" id="submenuRelatorios">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('/relatorios/procedimentos') }}">
                                <i class="bi bi-list-check"></i>Procedimentos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('consulta.pessoa.index') }}">
                                <i class="bi bi-person-bounding-box"></i>Antecedentes
                            </a>
                        </li>
                        
                        @if($canAuditoriaChips || (Auth::check() && Auth::user()->nivel_acesso === 'administrador'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('administrativo.auditoria_chips') }}" style="color: #f59e0b !important;">
                                <i class="bi bi-diagram-3"></i>Auditoria Chips
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>

            <!-- NOVO MENU SINCRONIZAÇÃO SDS -->
            @if($canInfopol)
            <li>
                <a href="{{ route('infopol.index') }}" target="_blank" style="color: #0dcaf0;">
                    <i class="bi bi-cloud-arrow-down"></i>Sincronização SDS
                </a>
            </li>
            @endif
            @endif

            <!-- BOTÃO ASSISTENTE IA -->
            <li class="mt-3 mb-2" style="padding: 0 15px;">
                <button type="button" class="btn w-100" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCopilot" aria-controls="offcanvasCopilot" style="background-color: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid rgba(255, 255, 255, 0.2); text-align: left; display: flex; align-items: center; padding: 12px 15px; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="bi bi-robot me-2" style="font-size: 1.2rem; color: #0dcaf0;"></i> 
                    <span style="font-weight: 500;">Assistente IA</span>
                </button>
            </li>


        </ul>
    </div>

    <!-- Botão para recolher/expandir menu em dispositivos móveis -->
    <button class="menu-collapse-btn" id="menuCollapseBtn">
        <i class="bi bi-list"></i>
    </button>
    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div class="container">

            <!-- Cabeçalho Profissional -->
            <div class="header-container position-relative">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h1 class="page-title">
                        <img src="{{ asset('images/police_avatar.ico') }}" alt="Logo" class="me-2">
                        SisDP - Módulo IP | APFD
                    </h1>
                </div>
                <div class="system-info">
                    <div class="system-date" id="currentDateTime">{{ date('d/m/Y H:i:s') }}</div>
                    <div class="system-user">Usuário: {{ Auth::user()->nome ?? 'Administrador' }}</div>
                </div>

                <!-- ✅ ÁREA DE NOTIFICAÇÕES (canto superior direito) -->
                <div class="position-absolute d-flex gap-2 align-items-center" style="top: 20px; right: 320px; z-index: 1000;">

                    <!-- Botão: Sugestões de Colaboradores (NOVO) -->
                    <button type="button" id="btnSugestoesPendentes" class="btn btn-warning shadow-sm d-none"
                            style="font-weight:600; border-radius:8px; padding:8px 16px; font-size:0.85rem;"
                            title="Sugestões de envolvidos aguardando sua aprovação">
                        <i class="bi bi-people-fill me-1"></i>
                        Sugestões
                        <span class="badge rounded-pill bg-danger ms-1" id="badgeSugestoes">0</span>
                    </button>

                    <!-- Botão: Procedimentos Pendentes (existente, restaurado) -->
                    <button type="button" id="btnAlertas" class="btn btn-danger shadow-sm"
                            style="display:none; font-weight:600; border-radius:8px; padding:8px 16px; font-size:0.85rem;
                                   background: linear-gradient(135deg, #dc3545, #9c2532); color:#fff; border:none;"
                            title="Procedimentos sem movimentação">
                        <i class="bi bi-bell-fill me-1"></i>
                        Pendências
                        <span class="badge rounded-pill bg-white text-dark ms-1" id="badgeAlertas">0</span>
                    </button>

                </div>
            </div>

            <!-- ABAS PRINCIPAIS - Para suportar abas dinâmicas -->
            <ul class="nav nav-tabs mt-3" id="abasPrincipais" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#aba-inicio" role="tab">Início</a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <div class="tab-pane fade show active" id="aba-inicio" role="tabpanel">
                    <!-- SUB-ABAS -->
                    <ul class="nav nav-tabs mt-3" id="subAbasInicio" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#dados" role="tab">APFD | IP</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#aba-apreensao" role="tab">Apreensão</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#documentos" role="tab">Dados
                                Complementares</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">
                        <!-- Dados Gerais -->
                        <div class="tab-pane fade show active" id="dados" role="tabpanel">
                            <form id="formInicio">
                                @csrf
                                <div class="row g-3 mb-3">
                                    <div class="col-md-2 pe-1">
                                        <input type="text" class="form-control" placeholder="DD/MM/AAAA" name="data"
                                            id="inputData" maxlength="10">
                                    </div>
                                    <div class="col-md-4 ps-0 pe-1">
                                        <input type="text" class="form-control" placeholder="Data Completa"
                                            name="data_comp" id="inputDataComp">
                                    </div>
                                    <div class="col-md-6 ps-0">
                                        <input type="text" class="form-control" placeholder="Data por Extenso"
                                            name="data_ext" id="inputDataExt">
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4"><input type="text" class="form-control" placeholder="IP"
                                            name="ip" id="inputIP"></div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="BOE" name="boe"
                                                id="inputBOE">
                                            <button class="btn btn-info" type="button" id="btnImportarBoe"
                                                title="Importar dados do BOE"><i class="bi bi-upload"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="BOE PM"
                                                name="boe_pm" id="inputBOEPM">
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6"><input type="text" class="form-control" placeholder="Delegado"
                                            name="delegado" id="inputDelegado"></div>
                                    <div class="col-md-6"><input type="text" class="form-control" placeholder="Escrivão"
                                            name="escrivao" id="inputEscrivao"></div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6"><input type="text" class="form-control"
                                            placeholder="Delegacia" name="delegacia" id="inputDelegacia"></div>
                                    <div class="col-md-6"><input type="text" class="form-control" placeholder="Cidade"
                                            name="cidade" id="inputCidade"></div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6"><input type="text" class="form-control"
                                            placeholder="Policial 1" name="policial_1" id="inputPolicial1"></div>
                                    <div class="col-md-6"><input type="text" class="form-control"
                                            placeholder="Policial 2" name="policial_2" id="inputPolicial2"></div>
                                </div>

                                <!-- Botões e Pesquisa Rápida de Documentos -->
                                <div class="button-group d-flex flex-wrap gap-2 mt-3 mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-action" id="btnNovo"><i
                                            class="bi bi-file-earmark-plus"></i> Novo</button>
                                    <button type="button" class="btn btn-sm btn-success btn-action" id="btnSalvar"><i
                                            class="bi bi-save"></i> Salvar</button>
                                    <button type="button" class="btn btn-sm btn-warning btn-action" id="btnEditar" disabled><i
                                            class="bi bi-pencil-square"></i> Editar</button>
                                    <button type="button" class="btn btn-sm btn-danger btn-action" id="btnExcluir" disabled><i
                                            class="bi bi-trash"></i> Excluir</button>
                                    <button type="button" class="btn btn-sm btn-secondary btn-action" id="btnLimpar"><i
                                            class="bi bi-x-circle"></i> Limpar</button>

                                    <!-- Campo de autocomplete para documentos - MESMO ESTILO DO CONDUTOR -->
                                    <div class="position-relative flex-grow-1"
                                        style="min-width: 200px; max-width: 500px;">
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="termoDocumentoInicio"
                                                placeholder="Digite o documento (ex: DECLARAÇÃO)...">
                                            <button type="button" class="btn btn-info" id="btnImprimirDocumentoInicio">
                                                <i class="bi bi-printer"></i> Imprimir
                                            </button>
                                        </div>
                                        <div class="list-group mt-1 position-absolute w-100"
                                            id="sugestoesDocumentosInicio" style="display: none; z-index: 1000;">
                                            <!-- Sugestões aparecerão aqui dinamicamente -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Seção de Envolvidos -->
                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Envolvidos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 mb-2">
                                            <!-- Condutor -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold">Condutor:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="inputCondutor"
                                                        placeholder="Adicionar condutor..." readonly>
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        id="btnAddCondutor">
                                                        <i class="bi bi-plus-circle"></i> Adicionar
                                                    </button>
                                                </div>
                                                <div id="chipsCondutores" class="d-flex flex-wrap gap-2 mt-2"></div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-2">
                                            <!-- Vítimas -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold">Vítimas:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="inputVitima"
                                                        placeholder="Adicionar vítima..." readonly>
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        id="btnAddVitima">
                                                        <i class="bi bi-plus-circle"></i> Adicionar
                                                    </button>
                                                </div>
                                                <div id="chipsVitimas" class="d-flex flex-wrap gap-2 mt-2"></div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-2">
                                            <!-- Autores -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold">Autores:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="inputAutor"
                                                        placeholder="Adicionar autor..." readonly>
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        id="btnAddAutor">
                                                        <i class="bi bi-plus-circle"></i> Adicionar
                                                    </button>
                                                </div>
                                                <div id="chipsAutores" class="d-flex flex-wrap gap-2 mt-2"></div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-2">
                                            <!-- Testemunhas -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold">Testemunhas:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="inputTestemunha"
                                                        placeholder="Adicionar testemunha..." readonly>
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        id="btnAddTestemunha">
                                                        <i class="bi bi-plus-circle"></i> Adicionar
                                                    </button>
                                                </div>
                                                <div id="chipsTestemunhas" class="d-flex flex-wrap gap-2 mt-2"></div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-2">
                                            <!-- Outros -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold">Outros:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="inputOutro"
                                                        placeholder="Adicionar outro..." readonly>
                                                    <button class="btn btn-outline-secondary" type="button"
                                                        id="btnAddOutro">
                                                        <i class="bi bi-plus-circle"></i> Adicionar
                                                    </button>
                                                </div>
                                                <div id="chipsOutros" class="d-flex flex-wrap gap-2 mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Fim da Seção de Envolvidos -->

                                <!-- Card de Pesquisa Profissional -->
                                <div class="card shadow-sm border-0 mb-4 mt-3 bg-light w-50">
                                    <div class="card-body p-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-filter"></i></span>
                                            <select class="form-select flex-grow-0 border-start-0" id="ddlFiltro" style="min-width: 90px; max-width: 150px;">
                                                <option value="BOE" selected>BOE</option>
                                                <option value="IP">IP</option>
                                            </select>
                                            <input type="text" class="form-control" id="txtPesquisa" placeholder="Digite o termo para pesquisa...">
                                            <button class="btn btn-primary px-4" type="button" id="btnPesquisar">
                                                <i class="bi bi-search"></i> Pesquisar
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card de Resultados -->
                                <div class="card shadow-sm border-0">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover align-middle mb-0" id="gridResultados">
                                                <thead class="bg-primary text-white">
                                                    <tr>
                                                        <th class="py-2 text-white" style="width: 14%;">BOE PC</th>
                                                        <th class="py-2 text-white" style="width: 14%;">BO PM</th>
                                                        <th class="py-2 text-white" style="width: 15%;">IP</th>
                                                        <th class="py-2 text-center text-white" style="width: 14%;">STATUS</th>
                                                        <th class="py-2 text-center text-white" style="width: 15%;">PRIORIDADE</th>
                                                        <th class="py-2 text-center text-white" style="width: 14%;">RESPONSÁVEL</th>
                                                        <th class="py-2 text-center text-white" style="width: 14%;">AÇÕES</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="7" class="text-center py-3 text-muted">
                                                            <i class="bi bi-search display-6 d-block mb-2 opacity-25" style="font-size: 2rem;"></i>
                                                            Nenhum registro encontrado. Realize uma pesquisa.
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Dados Complementares -->
                        <div class="tab-pane fade" id="documentos" role="tabpanel">
                            <form id="formDocumentos">
                                <!-- Primeira linha -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <label for="inputDataFato" class="form-label">Data do Fato</label>
                                        <input type="date" class="form-control" name="data_fato" id="inputDataFato">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="inputDataInstauracao" class="form-label">Data de Instauração</label>
                                        <input type="date" class="form-control" name="data_instauracao"
                                            id="inputDataInstauracao">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="inputHoraFato" class="form-label">Hora do Fato</label>
                                        <input type="time" class="form-control" name="hora_fato" id="inputHoraFato">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="inputStatus" class="form-label">Status</label>
                                        <select class="form-select" name="status" id="inputStatus">
                                            <option value="">Selecione o status</option>
                                            <option value="Em andamento">Em andamento</option>
                                            <option value="Concluído">Concluído</option>
                                            <option value="Arquivado">Arquivado</option>
                                            <option value="Remetido a Justiça">Remetido a Justiça</option>
                                            <option value="Parado">Parado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2" id="divDataStatus" style="display: none;">
                                        <label for="inputDataStatus" class="form-label text-primary fw-bold" title="Deixe em branco para usar a data de hoje.">Data do Status</label>
                                        <input type="date" class="form-control border-primary" name="data_status" id="inputDataStatus" title="Mude essa data apenas se o status ocorreu em um dia diferente de hoje, para contabilizar corretamente nos relatórios.">
                                    </div>
                                </div>

                                <!-- Segunda linha -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Meios Empregados"
                                            name="meios_empregados" id="inputMeiosEmpregados">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="Motivação" name="motivacao"
                                            id="inputMotivacao">
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" name="prioridade" id="inputPrioridade">
                                            <option value="">Selecione a Prioridade</option>
                                            <option value="ALTA PRIORIDADE" class="text-danger fw-bold">ALTA PRIORIDADE</option>
                                            <option value="MEDIA PRIORIDADE" class="fw-bold" style="color: #cf8a02;">MEDIA PRIORIDADE</option>
                                            <option value="BAIXA PRIORIDADE" class="text-success fw-bold">BAIXA PRIORIDADE</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Aqui vai Incidência Penal e Comarca -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" placeholder="Incidência Penal"
                                            name="incidencia_penal" id="inputIncidenciaPenal" required>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" placeholder="Comarca" name="comarca"
                                            id="inputComarca">
                                    </div>
                                </div>

                                <!-- Endereço do Fato -->
                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <input type="text" class="form-control" placeholder="Endereço do Fato"
                                            name="end_fato" id="inputEndFato">
                                    </div>
                                </div>

                                <!-- DP e CID -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" placeholder="DP Resp." name="dp_resp"
                                            id="inputDPResp">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" placeholder="CID Resp." name="cid_resp"
                                            id="inputCidResp">
                                    </div>
                                </div>

                                <!-- BEL e ESCR -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" placeholder="BEL Resp." name="bel_resp"
                                            id="inputBelResp">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" placeholder="ESCR Resp."
                                            name="escr_resp" id="inputEscrResp">
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Apreensão (Nova Aba) -->
                        <div class="tab-pane fade" id="aba-apreensao" role="tabpanel">
                            <form id="formApreensao">
                                <div class="card border-0 shadow-sm mt-2">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                        <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-2"></i>Itens Apreendidos</h6>
                                        <span class="badge bg-primary px-2 py-1" id="badgeContadorItens">0 itens detectados</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <textarea class="form-control" name="Apreensao" id="inputApreensao" rows="15"
                                                    placeholder="Aguardando processamento do BOE..."
                                                    style="border: 2px solid #adb5bd; border-radius: 12px; font-size: 1.15rem; padding: 15px; min-height: 400px; line-height: 1.5;"></textarea>
                                            </div>
                                        </div>
                                        <div class="mt-2 d-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="const ta = document.getElementById('inputApreensao'); ta.select(); document.execCommand('copy');">
                                                <i class="bi bi-clipboard"></i> Copiar Lista
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('inputApreensao').value = ''; document.getElementById('badgeContadorItens').innerText = '0 itens detectados';">
                                                <i class="bi bi-trash"></i> Limpar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Abas dinâmicas de pessoas (aparecem sob demanda) -->
                        <div class="tab-pane fade" id="tab-condutor" role="tabpanel">
                            @include('wf_condutor_apfd')
                        </div>
                        <div class="tab-pane fade" id="tab-vitima" role="tabpanel">
                            @include('wf_vitima1')
                        </div>
                        <div class="tab-pane fade" id="tab-testemunha" role="tabpanel">
                            @include('wf_testemunha1')
                        </div>
                        <div class="tab-pane fade" id="tab-autor" role="tabpanel">
                            @include('wf_autor1')
                        </div>
                        <div class="tab-pane fade" id="tab-outro" role="tabpanel">
                            @include('wf_outros')
                        </div>

                    </div> <!-- fecha tab-content das sub-abas -->
                </div> <!-- fecha tab-pane aba-inicio -->
            </div>

            <!-- ============ MODAIS DO wf_modal ============ -->

            <!-- Modais Estáticos Removidos (Usando core.js dinâmico) -->

            <!-- ✅ MODAL DE SELEÇÃO DE VÍTIMAS PARA PERÍCIA -->
            <div class="modal fade" id="modalSelecaoVitimas" tabindex="-1" aria-labelledby="modalSelecaoVitimasLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold" id="modalSelecaoVitimasLabel">
                                <i class="bi bi-list-check me-2"></i>
                                Selecione as Vítimas
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Selecione quais vítimas devem constar no ofício de <strong>Perícia</strong>:</p>
                            <form id="formSelecaoVitimas">
                                <div id="listaVitimasCheckboxes" class="d-flex flex-column gap-2">
                                    <!-- Checkboxes preenchidos via JS -->
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary fw-bold px-4" id="btnConfirmarSelecaoVitimas">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL DE SELEÇÃO PARA CONFRONTAÇÃO PAPILOSCÓPICA -->
            <div class="modal fade" id="modalSelecaoConfrontacao" tabindex="-1" aria-labelledby="modalSelecaoConfrontacaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold" id="modalSelecaoConfrontacaoLabel">
                                <i class="bi bi-person-lines-fill me-2"></i>
                                Autores e Vítimas
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Selecione quais <strong>Autores</strong> e <strong>Vítimas</strong> constarão no ofício:</p>
                            <form id="formSelecaoConfrontacao">
                                <h6 class="fw-bold mt-2 mb-2 text-primary border-bottom pb-1">Autores/Suspeitos</h6>
                                <div id="listaAutoresConfrontacao" class="d-flex flex-column gap-2 mb-4">
                                    <!-- Checkboxes preenchidos via JS -->
                                </div>
                                <h6 class="fw-bold mb-2 text-danger border-bottom pb-1">Vítimas</h6>
                                <div id="listaVitimasConfrontacao" class="d-flex flex-column gap-2">
                                    <!-- Checkboxes preenchidos via JS -->
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary fw-bold px-4" id="btnConfirmarSelecaoConfrontacao">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL DE SELEÇÃO PARA ENTORPECENTES -->
            <div class="modal fade" id="modalSelecaoEntorpecentes" tabindex="-1" aria-labelledby="modalSelecaoEntorpecentesLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title fw-bold" id="modalSelecaoEntorpecentesLabel">
                                <i class="bi bi-capsule-pill me-2"></i>
                                Seleção de Entorpecentes
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Selecione quais <strong>substâncias análogas</strong> foram apreendidas e insira as respectivas quantidades:</p>
                            <form id="formSelecaoEntorpecentes">
                                <div class="d-flex flex-column gap-3 mb-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check mb-0" style="min-width: 130px;">
                                            <input class="form-check-input entorpecente-checkbox" type="checkbox" value="MACONHA" id="chkMaconha" style="transform: scale(1.3); margin-right: 8px;">
                                            <label class="form-check-label fs-6 fw-bold" for="chkMaconha">MACONHA</label>
                                        </div>
                                        <input type="text" class="form-control form-control-sm qtd-input w-100" id="qtdMaconha" placeholder="Qtd. (ex: 15, 2.5)" disabled>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check mb-0" style="min-width: 130px;">
                                            <input class="form-check-input entorpecente-checkbox" type="checkbox" value="COCAÍNA" id="chkCocaina" style="transform: scale(1.3); margin-right: 8px;">
                                            <label class="form-check-label fs-6 fw-bold" for="chkCocaina">COCAÍNA</label>
                                        </div>
                                        <input type="text" class="form-control form-control-sm qtd-input w-100" id="qtdCocaina" placeholder="Qtd. (ex: 15, 2.5)" disabled>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check mb-0" style="min-width: 130px;">
                                            <input class="form-check-input entorpecente-checkbox" type="checkbox" value="CRACK" id="chkCrack" style="transform: scale(1.3); margin-right: 8px;">
                                            <label class="form-check-label fs-6 fw-bold" for="chkCrack">CRACK</label>
                                        </div>
                                        <input type="text" class="form-control form-control-sm qtd-input w-50" id="qtdCrackPedras" placeholder="Qtd. pedras (ex: 12)" disabled>
                                        <input type="text" class="form-control form-control-sm qtd-input w-50" id="qtdCrackPeso" placeholder="Peso (ex: 5g)" disabled>
                                    </div>
                                    
                                    <hr class="mt-2 mb-2">
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chkOutrasDrogas" style="transform: scale(1.3); margin-right: 8px;">
                                        <label class="form-check-label fs-6 fw-bold" for="chkOutrasDrogas">OUTRAS (Especificar):</label>
                                    </div>
                                    <div class="d-flex gap-2 mt-1">
                                        <input type="text" class="form-control flex-grow-1" id="inputOutrasDrogas" placeholder="Ex: HAXIXE, LSD, MDMA, SKUNK..." disabled>
                                        <input type="text" class="form-control w-25" id="qtdOutras" placeholder="Qtd..." disabled>
                                    </div>
                                    
                                    <hr class="mt-3 mb-2" id="hrAutuadosEntorpecentes">
                                    
                                    <div class="form-group mt-2" id="grupoAutuadosEntorpecentes">
                                        <label class="fw-bold mb-2 text-primary"><i class="bi bi-people-fill me-1"></i> Autuado(s) / Envolvido(s) para a Perícia Definitiva:</label>
                                        
                                        <!-- Container para checkboxes dinâmicos -->
                                        <div id="containerCheckboxesAutoresEntorpecentes" class="mb-3 d-flex flex-column gap-2 ms-1">
                                            <!-- Checkboxes injetados pelo JS -->
                                        </div>

                                        <input type="text" class="form-control border-primary" id="inputAutuadosManuais" placeholder="Adicionar manualmente (Ex: João da Silva)">
                                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Selecione os envolvidos ou digite manualmente (opcional).</small>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarSelecaoEntorpecentes">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA PERÍCIA DE ARROMBAMENTO -->
            <div class="modal fade" id="modalArrombamento" tabindex="-1" aria-labelledby="modalArrombamentoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title fw-bold" id="modalArrombamentoLabel">
                                <i class="bi bi-door-open-fill me-2"></i>
                                Perícia de Arrombamento
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Confirme o local exato onde ocorreu o arrombamento para gerar o laudo e a portaria:</p>
                            <form id="formArrombamento">
                                <div class="mb-3">
                                    <label for="inputLocalArrombamento" class="form-label fw-bold text-primary">Local do Fato / Arrombamento:</label>
                                    <input type="text" class="form-control border-primary" id="inputLocalArrombamento" placeholder="Ex: Rua das Flores, 123, Centro">
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>O endereço puxado do BOE pode ser ajustado se necessário.</small>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarArrombamento">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA OFÍCIO DE PRONTUÁRIO HOSPITALAR -->
            <div class="modal fade" id="modalProntuarioHospitalar" tabindex="-1" aria-labelledby="modalProntuarioHospitalarLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title fw-bold" id="modalProntuarioHospitalarLabel">
                                <i class="bi bi-hospital me-2"></i>
                                Ofício de Prontuário Hospitalar
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Preencha os dados necessários para gerar o ofício de requisição médica:</p>
                            <form id="formProntuarioHospitalar">
                                <div class="mb-3">
                                    <label for="inputHospitalNome" class="form-label fw-bold text-primary">Nome do Hospital/Maternidade:</label>
                                    <input type="text" class="form-control border-primary" id="inputHospitalNome" placeholder="Ex: Hospital Regional...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-primary">Vítima Atendida:</label>
                                    <div id="containerRadiosVitimasProntuario" class="d-flex flex-column gap-2 mb-2 px-2">
                                        <!-- Radios gerados via JS -->
                                    </div>
                                    <input type="text" class="form-control border-secondary" id="inputVitimaManualProntuario" placeholder="Ou digite o nome manualmente se não estiver na lista">
                                </div>
                                <div class="mb-3">
                                    <label for="inputMotivoInternamento" class="form-label fw-bold text-primary">Motivo do Atendimento (Fato):</label>
                                    <input type="text" class="form-control border-primary" id="inputMotivoInternamento" placeholder="Ex: disparos de arma de fogo, acidente de trânsito...">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarProntuarioHospitalar">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA PERÍCIA DOCUMENTOSCÓPICA -->
            <div class="modal fade" id="modalDocumentoscopica" tabindex="-1" aria-labelledby="modalDocumentoscopicaLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title fw-bold" id="modalDocumentoscopicaLabel">
                                <i class="bi bi-search me-2"></i>
                                Perícia Documentoscópica
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Informe os detalhes para encaminhamento ao Instituto de Criminalística:</p>
                            <form id="formDocumentoscopica">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-primary">Autor / Investigado:</label>
                                    <div id="containerRadiosAutoresDoc" class="d-flex flex-column gap-2 mb-2 px-2">
                                        <!-- Radios gerados via JS -->
                                    </div>
                                    <input type="text" class="form-control border-secondary" id="inputAutorManualDoc" placeholder="Ou digite o nome manualmente se não estiver na lista">
                                </div>
                                <div class="mb-3">
                                    <label for="inputDocumentosPericiados" class="form-label fw-bold text-primary">Documentos a serem periciados:</label>
                                    <textarea class="form-control border-primary" id="inputDocumentosPericiados" rows="3" placeholder="Ex: 01 (um) atestado médico em nome de Fulano e o livro de registro do Hospital X"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarDocumentoscopica">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA PERÍCIA EM IMÓVEL/LOCAL (LUMINOL) -->
            <div class="modal fade" id="modalPericiaLocal" tabindex="-1" aria-labelledby="modalPericiaLocalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title fw-bold" id="modalPericiaLocalLabel">
                                <i class="bi bi-house-door-fill me-2"></i>
                                Perícia em Imóvel/Local (Luminol)
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Informe os detalhes para solicitar a perícia de local:</p>
                            <form id="formPericiaLocal">
                                <div class="mb-3">
                                    <label for="inputEnderecoImovel" class="form-label fw-bold text-primary">Endereço do Imóvel / Local:</label>
                                    <input type="text" class="form-control border-primary" id="inputEnderecoImovel" placeholder="Ex: Rua das Flores, 123, Centro">
                                </div>
                                <div class="mb-3">
                                    <label for="inputRelatoFatoLocal" class="form-label fw-bold text-primary">Relato do Fato a Investigar:</label>
                                    <textarea class="form-control border-primary" id="inputRelatoFatoLocal" rows="3" placeholder="Ex: a pessoa de João da Silva encontra-se desaparecida desde o dia 10..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarPericiaLocal">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA PERÍCIA DE INFORMÁTICA -->
            <div class="modal fade" id="modalPericiaInformatica" tabindex="-1" aria-labelledby="modalPericiaInformaticaLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-secondary text-white">
                            <h5 class="modal-title fw-bold" id="modalPericiaInformaticaLabel">
                                <i class="bi bi-laptop me-2"></i>
                                Perícia de Informática (Extração)
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Informe os detalhes para solicitar a perícia e extração de dados:</p>
                            <form id="formPericiaInformatica">
                                <div class="mb-3">
                                    <label for="inputAparelhosApreendidos" class="form-label fw-bold text-primary">Aparelhos/Objetos Apreendidos:</label>
                                    <input type="text" class="form-control border-primary" id="inputAparelhosApreendidos" placeholder="Ex: 01 aparelho celular marca Motorola, cor preta...">
                                </div>
                                <div class="mb-3">
                                    <label for="inputObjetivoExtracao" class="form-label fw-bold text-primary">O que deve ser extraído/periciado?</label>
                                    <textarea class="form-control border-primary" id="inputObjetivoExtracao" rows="3" placeholder="Ex: conversas de WhatsApp entre A e B, fotos da galeria, etc."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarPericiaInformatica">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA TERMO DE AUTORIZAÇÃO DE EXTRAÇÃO DE DADOS -->
            <div class="modal fade" id="modalTermoAutorizacao" tabindex="-1" aria-labelledby="modalTermoAutorizacaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title fw-bold" id="modalTermoAutorizacaoLabel">
                                <i class="bi bi-file-earmark-lock2-fill me-2"></i>
                                Termo de Autorização (Extração de Dados)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <p class="mb-3 text-muted">Selecione quem está autorizando e informe o dispositivo:</p>
                            <form id="formTermoAutorizacao">
                                <div class="mb-3">
                                    <label for="selectPessoaAutorizadora" class="form-label fw-bold text-dark">Pessoa que Autoriza (Envolvidos no B.O):</label>
                                    <select class="form-select border-warning" id="selectPessoaAutorizadora">
                                        <option value="" selected disabled>Selecione um envolvido...</option>
                                        <!-- Preenchido dinamicamente pelo JS com base nas vítimas, testemunhas, autores, condutor -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="inputDispositivoEletronico" class="form-label fw-bold text-dark">Dispositivo Eletrônico / Conta:</label>
                                    <textarea class="form-control border-warning" id="inputDispositivoEletronico" rows="2" placeholder="Ex: Aparelho celular Motorola, cor preta, nº (81) 99999-9999"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarTermoAutorizacao">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA OFÍCIO DE COMUNICAÇÃO A ADVOGADO -->
            <div class="modal fade" id="modalComunicaAdvogado" tabindex="-1" aria-labelledby="modalComunicaAdvogadoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title fw-bold" id="modalComunicaAdvogadoLabel">
                                <i class="bi bi-briefcase-fill me-2"></i>
                                Ofício de Comunicação a Advogado
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form id="formComunicaAdvogado">
                                <div class="mb-3">
                                    <label for="inputNomeAdvogado" class="form-label fw-bold text-dark">Nome do Advogado(a) / OAB:</label>
                                    <input type="text" class="form-control" id="inputNomeAdvogado" placeholder="Ex: Dr. Fulano de Tal - OAB/PE 12345">
                                </div>
                                <div class="mb-3">
                                    <label for="inputCidadeAdvogado" class="form-label fw-bold text-dark">Cidade/UF do Escritório:</label>
                                    <input type="text" class="form-control" id="inputCidadeAdvogado" placeholder="Ex: Recife - PE">
                                </div>
                                <hr>
                                <div class="mb-3">
                                    <label for="selectAtoProcessual" class="form-label fw-bold text-dark">Ato a ser acompanhado:</label>
                                    <select class="form-select" id="selectAtoProcessual">
                                        <option value="" selected disabled>Selecione o Ato...</option>
                                        <option value="INQUIRIÇÃO">Inquirição</option>
                                        <option value="REINQUIRIÇÃO">Reinquirição</option>
                                        <option value="REPRODUÇÃO SIMULADA DOS FATOS">Reprodução Simulada dos Fatos</option>
                                        <option value="OITIVA">Oitiva</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="selectClienteAdvogado" class="form-label fw-bold text-dark">Nome do Cliente (Envolvido):</label>
                                    <select class="form-select" id="selectClienteAdvogado">
                                        <option value="" selected disabled>Selecione o Cliente...</option>
                                        <!-- Preenchido dinamicamente pelo JS -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="inputDataHoraAto" class="form-label fw-bold text-dark">Data e Hora do Ato:</label>
                                    <input type="text" class="form-control" id="inputDataHoraAto" placeholder="Ex: 17 de maio de 2024 às 10:00h">
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarComunicaAdvogado">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA OFÍCIO DE ENCAMINHAR VEÍCULO CIRETRAN -->
            <div class="modal fade" id="modalEncaminharVeiculo" tabindex="-1" aria-labelledby="modalEncaminharVeiculoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold" id="modalEncaminharVeiculoLabel">
                                <i class="bi bi-car-front-fill me-2"></i>
                                Ofício de Encaminhamento de Veículo (CIRETRAN)
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form id="formEncaminharVeiculo">
                                <div class="mb-3">
                                    <label for="inputCiretranDestino" class="form-label fw-bold text-primary">CIRETRAN de Destino:</label>
                                    <input type="text" class="form-control border-primary" id="inputCiretranDestino" placeholder="Ex: 24ª CIRETRAN - PE">
                                </div>
                                <div class="mb-3">
                                    <label for="inputDadosVeiculoCiretran" class="form-label fw-bold text-primary">Dados do Veículo:</label>
                                    <textarea class="form-control border-primary" id="inputDadosVeiculoCiretran" rows="3" placeholder="Ex: UMA (01) MOTOCICLETA, HONDA CG 150 FAN, NA COR CINZA, CHASSI: PINADO, PLACA AFIXADA KHU-1643..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="inputJustificativaCiretran" class="form-label fw-bold text-primary">Justificativa / Contexto (opcional):</label>
                                    <textarea class="form-control border-primary" id="inputJustificativaCiretran" rows="3" placeholder="Ex: fora encontrada por policiais militares, onde na ocasião estava sendo pilotada pela pessoa de JOSÉ..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarEncaminharVeiculo">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA CERTIDAO DE MIDIAS EM NUVEM -->
            <div class="modal fade" id="modalCertidaoMidiasDrive" tabindex="-1" aria-labelledby="modalCertidaoMidiasDriveLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-info text-dark">
                            <h5 class="modal-title fw-bold" id="modalCertidaoMidiasDriveLabel">
                                <i class="bi bi-cloud-arrow-down-fill me-2"></i>
                                Certidão de Mídias em Nuvem (Drive)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form id="formCertidaoMidiasDrive">
                                <div class="mb-3">
                                    <label for="inputDescricaoMidias" class="form-label fw-bold text-dark">Descrição das Mídias/Vídeos:</label>
                                    <textarea class="form-control border-info" id="inputDescricaoMidias" rows="3" placeholder="Ex: as mídias/vídeos captados pelo sistema de videomonitoramento, os quais registram os fatos relacionados à presente investigação..."></textarea>
                                    
                                    <!-- Sugestões Rápidas (Dropdowns) -->
                                    <div class="mt-2 d-flex flex-wrap gap-2">
                                        <!-- Botão Locais Comuns -->
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-info text-dark dropdown-toggle fw-bold border-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-building"></i> Locais Comuns
                                            </button>
                                            <ul class="dropdown-menu shadow-sm border-info">
                                                <li><a class="dropdown-item" href="#" onclick="document.getElementById('inputDescricaoMidias').value='as mídias/vídeos captados pelo sistema de videomonitoramento do estabelecimento comercial, os quais registram os fatos'">🏢 Comércio</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="document.getElementById('inputDescricaoMidias').value='as imagens captadas pelo sistema de segurança da residência particular, as quais flagram a ação criminosa'">🏠 Residência</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="document.getElementById('inputDescricaoMidias').value='as gravações do circuito interno de câmeras da agência bancária'">🏦 Banco</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="document.getElementById('inputDescricaoMidias').value='os arquivos de vídeo registrados pelas câmeras de segurança do hospital/unidade de saúde'">🏥 Hospital</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="document.getElementById('inputDescricaoMidias').value='os vídeos capturados pelo sistema de monitoramento da instituição de ensino / escola'">🏫 Escola</a></li>
                                            </ul>
                                        </div>

                                        <!-- Botão Vítimas (Oculto por padrão, ativado pelo JS) -->
                                        <div class="dropdown" id="dropdownVitimasContainer" style="display:none;">
                                            <button class="btn btn-sm btn-outline-success dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-person-heart"></i> Fornecido por Vítima
                                            </button>
                                            <ul class="dropdown-menu shadow-sm border-success" id="listaDropdownVitimas">
                                            </ul>
                                        </div>

                                        <!-- Botão Testemunhas (Oculto por padrão, ativado pelo JS) -->
                                        <div class="dropdown" id="dropdownTestemunhasContainer" style="display:none;">
                                            <button class="btn btn-sm btn-outline-warning text-dark dropdown-toggle fw-bold border-warning" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-people-fill"></i> Fornecido por Testemunha
                                            </button>
                                            <ul class="dropdown-menu shadow-sm border-warning" id="listaDropdownTestemunhas">
                                            </ul>
                                        </div>

                                        <!-- Botão Solto WhatsApp -->
                                        <button class="btn btn-sm btn-outline-secondary fw-bold" type="button" onclick="document.getElementById('inputDescricaoMidias').value='os arquivos de mídia (vídeos/fotos) recebidos via aplicativo de mensagens e extraídos de dispositivo móvel'">
                                            <i class="bi bi-whatsapp text-success"></i> WhatsApp
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="inputLinkDrive" class="form-label fw-bold text-dark">Link de Acesso (Drive / Expresso):</label>
                                    <input type="url" class="form-control border-info" id="inputLinkDrive" placeholder="Ex: https://drive.expresso.pe.gov.br/s/abcd123">
                                    <div class="form-text">O sistema irá gerar um QR Code automaticamente a partir deste link.</div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarCertidaoMidiasDrive">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA CI DE REMESSA DE PROCEDIMENTOS -->
            <div class="modal fade" id="modalCIRemessaProcedimentos" tabindex="-1" aria-labelledby="modalCIRemessaProcedimentosLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title fw-bold" id="modalCIRemessaProcedimentosLabel">
                                <i class="bi bi-folder-symlink-fill me-2"></i>
                                C.I. de Remessa de Procedimentos
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form id="formCIRemessaProcedimentos">
                                <div class="mb-3">
                                    <label for="inputCIDestinatario" class="form-label fw-bold text-dark">Destinatário da C.I.:</label>
                                    <input type="text" class="form-control" id="inputCIDestinatario" placeholder="Ex: GESTOR(A) DA 20ª DESEC – AFOGADOS DA INGAZEIRA" value="GESTOR(A) DA 20ª DESEC – AFOGADOS DA INGAZEIRA">
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="selectCITipoProc" class="form-label fw-bold text-dark">Tipo de Procedimento:</label>
                                        <select class="form-select" id="selectCITipoProc">
                                            <option value="AUTO DE PRISÃO EM FLAGRANTE DELITO (APFD)" selected>APFD</option>
                                            <option value="TERMO CIRCUNSTANCIADO DE OCORRÊNCIA (TCO)">TCO</option>
                                            <option value="BOLETIM DE OCORRÊNCIA CIRCUNSTANCIADO (BOC)">BOC</option>
                                            <option value="PORTARIA (INQUÉRITO POLICIAL)">Inquérito (Portaria)</option>
                                            <option value="PROCEDIMENTO POLICIAL">Outro</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="checkCIFianca" role="switch">
                                    <label class="form-check-label fw-bold text-dark" for="checkCIFianca">Houve pagamento de Fiança?</label>
                                </div>
                                <div id="containerCIFianca" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="inputCIFiancaValor" class="form-label fw-bold text-dark">Valor (R$):</label>
                                            <input type="text" class="form-control" id="inputCIFiancaValor" placeholder="Ex: 1.412,00">
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label for="inputCIFiancaExtenso" class="form-label fw-bold text-dark">Valor por Extenso:</label>
                                            <input type="text" class="form-control" id="inputCIFiancaExtenso" placeholder="Ex: mil, quatrocentos e doze reais">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarCIRemessaProcedimentos">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA CI DE REMESSA DE OBJETOS E DOCUMENTOS -->
            <div class="modal fade" id="modalCIRemessaObjetosDocumentos" tabindex="-1" aria-labelledby="modalCIRemessaObjetosDocumentosLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title fw-bold" id="modalCIRemessaObjetosDocumentosLabel">
                                <i class="bi bi-box-seam-fill me-2"></i>
                                C.I. de Remessa de Objetos / Documentos
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form id="formCIRemessaObjetosDocumentos">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="inputCIDestinatarioRemessa" class="form-label fw-bold text-dark">Destinatário da C.I.:</label>
                                        <input type="text" class="form-control" id="inputCIDestinatarioRemessa" placeholder="Ex: GESTOR(A) DA 20ª DESEC – AFOGADOS DA INGAZEIRA" value="GESTOR(A) DA 20ª DESEC – AFOGADOS DA INGAZEIRA">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="selectCITipoRemessa" class="form-label fw-bold text-dark">O que será enviado?</label>
                                        <select class="form-select" id="selectCITipoRemessa">
                                            <option value="OBJETOS" selected>Objetos / Apreensões</option>
                                            <option value="DOCUMENTOS">Documentos</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="containerRemessaObjetos">
                                    <div class="mb-3">
                                        <label for="selectCITipoProcRemessa" class="form-label fw-bold text-dark">Procedimento Referente aos Objetos:</label>
                                        <select class="form-select" id="selectCITipoProcRemessa">
                                            <option value="AUTO DE PRISÃO EM FLAGRANTE DELITO (APFD)" selected>APFD</option>
                                            <option value="TERMO CIRCUNSTANCIADO DE OCORRÊNCIA (TCO)">TCO</option>
                                            <option value="BOLETIM DE OCORRÊNCIA CIRCUNSTANCIADO (BOC)">BOC</option>
                                            <option value="PORTARIA (INQUÉRITO POLICIAL)">Inquérito (Portaria)</option>
                                            <option value="PROCEDIMENTO POLICIAL">Outro</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="containerRemessaDocumentos" style="display: none;">
                                    <div class="mb-3">
                                        <label for="inputCIOrigemDocs" class="form-label fw-bold text-dark">Documentos foram enviados a esta delegacia por quem?</label>
                                        <input type="text" class="form-control" id="inputCIOrigemDocs" placeholder="Ex: Polícia Militar, Justiça Eleitoral, etc...">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="inputCIListaItens" class="form-label fw-bold text-dark">Lista de Itens (Objetos ou Documentos):</label>
                                    <textarea class="form-control" id="inputCIListaItens" rows="4" placeholder="Ex:&#10;- 01 (um) Aparelho Celular marca Samsung...&#10;- 01 (um) Revólver calibre 38..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnConfirmarCIRemessaObjetosDocumentos">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA CI ADMINISTRATIVA / GENÉRICA -->
            <div class="modal fade" id="modalCIGenerica" tabindex="-1" aria-labelledby="modalCIGenericaLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalCIGenericaLabel">
                                <i class="fas fa-file-alt me-2"></i> C.I. Administrativa / Genérica
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="inputCIGenericaDestinatario" class="form-label fw-bold text-dark">Destinatário da C.I.:</label>
                                            <input type="text" class="form-control" id="inputCIGenericaDestinatario" placeholder="Ex: GESTOR(A) DA 20ª DESEC – AFOGADOS DA INGAZEIRA" value="GESTOR(A) DA 20ª DESEC – AFOGADOS DA INGAZEIRA">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="inputCIGenericaAssunto" class="form-label fw-bold text-dark">Assunto (Opcional):</label>
                                            <input type="text" class="form-control" id="inputCIGenericaAssunto" placeholder="Ex: Comunicação de Fato, Solicitação de Férias, etc.">
                                            <div class="mt-2">
                                                <span class="badge bg-secondary cursor-pointer me-1" onclick="$('#inputCIGenericaAssunto').val('COMUNICAÇÃO DE LOCAL DE CRIME')">Comunicação de Local de Crime</span>
                                                <span class="badge bg-secondary cursor-pointer me-1" onclick="$('#inputCIGenericaAssunto').val('ENTREGA DE OBJETO APREENDIDO')">Entrega de Objeto</span>
                                                <span class="badge bg-secondary cursor-pointer me-1" onclick="$('#inputCIGenericaAssunto').val('SOLICITAÇÃO DE MANUTENÇÃO')">Manutenção</span>
                                                <span class="badge bg-secondary cursor-pointer me-1" onclick="$('#inputCIGenericaAssunto').val('COMUNICAÇÃO DE AFASTAMENTO')">Afastamento</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="inputCIGenericaCorpo" class="form-label fw-bold text-dark">Texto Base (Início do documento):</label>
                                            <textarea class="form-control" id="inputCIGenericaCorpo" rows="4">Cumprimentando-o(a) cordialmente, sirvo-me do presente para informar/solicitar que...</textarea>
                                            <small class="text-muted">Você poderá formatar todo o texto no editor profissional logo em seguida.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarCIGenerica">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA CI DE ENCAMINHAMENTO AO IITB -->
            <div class="modal fade" id="modalCIEncaminhamentoIITB" tabindex="-1" aria-labelledby="modalCIEncaminhamentoIITBLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalCIEncaminhamentoIITBLabel">
                                <i class="fas fa-fingerprint me-2"></i> C.I. de Encaminhamento ao IITB
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-check me-2 text-primary"></i>Selecione a pessoa a ser encaminhada:</h6>
                                </div>
                                <div class="card-body">
                                    <div id="containerRadiosPessoasIITB" class="row">
                                        <!-- Radios preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <div class="mt-3">
                                        <label for="inputPessoaManualIITB" class="form-label fw-bold text-dark">Ou digite o nome manualmente (se não estiver listado):</label>
                                        <input type="text" class="form-control" id="inputPessoaManualIITB" placeholder="Nome completo da pessoa...">
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <label for="inputQualificacaoIITB" class="form-label fw-bold text-dark">Qualificação (Opcional):</label>
                                    <input type="text" class="form-control" id="inputQualificacaoIITB" placeholder="Ex: brasileiro, solteiro, RG nº..., nascido aos...">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarCIEncaminhamentoIITB">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA OFÍCIO BANCO / INSTITUIÇÃO FINANCEIRA -->
            <div class="modal fade" id="modalOficioBanco" tabindex="-1" aria-labelledby="modalOficioBancoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalOficioBancoLabel">
                                <i class="fas fa-university me-2"></i> Ofício para Instituição Financeira / Banco
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label for="selectBancoNome" class="form-label fw-bold text-dark">Banco / Instituição Financeira:</label>
                                            <select class="form-select" id="selectBancoNome">
                                                <option value="BANCO DO BRASIL" selected>Banco do Brasil</option>
                                                <option value="CAIXA ECONÔMICA FEDERAL">Caixa Econômica Federal</option>
                                                <option value="BRADESCO">Bradesco</option>
                                                <option value="ITAÚ">Itaú</option>
                                                <option value="SANTANDER">Santander</option>
                                                <option value="SICREDI">Sicredi</option>
                                                <option value="SICOOB">Sicoob</option>
                                                <option value="BANCO DO NORDESTE (BNB)">Banco do Nordeste (BNB)</option>
                                                <option value="OUTRO">Outro (Digitar manualmente no editor)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="inputBancoAgencia" class="form-label fw-bold text-dark">Agência:</label>
                                            <input type="text" class="form-control" id="inputBancoAgencia" placeholder="Ex: Afogados da Ingazeira">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label fw-bold text-dark d-block">Tipo de Solicitação:</label>
                                            
                                            <div class="form-check form-check-inline mt-2">
                                                <input class="form-check-input" type="radio" name="radioBancoTipoSolicitacao" id="radioBancoImagens" value="IMAGENS" checked>
                                                <label class="form-check-label fw-bold" for="radioBancoImagens">Imagens de Câmeras (CFTV)</label>
                                            </div>
                                            <div class="form-check form-check-inline mt-2">
                                                <input class="form-check-input" type="radio" name="radioBancoTipoSolicitacao" id="radioBancoDocumentos" value="DOCUMENTOS">
                                                <label class="form-check-label fw-bold" for="radioBancoDocumentos">Documentos (Extratos, Contratos, etc.)</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row" id="containerBancoDetalhes" style="display: none;">
                                        <div class="col-md-12 mb-3">
                                            <label for="inputBancoDetalhes" class="form-label fw-bold text-dark">Detalhes da Solicitação:</label>
                                            <textarea class="form-control" id="inputBancoDetalhes" rows="3" placeholder="Ex: Extrato bancário referente ao período de XX/XX a XX/XX da conta número..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarOficioBanco">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA OFÍCIO SOLICITANDO CERTIDÃO DE ÓBITO -->
            <div class="modal fade" id="modalOficioCertidaoObito" tabindex="-1" aria-labelledby="modalOficioCertidaoObitoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalOficioCertidaoObitoLabel">
                                <i class="fas fa-file-contract me-2"></i> Ofício - Certidão de Óbito
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-injured me-2 text-primary"></i>Selecione a Vítima Falecida:</h6>
                                </div>
                                <div class="card-body">
                                    <div id="containerRadiosVitimasObito" class="row">
                                        <!-- Radios preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <div class="mt-3">
                                        <label for="inputVitimaManualObito" class="form-label fw-bold text-dark">Ou digite o nome manualmente (se não estiver listada):</label>
                                        <input type="text" class="form-control" id="inputVitimaManualObito" placeholder="Nome completo da vítima...">
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <label for="inputCartorioNome" class="form-label fw-bold text-dark">Nome do Cartório do Registro Civil:</label>
                                    <input type="text" class="form-control" id="inputCartorioNome" placeholder="Ex: Cartório do Registro Civil das Pessoas Naturais">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarOficioCertidaoObito">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA OFÍCIO DE ENCAMINHAMENTO DE CADÁVER AO IML -->
            <div class="modal fade" id="modalOficioIML" tabindex="-1" aria-labelledby="modalOficioIMLLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalOficioIMLLabel">
                                <i class="fas fa-ambulance me-2"></i> Ofício - Encaminhamento ao IML
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-injured me-2 text-primary"></i>Selecione o Cadáver (Vítima):</h6>
                                </div>
                                <div class="card-body">
                                    <div id="containerRadiosVitimasIML" class="row">
                                        <!-- Radios preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <div class="mt-3">
                                        <label for="inputVitimaManualIML" class="form-label fw-bold text-dark">Ou digite o nome manualmente (se não estiver listada):</label>
                                        <input type="text" class="form-control" id="inputVitimaManualIML" placeholder="Nome do cadáver / Não identificado...">
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="selectCidadeIML" class="form-label fw-bold text-dark">Cidade do IML:</label>
                                            <select class="form-select" id="selectCidadeIML">
                                                <option value="CARUARU-PE" selected>Caruaru-PE</option>
                                                <option value="PETROLINA-PE">Petrolina-PE</option>
                                                <option value="RECIFE-PE">Recife-PE</option>
                                                <option value="SALGUEIRO-PE">Salgueiro-PE</option>
                                                <option value="OUTRO">Outra Cidade (Digitar no editor)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="inputIMLCondutor" class="form-label fw-bold text-dark">Cadáver encaminhado por: (Opcional)</label>
                                            <input type="text" class="form-control" id="inputIMLCondutor" placeholder="Ex: Funerária XYZ, GATI, Policiais Militares...">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <label for="inputIMLLocalFato" class="form-label fw-bold text-dark">Local do Óbito / Encontro do Cadáver:</label>
                                            <input type="text" class="form-control" id="inputIMLLocalFato" placeholder="Será preenchido automaticamente com o Endereço do Fato">
                                        </div>
                                    </div>
                                    
                                    <hr class="my-3">
                                    
                                    <div class="row">
                                        <div class="col-12 mb-2">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="checkIMLAutorizarFamiliar">
                                                <label class="form-check-label fw-bold text-primary" for="checkIMLAutorizarFamiliar">
                                                    Adicionar autorização para familiar/pessoa liberar o corpo
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row bg-light rounded p-3 mt-2 border" id="containerIMLAutorizacao" style="display: none;">
                                        <div class="col-md-5 mb-2">
                                            <label for="inputIMLFamiliarNome" class="form-label fw-bold text-dark">Nome do Familiar/Pessoa:</label>
                                            <input type="text" class="form-control" id="inputIMLFamiliarNome" placeholder="Ex: João da Silva">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for="inputIMLFamiliarParentesco" class="form-label fw-bold text-dark">Grau de Parentesco:</label>
                                            <input type="text" class="form-control" id="inputIMLFamiliarParentesco" placeholder="Ex: Pai, Tio, Esposa...">
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label for="inputIMLFamiliarRG" class="form-label fw-bold text-dark">RG / CPF:</label>
                                            <input type="text" class="form-control" id="inputIMLFamiliarRG" placeholder="Ex: 1234567 SDS/PE">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarOficioIML">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA OFÍCIO REQUISITANDO PM -->
            <div class="modal fade" id="modalOficioPM" tabindex="-1" aria-labelledby="modalOficioPMLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalOficioPMLabel">
                                <i class="fas fa-shield-alt me-2"></i> Ofício - Requisição de PM
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i>Selecione os Policiais a serem requisitados:</h6>
                                </div>
                                <div class="card-body">
                                    <div id="containerCheckboxesPM" class="row">
                                        <!-- Checkboxes preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <div class="mt-3">
                                        <label for="inputPMManual" class="form-label fw-bold text-dark">Adicionar outro Policial manualmente:</label>
                                        <textarea class="form-control" id="inputPMManual" rows="2" placeholder="Ex: SD PM Fulano de Tal, Matrícula 12345-6..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body p-4">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="selectBatalhaoPM" class="form-label fw-bold text-dark">Batalhão / Companhia / OPM:</label>
                                            <select class="form-select" id="selectBatalhaoPM">
                                                <option value="23º BATALHÃO DE POLÍCIA MILITAR" selected>23º BPM - Afogados da Ingazeira</option>
                                                <option value="3º BATALHÃO DE POLÍCIA MILITAR">3º BPM - Arcoverde</option>
                                                <option value="15º BATALHÃO DE POLÍCIA MILITAR">15º BPM - Belo Jardim</option>
                                                <option value="BEPI / CIOSAC">BEPI / CIOSAC</option>
                                                <option value="BPRV - BATALHÃO DE POLÍCIA RODOVIÁRIA">BPRv</option>
                                                <option value="OUTRO">Outro (Digitar no editor)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="inputPMData" class="form-label fw-bold text-dark">Data da Apresentação:</label>
                                            <input type="text" class="form-control" id="inputPMData" placeholder="Ex: 15/10/2024">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label for="inputPMHora" class="form-label fw-bold text-dark">Hora da Apresentação:</label>
                                            <input type="text" class="form-control" id="inputPMHora" placeholder="Ex: 09h00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarOficioPM">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA CERTIDÃO DE COMPARECIMENTO -->
            <div class="modal fade" id="modalCertidaoComparecimento" tabindex="-1" aria-labelledby="modalCertidaoComparecimentoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalCertidaoComparecimentoLabel">
                                <i class="fas fa-stamp me-2"></i> Certidão de Comparecimento
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-check me-2 text-primary"></i>Selecione quem compareceu:</h6>
                                </div>
                                <div class="card-body">
                                    <div id="containerRadiosComparecimento" class="row">
                                        <!-- Radios preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <div class="mt-3">
                                        <label for="inputComparecimentoManual" class="form-label fw-bold text-dark">Ou digite o nome manualmente:</label>
                                        <input type="text" class="form-control" id="inputComparecimentoManual" placeholder="Nome completo da pessoa...">
                                    </div>
                                    <div class="mt-2">
                                        <label for="inputComparecimentoDoc" class="form-label fw-bold text-dark">Documento de Identificação (RG, CPF, OAB, etc.): (Opcional)</label>
                                        <input type="text" class="form-control" id="inputComparecimentoDoc" placeholder="Ex: RG 123456 SDS/PE">
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="inputComparecimentoData" class="form-label fw-bold text-dark">Data do Comparecimento:</label>
                                            <input type="text" class="form-control" id="inputComparecimentoData" placeholder="Ex: 15/10/2024">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="inputComparecimentoHoraChegada" class="form-label fw-bold text-dark">Hora de Chegada:</label>
                                            <input type="text" class="form-control" id="inputComparecimentoHoraChegada" placeholder="Ex: 09h00">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="inputComparecimentoHoraSaida" class="form-label fw-bold text-dark">Hora de Saída:</label>
                                            <input type="text" class="form-control" id="inputComparecimentoHoraSaida" placeholder="Ex: 11h30">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarCertidaoComparecimento">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA CERTIDÃO DE COMUNICAÇÃO À FAMÍLIA -->
            <div class="modal fade" id="modalCertidaoComunicaFamilia" tabindex="-1" aria-labelledby="modalCertidaoComunicaFamiliaLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalCertidaoComunicaFamiliaLabel">
                                <i class="fas fa-phone-volume me-2"></i> Certidão de Comunicação à Família
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-lock me-2 text-primary"></i>Selecione o Autuado/Preso:</h6>
                                </div>
                                <div class="card-body">
                                    <div id="containerRadiosAutuadosComunica" class="row">
                                        <!-- Radios preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <div class="mt-3">
                                        <label for="inputComunicaAutuadoManual" class="form-label fw-bold text-dark">Ou digite o nome do preso manualmente:</label>
                                        <input type="text" class="form-control" id="inputComunicaAutuadoManual" placeholder="Nome completo do autuado...">
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i>Dados do Familiar Comunicado:</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-7 mb-3">
                                            <label for="inputComunicaFamiliarNome" class="form-label fw-bold text-dark">Nome do Familiar/Amigo:</label>
                                            <input type="text" class="form-control" id="inputComunicaFamiliarNome" placeholder="Ex: Maria da Silva">
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label for="inputComunicaParentesco" class="form-label fw-bold text-dark">Grau de Parentesco:</label>
                                            <input type="text" class="form-control" id="inputComunicaParentesco" placeholder="Ex: Mãe, Pai, Tio, Esposa...">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <label class="form-label fw-bold text-dark d-block">Forma de Comunicação:</label>
                                            <div class="form-check form-check-inline mt-2">
                                                <input class="form-check-input" type="radio" name="radioComunicaMeio" id="radioComunicaTelefone" value="TELEFONE" checked>
                                                <label class="form-check-label" for="radioComunicaTelefone">Por Telefone</label>
                                            </div>
                                            <div class="form-check form-check-inline mt-2">
                                                <input class="form-check-input" type="radio" name="radioComunicaMeio" id="radioComunicaPessoal" value="PESSOAL">
                                                <label class="form-check-label" for="radioComunicaPessoal">Pessoalmente na Delegacia</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2" id="containerComunicaTelefone">
                                        <div class="col-md-6 mb-2">
                                            <label for="inputComunicaTelefone" class="form-label fw-bold text-dark">Número do Telefone:</label>
                                            <input type="text" class="form-control" id="inputComunicaTelefone" placeholder="Ex: (81) 99999-9999">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarCertidaoComunicaFamilia">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA RECIBO DE ENTREGA DE PRESO -->
            <div class="modal fade" id="modalReciboPreso" tabindex="-1" aria-labelledby="modalReciboPresoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalReciboPresoLabel">
                                <i class="fas fa-handshake me-2"></i> Recibo de Entrega de Preso
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-shield me-2 text-primary"></i>Selecione o Condutor (PM/Policial):</h6>
                                </div>
                                <div class="card-body">
                                    <div id="containerRadiosCondutoresRecibo" class="row">
                                        <!-- Radios preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <div class="mt-3">
                                        <label for="inputReciboCondutorManual" class="form-label fw-bold text-dark">Ou digite o nome do condutor manualmente:</label>
                                        <input type="text" class="form-control" id="inputReciboCondutorManual" placeholder="Ex: SD PM Fulano de Tal, Matrícula...">
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-lock me-2 text-primary"></i>Selecione o Preso Entregue:</h6>
                                </div>
                                <div class="card-body">
                                    <div id="containerRadiosAutuadosRecibo" class="row">
                                        <!-- Radios preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <div class="mt-3">
                                        <label for="inputReciboAutuadoManual" class="form-label fw-bold text-dark">Ou digite o nome do preso manualmente:</label>
                                        <input type="text" class="form-control" id="inputReciboAutuadoManual" placeholder="Nome completo do preso...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarReciboPreso">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA RECIBO DE PROCEDIMENTO -->
            <div class="modal fade" id="modalReciboProcedimento" tabindex="-1" aria-labelledby="modalReciboProcedimentoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalReciboProcedimentoLabel">
                                <i class="fas fa-folder-open me-2"></i> Recibo de Procedimento
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label for="selectDestinoProcedimento" class="form-label fw-bold text-dark">Destino do Procedimento:</label>
                                            <select class="form-select" id="selectDestinoProcedimento">
                                                <option value="AUDIÊNCIA DE CUSTÓDIA" selected>Audiência de Custódia</option>
                                                <option value="20ª DESEC">20ª DESEC</option>
                                                <option value="FÓRUM / PODER JUDICIÁRIO">Fórum / Poder Judiciário</option>
                                                <option value="MINISTÉRIO PÚBLICO">Ministério Público</option>
                                                <option value="OUTRO">Outro (Digitar no editor)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <label for="inputObsProcedimento" class="form-label fw-bold text-dark">Observações Adicionais (Opcional):</label>
                                            <textarea class="form-control" id="inputObsProcedimento" rows="3" placeholder="Ex: juntamente com 01 (um) aparelho celular e 01 (uma) faca apreendidos..."></textarea>
                                            <small class="text-muted">Isso será inserido na frase: "...referente ao Boletim de Ocorrência em epígrafe, [SUA OBSERVAÇÃO], para fins de encaminhamento ao..."</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarReciboProcedimento">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA AUTO DE DEPÓSITO FIEL -->
            <div class="modal fade" id="modalAutoDepositoFiel" tabindex="-1" aria-labelledby="modalAutoDepositoFielLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalAutoDepositoFielLabel">
                                <i class="fas fa-box-open me-2"></i> Auto de Depósito Fiel
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-check me-2 text-primary"></i>Selecione o Fiel Depositário:</h6>
                                    <small class="text-muted">A pessoa que ficará responsável pela guarda dos bens apreendidos.</small>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <select class="form-select" id="selectPessoaDepositoFiel">
                                                <option value="" selected disabled>Escolha uma pessoa envolvida...</option>
                                                <!-- Opções preenchidas pelo JS -->
                                            </select>
                                        </div>
                                    </div>
                                    <hr>
                                    <p class="fw-bold text-dark mb-2">Ou digite os dados do depositário manualmente:</p>
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <input type="text" class="form-control" id="inputDepositoNome" placeholder="Nome completo...">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="inputDepositoRG" placeholder="RG...">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control cpf-mask" id="inputDepositoCPF" placeholder="CPF...">
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <input type="text" class="form-control" id="inputDepositoEndereco" placeholder="Endereço completo...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Bens e Objetos Deixados em Depósito:</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <textarea class="form-control" id="inputDepositoObjetos" rows="4" placeholder="Descreva de forma minuciosa os bens, equipamentos, veículos ou objetos que ficarão sob a guarda do fiel depositário..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarAutoDepositoFiel">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA TERMO DE GUARDA E ENTREGA DE VEÍCULO -->
            <div class="modal fade" id="modalTermoGuardaVeiculo" tabindex="-1" aria-labelledby="modalTermoGuardaVeiculoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalTermoGuardaVeiculoLabel">
                                <i class="fas fa-car me-2"></i> Termo de Guarda e Entrega de Veículo
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-check me-2 text-primary"></i>Selecione o Recebedor / Fiel Depositário:</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <select class="form-select" id="selectPessoaGuardaVeiculo">
                                                <option value="" selected disabled>Escolha uma pessoa envolvida...</option>
                                                <!-- Opções preenchidas pelo JS -->
                                            </select>
                                        </div>
                                    </div>
                                    <hr>
                                    <p class="fw-bold text-dark mb-2">Ou digite os dados manualmente:</p>
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <input type="text" class="form-control" id="inputGuardaNome" placeholder="Nome completo...">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="inputGuardaRG" placeholder="RG...">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control cpf-mask" id="inputGuardaCPF" placeholder="CPF...">
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <input type="text" class="form-control" id="inputGuardaEndereco" placeholder="Endereço completo...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-car-side me-2 text-primary"></i>Dados do Veículo:</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" id="inputGuardaVeiculoTipo" placeholder="Tipo (ex: Automóvel, Moto)">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" id="inputGuardaVeiculoMarca" placeholder="Marca (ex: Fiat)">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" id="inputGuardaVeiculoModelo" placeholder="Modelo (ex: Uno Mille)">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" id="inputGuardaVeiculoCor" placeholder="Cor (ex: Prata)">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control placa-mask" id="inputGuardaVeiculoPlaca" placeholder="Placa (ex: ABC-1234)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarTermoGuardaVeiculo">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA TERMO DE APREENSÃO -->
            <div class="modal fade" id="modalTermoApreensao" tabindex="-1" aria-labelledby="modalTermoApreensaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalTermoApreensaoLabel">
                                <i class="fas fa-hand-holding-box me-2"></i> Termo de Apreensão
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-check me-2 text-primary"></i>Apresentador dos Objetos:</h6>
                                    <small class="text-muted">A pessoa que entregou ou apresentou os objetos na delegacia.</small>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <select class="form-select" id="selectPessoaApreensao">
                                                <option value="" selected disabled>Escolha uma pessoa envolvida...</option>
                                                <!-- Opções preenchidas pelo JS -->
                                            </select>
                                        </div>
                                    </div>
                                    <p class="fw-bold text-dark mb-2 mt-3">Ou digite o nome manualmente:</p>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <input type="text" class="form-control" id="inputApreensaoApresentador" placeholder="Nome do Condutor, Testemunha, etc...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list me-2 text-primary"></i>Bens, Objetos e Local:</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label fw-bold">Local onde foram arrecadados:</label>
                                            <input type="text" class="form-control" id="inputApreensaoLocal" placeholder="Ex: Local do crime, residência do suspeito, etc...">
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <label class="form-label fw-bold">Descrição dos objetos apreendidos:</label>
                                            <textarea class="form-control" id="inputApreensaoObjetos" rows="4" placeholder="Descreva os objetos apreendidos..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarTermoApreensao">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA CAPA DE PROCEDIMENTO (BOC / TCO) -->
            <div class="modal fade" id="modalCapaProcedimento" tabindex="-1" aria-labelledby="modalCapaProcedimentoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalCapaProcedimentoLabel">
                                <i class="fas fa-file-alt me-2"></i> Configurar Capa
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-cog me-2 text-primary"></i>Tipo de Procedimento:</h6>
                                    <small class="text-muted">Escolha a modalidade do procedimento que constará no cabeçalho.</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <select class="form-select form-select-lg" id="selectTipoProcedimentoCapa">
                                                <option value="Flagrante">Flagrante (Auto de Prisão / Apreensão em Flagrante)</option>
                                                <option value="Portaria">Portaria (Instauração por Portaria)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden" id="inputDocumentoBaseCapa">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarCapaProcedimento">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA ORDEM DE SERVIÇO DE INTIMAÇÃO -->
            <div class="modal fade" id="modalOrdemServicoIntimacao" tabindex="-1" aria-labelledby="modalOrdemServicoIntimacaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalOrdemServicoIntimacaoLabel">
                                <i class="fas fa-clipboard-list me-2"></i> Ordem de Serviço - Intimação
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i>Pessoas a serem Intimadas:</h6>
                                    <small class="text-muted">Selecione quem deverá ser intimado na diligência (pode selecionar mais de uma pessoa).</small>
                                </div>
                                <div class="card-body">
                                    <div class="row" id="containerCheckboxesIntimacao">
                                        <!-- Checkboxes preenchidos dinamicamente pelo JS -->
                                    </div>
                                    <hr>
                                    <p class="fw-bold text-dark mb-2">Ou adicione alguém manualmente:</p>
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <input type="text" class="form-control" id="inputIntimacaoPessoaManual" placeholder="Nome completo, Endereço, RG/CPF...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarOrdemServicoIntimacao">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL PARA OFÍCIO DE REMESSA -->
            <div class="modal fade" id="modalOficioRemessa" tabindex="-1" aria-labelledby="modalOficioRemessaLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #2c3e50;">
                            <h5 class="modal-title" id="modalOficioRemessaLabel">
                                <i class="fas fa-envelope-open-text me-2"></i> Ofício de Remessa de Procedimento
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light">
                            <div class="card border-0 shadow-sm mb-3">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-paper-plane me-2 text-primary"></i>Destinatário do Ofício:</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold">Tratamento:</label>
                                            <select class="form-select" id="selectRemessaTratamento">
                                                <option value="Promotor(a)">Senhor(a) Promotor(a)</option>
                                                <option value="Juiz(a)">Senhor(a) Juiz(a)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold">Cargo Extenso (Destinatário):</label>
                                            <input type="text" class="form-control" id="inputRemessaDestinatario" value="PROMOTOR(A) DE JUSTIÇA DO ESTADO DE PERNAMBUCO">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-folder-open me-2 text-primary"></i>Dados do Procedimento Remetido:</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold">Tipo de Procedimento:</label>
                                            <select class="form-select" id="selectRemessaTipoProc">
                                                <option value="IP">IP (Inquérito Policial)</option>
                                                <option value="APFD">APFD (Auto de Prisão em Flagrante)</option>
                                                <option value="BOC">BOC (Boletim de Ocorrência Circunstanciado)</option>
                                                <option value="TCO">TCO (Termo Circunstanciado)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label fw-bold">Nº do Tombo (Procedimento):</label>
                                            <input type="text" class="form-control" id="inputRemessaTombo" placeholder="Ex: 01234/2026">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarOficioRemessa">Confirmar e Gerar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL DE SUGESTÕES PENDENTES DE COLABORADORES -->
            <div class="modal fade" id="modalSugestoesPendentes" tabindex="-1" aria-labelledby="modalSugestoesLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable" style="max-width: 950px;">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header" style="background: linear-gradient(135deg,#ff8c00,#ffd700); color:#fff;">
                            <h5 class="modal-title fw-bold" id="modalSugestoesLabel">
                                <i class="bi bi-people-fill me-2"></i>
                                Sugestões de Envolvidos Aguardando Aprovação
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div id="listaSugestoesPendentes" class="p-3">
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-hourglass-split fs-2"></i>
                                    <p class="mt-2">Carregando sugestões...</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light">
                            <small class="text-muted me-auto"><i class="bi bi-info-circle me-1"></i>Abra o BOE para aprovar ou rejeitar cada envolvido diretamente nos chips laranjas.</small>
                            <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Pendências (Alertas) - Mantido como estava -->
            <div class="modal fade" id="modalPendencias" tabindex="-1" aria-labelledby="modalPendenciasLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title text-dark" id="modalPendenciasLabel"><i class="bi bi-exclamation-triangle-fill"></i> Alerta de Procedimentos sem Movimentação</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Legenda de Prazos -->
                            <div class="alert alert-light border-warning shadow-sm mb-3" role="alert">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                                        <strong>Regras de Inatividade:</strong> Procedimentos que ultrapassam os prazos abaixo aparecem aqui.
                                    </div>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-danger px-2 py-1"><i class="bi bi-alarm"></i> Alta: > 8d</span>
                                        <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-alarm"></i> Média: > 20d</span>
                                        <span class="badge bg-success px-2 py-1"><i class="bi bi-alarm"></i> Baixa: > 50d</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Filtros e Busca -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Prioridade</label>
                                    <select class="form-select form-select-sm" id="filtroPrioridade">
                                        <option value="">Todas</option>
                                        <option value="ALTA PRIORIDADE">Alta</option>
                                        <option value="MEDIA PRIORIDADE">Média</option>
                                        <option value="BAIXA PRIORIDADE">Baixa</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Buscar BOE ou IP</label>
                                    <input type="text" class="form-control form-control-sm" id="buscaPendencias" placeholder="Digite BOE ou IP...">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover rounded" id="tabelaPendencias">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width: 14%">Prioridade</th>
                                            <th class="text-center" style="width: 12%">Data do Fato</th>
                                            <th class="text-center" style="width: 19%">BOE</th>
                                            <th class="text-center" style="width: 20%">IP</th>
                                            <th class="text-center" style="width: 12%">Última Mov.</th>
                                            <th class="text-center" style="width: 13%">Dias sem Mov.</th>
                                            <th class="text-center" style="width: 10%">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="listaPendencias">
                                        <!-- Preenchido via JS -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginação -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small" id="infoPaginacao">Mostrando 0 de 0</div>
                                <nav>
                                    <ul class="pagination pagination-sm mb-0" id="paginacaoPendencias">
                                        <!-- Preenchido via JS -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Erro Removido -->

            <!-- Modal Genérico de Confirmação de Exclusão (Atualizado) -->
            <div class="modal fade" id="modalConfirmacaoGenerico" tabindex="-1" aria-labelledby="modalConfirmacaoGenericoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalConfirmacaoGenericoLabel">Confirmar Exclusão</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="modalConfirmacaoGenericoBody">
                            Tem certeza que deseja excluir este registro?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoGenerico">Excluir</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ MODAL CONFIGURAÇÕES PESSOAIS (NOVO) -->
            <div class="modal fade" id="modalConfigPessoais" tabindex="-1" aria-labelledby="modalConfigPessoaisLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72, #2a5298); color: white;">
                            <h5 class="modal-title fw-bold" id="modalConfigPessoaisLabel">
                                <i class="bi bi-gear-fill me-2"></i> Minhas Configurações Padrão
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-4">
                                <i class="bi bi-info-circle-fill me-1"></i> 
                                Os dados preenchidos aqui ficarão salvos <strong>na sua conta</strong> e serão inseridos automaticamente em novos formulários de procedimentos para agilizar o preenchimento.
                            </p>
                            <form id="formConfigPessoais">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Delegado</label>
                                        <input type="text" class="form-control" id="configDelegado" placeholder="Ex: Nome Completo">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Escrivão</label>
                                        <input type="text" class="form-control" id="configEscrivao" placeholder="Ex: Nome Completo">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Delegacia / Circunscrição</label>
                                        <input type="text" class="form-control" id="configDelegacia" placeholder="Ex: 167ª Circunscrição">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Cidade</label>
                                        <input type="text" class="form-control" id="configCidade" placeholder="Ex: Afogados da Ingazeira">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary">Policial 1</label>
                                        <input type="text" class="form-control" id="configPolicial1" placeholder="Ex: Nome Completo">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-primary">Policial 2</label>
                                        <input type="text" class="form-control" id="configPolicial2" placeholder="Ex: Nome Completo">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-success fw-bold px-4" id="btnSalvarConfigPessoais">
                                <i class="bi bi-save me-1"></i> Salvar e Aplicar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para Importação Híbrida de BOE (Texto ou PDF) substituído por Componente -->
            @include('components.modal_importacao_boe')

            <!-- Modal de Busca/Cadastro de Pessoa (Novo) -->
            <div class="modal fade" id="modalPessoa" tabindex="-1" aria-labelledby="modalPessoaLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalPessoaLabel">Adicionar/Buscar Pessoa</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="nav nav-tabs" id="modalPessoaTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="pesquisar-tab" data-bs-toggle="tab" data-bs-target="#pesquisar-pane" type="button" role="tab">Pesquisar</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="cadastrar-tab" data-bs-toggle="tab" data-bs-target="#cadastrar-pane" type="button" role="tab">Cadastrar Novo</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="modalPessoaTabsContent">
                                <!-- Aba de Pesquisa -->
                                <div class="tab-pane fade show active p-3" id="pesquisar-pane" role="tabpanel">
                                    <label for="inputBuscaPessoaModal" class="form-label">Buscar por nome ou alcunha:</label>
                                    <input type="text" class="form-control" id="inputBuscaPessoaModal" placeholder="Digite para buscar...">
                                    <input type="hidden" id="hiddenPessoaIdModal">
                                    <div id="resultadosBuscaPessoa" class="list-group mt-2"></div>
                                </div>
                                <!-- Aba de Cadastro -->
                                <div class="tab-pane fade p-3" id="cadastrar-pane" role="tabpanel">
                                    <form id="formCadPessoaModal">
                                        <div class="row g-3">
                                            <div class="col-md-8">
                                                <label for="inputNomeModal" class="form-label">Nome Completo</label>
                                                <input type="text" class="form-control" id="inputNomeModal" name="nome" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="inputAlcunhaModal" class="form-label">Alcunha</label>
                                                <input type="text" class="form-control" id="inputAlcunhaModal" name="alcunha">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="inputNascimentoModal" class="form-label">Data de Nascimento</label>
                                                <input type="date" class="form-control" id="inputNascimentoModal" name="nascimento">
                                            </div>
                                            <!-- Adicione outros campos do cadpessoa aqui conforme necessário -->
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-primary" id="btnSalvarPessoaModal">Salvar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de confirmação para remover chip (Novo) -->
            <div class="modal fade" id="modalConfirmacaoChip" tabindex="-1" aria-labelledby="modalConfirmacaoChipLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalConfirmacaoChipLabel">Confirmar Remoção</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Deseja remover este envolvido?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger" id="btnConfirmarRemoverChip">Remover</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Gerador de Prompts (Novo) -->
            <div class="modal fade" id="modalPromptGenerator" tabindex="-1" aria-labelledby="modalPromptGeneratorLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalPromptGeneratorLabel">📋 Gerador de Prompt para Depoimento</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="selectTipoPrompt" class="form-label">Tipo de Prompt</label>
                                <select class="form-select" id="selectTipoPrompt">
                                    <option value="">Carregando...</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="textareaPromptGerado" class="form-label">Prompt Gerado (Pronto para copiar)</label>
                                <textarea class="form-control" id="textareaPromptGerado" rows="12" style="font-family: monospace; font-size: 13px;"></textarea>
                                <div class="form-text text-warning mt-2" id="avisoHistoricoFaltando" style="display: none;">
                                    <i class="bi bi-exclamation-triangle-fill"></i> O histórico deste BOE não estava em cache. Por favor, cole manualmente o HISTÓRICO no final do texto acima antes de copiar.
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-between">
                            <div><span class="badge bg-secondary" id="badgeTipoCrimePrompt"></span></div>
                            <div>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="button" class="btn btn-primary" id="btnCopiarPrompt"><i class="bi bi-clipboard"></i> Copiar Prompt</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Dados Complementares do Autor (Novo) -->
            <div class="modal fade" id="modalDadosAutor" tabindex="-1" aria-labelledby="modalDadosAutorLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDadosAutorLabel">Dados Complementares do Autor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formDadosAutorModal">
                                <input type="hidden" id="hiddenAutorIndex">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="inputInterrogatorio" class="form-label">Interrogatório</label>
                                        <textarea class="form-control" id="inputInterrogatorio" name="interrogatorio" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="inputNotaCulpa" class="form-label">Nota de Culpa</label>
                                        <textarea class="form-control" id="inputNotaCulpa" name="nota_culpa" rows="3"></textarea>
                                    </div>
                                    <!-- Adicione outros campos complementares do autor aqui -->
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-primary" id="btnSalvarDadosAutorModal">Salvar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Dados Complementares da Vítima (Novo) -->
            <div class="modal fade" id="modalDadosVitima" tabindex="-1" aria-labelledby="modalDadosVitimaLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDadosVitimaLabel">Dados Complementares da Vítima</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formDadosVitimaModal">
                                <input type="hidden" id="hiddenVitimaIndex">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="inputInterrogatorioVitima" class="form-label">Relato/Depoimento</label>
                                        <textarea class="form-control" id="inputInterrogatorioVitima" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="inputNotaVitima" class="form-label">Observações</label>
                                        <textarea class="form-control" id="inputNotaVitima" rows="3"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-primary" id="btnSalvarDadosVitimaModal">Salvar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Dados Complementares da Testemunha (Novo) -->
            <div class="modal fade" id="modalDadosTestemunha" tabindex="-1" aria-labelledby="modalDadosTestemunhaLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalDadosTestemunhaLabel">Dados Complementares da Testemunha</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formDadosTestemunhaModal">
                                <input type="hidden" id="hiddenTestemunhaIndex">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="inputInterrogatorioTestemunha" class="form-label">Depoimento</label>
                                        <textarea class="form-control" id="inputInterrogatorioTestemunha" rows="3"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="inputNotaTestemunha" class="form-label">Observações</label>
                                        <textarea class="form-control" id="inputNotaTestemunha" rows="3"></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-primary" id="btnSalvarDadosTestemunhaModal">Salvar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Troca de Papel (Novo) -->
            <div class="modal fade" id="modalTrocarPapel" tabindex="-1" aria-labelledby="modalTrocarPapelLabel" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalTrocarPapelLabel">Trocar Papel</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <label for="selectNovoPapel" class="form-label">Mover para:</label>
                            <select class="form-select" id="selectNovoPapel">
                                <option value="condutores">Condutor</option>
                                <option value="vitimas">Vítima</option>
                                <option value="autores">Autor</option>
                                <option value="testemunhas">Testemunha</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary btn-sm" id="btnConfirmarTrocaPapel">Mover</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Alerta Removido -->

            <!-- Modal de Escolha de Oitiva (Novo) -->
            <div class="modal fade" id="modalEscolherOitiva" tabindex="-1" aria-labelledby="modalEscolherOitivaLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="modalEscolherOitivaLabel"><i class="bi bi-file-earmark-text"></i> Escolher Tipo de Termo</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Selecione qual documento deseja gerar/editar para <strong id="nomeOitivaModal" class="text-primary"></strong>:</p>
                            
                            <div class="list-group">
                                <label class="list-group-item list-group-item-action cursor-pointer">
                                    <input class="form-check-input me-1" type="radio" name="radioTipoOitiva" value="depoimento" id="radioDepoimento">
                                    <strong>Termo de Depoimento</strong> <small class="text-muted d-block ms-4">Padrão para Condutores e Testemunhas</small>
                                </label>
                                <label class="list-group-item list-group-item-action cursor-pointer">
                                    <input class="form-check-input me-1" type="radio" name="radioTipoOitiva" value="declaracao" id="radioDeclaracao">
                                    <strong>Termo de Declaração</strong> <small class="text-muted d-block ms-4">Padrão para Vítimas e Menores</small>
                                </label>
                                <label class="list-group-item list-group-item-action cursor-pointer">
                                    <input class="form-check-input me-1" type="radio" name="radioTipoOitiva" value="interrogatorio" id="radioInterrogatorio">
                                    <strong>Termo de Interrogatório</strong> <small class="text-muted d-block ms-4">Padrão para Autores</small>
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary fw-bold" id="btnConfirmarOitivaModal"><i class="bi bi-check2-circle"></i> Abrir Editor</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bibliotecas principais -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    
    <!-- JS Central do Sistema (Modais Originais Restabelecidos) -->
    <script src="{{ asset('js/core.js') }}?v={{ time() }}"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Rotas UNIFICADAS -->
    <script>
        const rotas = {
            inicio: {
                pesquisar: "{{ route('inicio.pesquisar') }}",
                salvar: "{{ route('inicio.salvar') }}",
                buscar: "{{ url('/inicio/buscar') }}",
                atualizar: "{{ url('/inicio/atualizar') }}",
                excluir: "{{ url('/inicio/excluir') }}"
            },
            condutor: {
                pesquisar: "{{ route('condutor.apfd.pesquisar') }}",
                salvar: "{{ route('condutor.apfd.salvar') }}",
                buscar: "{{ url('/condutor-apfd/buscar') }}",
                atualizar: "{{ url('/condutor-apfd/atualizar') }}",
                excluir: "{{ url('/condutor-apfd/excluir') }}",
                ultimos: "{{ route('condutor.apfd.ultimos') }}"
            },
            vitima1: {
                pesquisar: "{{ url('/vitima1/pesquisar') }}",
                salvar: "{{ url('/vitima1/salvar') }}",
                buscar: "{{ url('/vitima1/buscar') }}",
                atualizar: "{{ url('/vitima1/atualizar') }}",
                excluir: "{{ url('/vitima1/excluir') }}"
            },
            testemunha1: {
                pesquisar: "{{ url('/testemunha1/pesquisar') }}",
                salvar: "{{ url('/testemunha1/salvar') }}",
                buscar: "{{ url('/testemunha1/buscar') }}",
                atualizar: "{{ url('/testemunha1/atualizar') }}",
                excluir: "{{ url('/testemunha1/excluir') }}"
            },
            autor1: {
                pesquisar: "{{ url('/autor1/pesquisar') }}",
                salvar: "{{ url('/autor1/salvar') }}",
                buscar: "{{ url('/autor1/buscar') }}",
                atualizar: "{{ url('/autor1/atualizar') }}",
                excluir: "{{ url('/autor1/excluir') }}"
            },
            outro: {
                pesquisar: "{{ route('outro.pesquisar') }}",
                salvar: "{{ route('outro.salvar') }}",
                buscar: "{{ url('/outro/buscar') }}",
                atualizar: "{{ url('/outro/atualizar') }}",
                excluir: "{{ url('/outro/excluir') }}"
            }
        };
    </script>

    <!-- ✅ CORREÇÃO: Rotas PRIMEIRO (cache-busting p/ evitar JS antigo no navegador) -->
    <script src="{{ asset('js/rotas_impressao.js') }}?v={{ time() }}_fix3"></script>

    <!-- Chart.js para os gráficos de Distribuição de Status -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- ✅✅✅ ORDEM CORRIGIDA DOS SCRIPTS -->
    <script src="{{ asset('js/vinculos_boe_simples.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/vinculos_completo.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/script.js') }}?v={{ time() }}_fix4"></script>
    <script src="{{ asset('js/menu_lateral.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/config_pessoais.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/script_apfd.js') }}?v={{ time() }}_fix3"></script>
    <script>
        // Lógica de Alertas de Pendências com Filtros e Paginação
        $(document).ready(function() {
            let todasPendencias = [];
            let pendenciasFiltradas = [];
            let paginaAtual = 1;
            const itensPorPagina = 7;

            // ── Verificações ao carregar ──────────────────────────────
            verificarPendencias();
            verificarSugestoesPendentes();
            // Atualizar sugestões a cada 30 segundos
            setInterval(verificarSugestoesPendentes, 30000);

            // ── Botão Pendências de Procedimentos ────────────────────
            $('#btnAlertas').click(function() {
                $('#modalPendencias').modal('show');
                renderizarPendencias();
            });

            // ── Botão Sugestões de Colaboradores ─────────────────────
            $('#btnSugestoesPendentes').click(function() {
                verificarSugestoesPendentes(true); // força recarregar e abre modal
            });

            // ── Lógica de Sugestões ───────────────────────────────────
            function verificarSugestoesPendentes(abrirModal) {
                $.ajax({
                    url: '/boe/vinculos/sugestoes-pendentes',
                    method: 'GET',
                    success: function(resp) {
                        if (resp.success && resp.count > 0) {
                            $('#btnSugestoesPendentes').removeClass('d-none');
                            $('#badgeSugestoes').text(resp.count);
                            if (abrirModal) {
                                renderizarSugestoes(resp.data);
                                $('#modalSugestoesPendentes').modal('show');
                            }
                        } else {
                            $('#btnSugestoesPendentes').addClass('d-none');
                            if (abrirModal) {
                                renderizarSugestoes([]);
                                $('#modalSugestoesPendentes').modal('show');
                            }
                        }
                    }
                });
            }

            function renderizarSugestoes(grupos) {
                const $lista = $('#listaSugestoesPendentes');
                if (!grupos || grupos.length === 0) {
                    $lista.html(`
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-check-circle fs-1 text-success"></i>
                            <p class="mt-3 fs-5">Nenhuma sugestão pendente!</p>
                            <small>Todos os envolvidos sugeridos já foram processados.</small>
                        </div>`);
                    return;
                }

                let html = '';
                grupos.forEach(function(grupo) {
                    html += `
                        <div class="card mb-3 border-warning shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center"
                                 style="background:linear-gradient(135deg,#fff3cd,#ffeaa7);">
                                <div>
                                    <i class="bi bi-file-earmark-text text-warning me-2"></i>
                                    <strong>BOE: ${grupo.boe}</strong>
                                </div>
                                <span class="badge bg-warning text-dark">${grupo.sugestoes.length} sugestão(ões)</span>
                            </div>
                            <ul class="list-group list-group-flush">`;
                    grupo.sugestoes.forEach(function(s) {
                        const tipoLabel = {CONDUTOR:'Condutor',VITIMA:'Vítima',AUTOR:'Autor',TESTEMUNHA:'Testemunha',OUTRO:'Outro'}[s.tipo_vinculo] || s.tipo_vinculo;
                        const dataStr = s.created_at ? new Date(s.created_at).toLocaleString('pt-BR') : '';
                        html += `
                                <li class="list-group-item py-2 px-3">
                                    <div class="d-flex align-items-center flex-nowrap w-100">
                                        <span class="badge bg-secondary me-2 flex-shrink-0" style="min-width: 85px;">${tipoLabel}</span>
                                        <span class="fw-bold text-uppercase text-truncate me-2" style="flex-grow: 1; font-size: 0.9rem;">${s.pessoa_nome}</span>
                                        <div class="text-muted small text-nowrap flex-shrink-0" style="font-size: 0.75rem;">
                                            <i class="bi bi-person me-1"></i>por <span class="text-dark fw-semibold">${s.criado_por_nome}</span>
                                            <span class="mx-1">|</span>
                                            <i class="bi bi-clock me-1"></i>${dataStr}
                                        </div>
                                    </div>
                                </li>`;
                    });
                    html += `
                            </ul>
                            <div class="card-footer text-end py-2 bg-light">
                                <button type="button" class="btn btn-sm btn-warning fw-bold" onclick="carregarBoeSugerido('${grupo.boe}')">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>Abrir BOE e Revisar
                                </button>
                            </div>
                        </div>`;
                });
                $lista.html(html);
            }

            // Função global para ser chamada pelo onclick do botão gerado
            window.carregarBoeSugerido = function(boe) {
                $('#modalSugestoesPendentes').modal('hide');
                
                // Mudar para a aba de Início principal
                $('#abasPrincipais a[href="#aba-inicio"]').tab('show');
                // Ir para a sub-aba APFD | IP (onde fica a pesquisa)
                $('#subAbasInicio a[href="#dados"]').tab('show');
                
                // Tenta carregar os dados diretamente via AJAX para abrir a ficha imediatamente
                $.ajax({
                    url: '/inicio/pesquisar',
                    method: 'GET',
                    data: {
                        filtro: 'BOE',
                        termo: boe
                    },
                    success: function(resp) {
                        if (resp.success && resp.data && resp.data.length > 0) {
                            // Carrega a ficha APFD do primeiro resultado exato
                            OcorrenciasApp.carregarDadosRegistro(resp.data[0].id);
                            // Rolar para o topo suavemente para ver o form preenchido
                            $('html, body').animate({scrollTop: 0}, 'fast');
                        } else {
                            // Fallback clássico caso não retorne via API
                            $('#ddlFiltro').val('BOE');
                            $('#txtPesquisa').val(boe);
                            $('#btnPesquisar').click();
                            $('html, body').animate({scrollTop: 0}, 'fast');
                        }
                    },
                    error: function() {
                        // Faz a pesquisa normal se a requisição AJAX falhar
                        $('#ddlFiltro').val('BOE');
                        $('#txtPesquisa').val(boe);
                        $('#btnPesquisar').click();
                        $('html, body').animate({scrollTop: 0}, 'fast');
                    }
                });
            };

            // Event listeners para filtros
            $('#filtroPrioridade, #filtroAno').change(function() {
                aplicarFiltros();
            });

            $('#buscaPendencias').on('input', function() {
                aplicarFiltros();
            });

            function verificarPendencias() {
                $.get("{{ route('inicio.buscarPendencias') }}", function(response) {
                    console.log('🔍 DEBUG: Total de pendências recebidas:', response.count);
                    console.log('🔍 DEBUG: Dados:', response.data);
                    
                    if (response.success && response.count > 0) {
                        $('#btnAlertas').show();
                        $('#badgeAlertas').text(response.count);
                        todasPendencias = response.data;
                        pendenciasFiltradas = [...todasPendencias];
                        
                        // Preencher filtro de anos
                        preencherFiltroAnos();
                        
                        // Atualizar resumo
                        atualizarResumo();
                        
                        // Renderizar primeira página
                        renderizarPendencias();
                    } else {
                        $('#btnAlertas').hide();
                    }
                });
            }

            function preencherFiltroAnos() {
                const anos = [...new Set(todasPendencias.map(item => {
                    return new Date(item.updated_at).getFullYear();
                }))].sort((a, b) => b - a);
                
                let options = '<option value="">Todos</option>';
                anos.forEach(ano => {
                    options += `<option value="${ano}">${ano}</option>`;
                });
                $('#filtroAno').html(options);
            }

            function atualizarResumo() {
                const alta = pendenciasFiltradas.filter(p => p.prioridade === 'ALTA PRIORIDADE').length;
                const media = pendenciasFiltradas.filter(p => p.prioridade === 'MEDIA PRIORIDADE').length;
                const baixa = pendenciasFiltradas.filter(p => p.prioridade === 'BAIXA PRIORIDADE').length;
                
                $('#resumoPendencias').html(`
                    <span class="badge bg-danger">${alta} Alta</span>
                    <span class="badge bg-warning text-dark">${media} Média</span>
                    <span class="badge bg-success">${baixa} Baixa</span>
                `);
            }

            function aplicarFiltros() {
                const prioridadeSelecionada = $('#filtroPrioridade').val();
                const anoSelecionado = $('#filtroAno').val();
                const termoBusca = $('#buscaPendencias').val().toUpperCase();

                pendenciasFiltradas = todasPendencias.filter(item => {
                    // Filtro de prioridade
                    if (prioridadeSelecionada && item.prioridade !== prioridadeSelecionada) {
                        return false;
                    }

                    // Filtro de ano
                    if (anoSelecionado) {
                        const anoItem = new Date(item.updated_at).getFullYear();
                        if (anoItem != anoSelecionado) {
                            return false;
                        }
                    }

                    // Busca por BOE ou IP
                    if (termoBusca) {
                        const boe = (item.BOE || '').toUpperCase();
                        const ip = (item.IP || '').toUpperCase();
                        if (!boe.includes(termoBusca) && !ip.includes(termoBusca)) {
                            return false;
                        }
                    }

                    return true;
                });

                paginaAtual = 1;
                atualizarResumo();
                renderizarPendencias();
            }

            function renderizarPendencias() {
                const inicio = (paginaAtual - 1) * itensPorPagina;
                const fim = inicio + itensPorPagina;
                const itensPagina = pendenciasFiltradas.slice(inicio, fim);

                let html = '';
                if (itensPagina.length === 0) {
                    html = '<tr><td colspan="7" class="text-center text-muted py-4">Nenhum procedimento encontrado.</td></tr>';
                } else {
                    itensPagina.forEach(function(item) {
                        let badgeClass = 'bg-secondary';
                        if(item.prioridade === 'ALTA PRIORIDADE') badgeClass = 'bg-danger';
                        else if(item.prioridade === 'MEDIA PRIORIDADE') badgeClass = 'bg-warning text-dark';
                        else if(item.prioridade === 'BAIXA PRIORIDADE') badgeClass = 'bg-success';

                        html += `
                            <tr>
                                <td class="align-middle"><span class="badge ${badgeClass} w-100 py-2">${item.prioridade}</span></td>
                                <td class="text-center align-middle">${item.data_fato || '-'}</td>
                                <td class="text-center align-middle font-monospace fw-bold text-primary">${item.BOE || '-'}</td>
                                <td class="text-center align-middle font-monospace">${item.IP || '-'}</td>
                                <td class="text-center align-middle">${item.data_ult_mov}</td>
                                <td class="text-center align-middle">
                                    <span class="text-danger fw-bold fs-6">${item.dias_parado} dias</span>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar ${item.dias_parado > 50 ? 'bg-danger' : 'bg-warning'}" role="progressbar" style="width: ${Math.min(item.dias_parado, 100)}%"></div>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <button class="btn btn-sm btn-outline-primary btn-carregar-pendencia shadow-sm" data-id="${item.id}">
                                        <i class="bi bi-box-arrow-in-right"></i> Abrir
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }
                
                $('#listaPendencias').html(html);
                renderizarPaginacao();
                atualizarInfoPaginacao(inicio, fim);

                // Bind click do botão carregar
                $('.btn-carregar-pendencia').click(function() {
                    let id = $(this).data('id');
                    $('#modalPendencias').modal('hide');
                    OcorrenciasApp.carregarDadosRegistro(id);
                });
            }

            function renderizarPaginacao() {
                const totalPaginas = Math.ceil(pendenciasFiltradas.length / itensPorPagina);
                let html = '';

                if (totalPaginas > 1) {
                    // Botão Anterior
                    html += `
                        <li class="page-item ${paginaAtual === 1 ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-pagina="${paginaAtual - 1}">Anterior</a>
                        </li>
                    `;

                    // Números das páginas
                    for (let i = 1; i <= totalPaginas; i++) {
                        if (i === 1 || i === totalPaginas || (i >= paginaAtual - 1 && i <= paginaAtual + 1)) {
                            html += `
                                <li class="page-item ${i === paginaAtual ? 'active' : ''}">
                                    <a class="page-link" href="#" data-pagina="${i}">${i}</a>
                                </li>
                            `;
                        } else if (i === paginaAtual - 2 || i === paginaAtual + 2) {
                            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    // Botão Próximo
                    html += `
                        <li class="page-item ${paginaAtual === totalPaginas ? 'disabled' : ''}">
                            <a class="page-link" href="#" data-pagina="${paginaAtual + 1}">Próximo</a>
                        </li>
                    `;
                }

                $('#paginacaoPendencias').html(html);

                // Bind clicks de paginação
                $('.page-link').click(function(e) {
                    e.preventDefault();
                    const novaPagina = parseInt($(this).data('pagina'));
                    if (novaPagina && novaPagina !== paginaAtual) {
                        paginaAtual = novaPagina;
                        renderizarPendencias();
                    }
                });
            }

            function atualizarInfoPaginacao(inicio, fim) {
                const total = pendenciasFiltradas.length;
                const mostrando = Math.min(fim, total);
                $('#infoPaginacao').text(`Mostrando ${inicio + 1}-${mostrando} de ${total}`);
            }
        });
    </script>
    <!-- Modal do Cropper.js -->
    <div class="modal fade" id="modalCropEnvolvido" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Recortar Foto do Envolvido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center bg-dark">
                    <div style="max-height: 500px; max-width: 100%; overflow: hidden;">
                        <img id="imageToCrop" src="" style="max-width: 100%; display: block;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmCrop">
                        <i class="bi bi-crop"></i> Cortar e Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('partials.modal_reconhecimento')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script src="{{ asset('js/core.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/DocumentoService.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/chips_envolvidos.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/script_condutor_apfd.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/script_vitima1.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/script_testemunha1.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/script_autor1.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/script_outros.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/fotos_envolvidos.js') }}?v={{ time() }}"></script>

    <!-- Script para atualizar data/hora -->
    <script>
        function updateDateTime() {
            const now = new Date();
            const options = {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            document.getElementById('currentDateTime').textContent = now.toLocaleDateString('pt-BR', options);
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>

    <script>
        $(document).ready(function() {
            function toggleDataStatus() {
                var val = $('#inputStatus').val();
                if (val === 'Remetido a Justiça' || val === 'Concluído' || val === 'Remetido à Justiça') {
                    $('#divDataStatus').fadeIn();
                } else {
                    $('#divDataStatus').fadeOut();
                    $('#inputDataStatus').val('');
                }
            }

            $('#inputStatus').on('change', toggleDataStatus);
            // Executa no load também para caso de edição
            setTimeout(toggleDataStatus, 500);
            
            // Para garantir que recarregue o dado no edit, escuta também customEvent se houver
            $(document).on('click', '.btn-editar-grid', function() {
                setTimeout(toggleDataStatus, 1000);
            });
        });
    </script>

    <script>
        // Permissões
        window._userPerms = {
            gerar_prompts: {{ (Auth::check() && isset(Auth::user()->permissions['gerar_prompts']) && !Auth::user()->permissions['gerar_prompts']) ? 'false' : 'true' }}
        };

        // Definir as rotas para a intimação
        var rotasIntimacao = {
            pesquisar: "{{ route('intimacao.pesquisar') }}",
            salvar: "{{ route('intimacao.salvar') }}",
            buscar: "{{ route('intimacao.buscar', '') }}",
            atualizar: "{{ route('intimacao.atualizar', '') }}",
            excluir: "{{ route('intimacao.excluir', '') }}",
            controlePeriodo: "{{ route('intimacao.controle.periodo') }}",
            ultimos: "{{ route('intimacao.ultimos') }}",
            editor: "{{ route('intimacao.editor', '') }}"
        };

        // Rotas de impressão específicas para intimação
        var rotasImpressaoIntimacao = {
            'EDITOR DE INTIMAÇÃO': "{{ route('intimacao.editor', '--DADOS--') }}"
        };
    </script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>




    <script src="{{ asset('js/script_intimacao.js') }}?v={{ time() }}"></script>

    {{-- Auto-carregar registro se vier da Consulta de Antecedentes via ?abrir_id= --}}
    <script>
    (function() {
        const params = new URLSearchParams(window.location.search);
        const abrirId = params.get('abrir_id');
        if (abrirId) {
            // Aguarda a inicialização completa do OcorrenciasApp antes de carregar
            const tentarCarregar = (tentativas) => {
                if (window.OcorrenciasApp && typeof window.OcorrenciasApp.carregarDadosRegistro === 'function') {
                    window.OcorrenciasApp.carregarDadosRegistro(parseInt(abrirId, 10));
                    // Limpa o parâmetro da URL sem recarregar a página
                    history.replaceState(null, '', '/ip-apfd');
                } else if (tentativas > 0) {
                    setTimeout(() => tentarCarregar(tentativas - 1), 300);
                }
            };
            // Inicia as tentativas após o documento estar pronto
            $(document).ready(function() {
                setTimeout(() => tentarCarregar(20), 500);
            });
        }
    })();
    </script>
    </script>

    <!-- Offcanvas do Copiloto IA -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasCopilot" aria-labelledby="offcanvasCopilotLabel" style="width: 400px; background-color: #1e1e2d; color: #fff; border-right: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="offcanvas-header d-flex justify-content-between align-items-center" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
            <h5 class="offcanvas-title mb-0" id="offcanvasCopilotLabel" style="font-size: 1.1rem;">
                <i class="bi bi-robot text-info me-2"></i>Assistente IA
            </h5>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="btnCopilotClear" title="Nova Conversa / Limpar" style="border: none; color: #adb5bd;">
                    <i class="bi bi-eraser-fill"></i> Limpar
                </button>
                <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
        </div>
        <div class="offcanvas-body d-flex flex-column" style="padding: 0;">
            <!-- Área de Mensagens -->
            <div id="copilotChatBox" class="flex-grow-1 p-3" style="overflow-y: auto; display: flex; flex-direction: column; gap: 15px; background-color: #1a1a27;">
                <!-- Mensagem de boas vindas -->
                <div class="d-flex align-items-start">
                    <div class="bg-primary text-white p-2 rounded-3" style="max-width: 85%; font-size: 0.9rem; border-bottom-left-radius: 0 !important;">
                        Olá! Eu sou o assistente virtual do Sisdepol. Posso ajudar a analisar o BOE, preencher dados ou abrir oitivas automaticamente. O que deseja fazer?
                    </div>
                </div>
            </div>
            <!-- Área de Digitação -->
            <div class="p-3" style="border-top: 1px solid rgba(255, 255, 255, 0.1); background-color: #1e1e2d;">
                <form id="copilotForm">
                    <div class="input-group">
                        <textarea class="form-control" id="copilotInput" rows="1" placeholder="Digite seu comando..." style="background-color: rgba(255, 255, 255, 0.05); color: #fff; border: 1px solid rgba(255, 255, 255, 0.1); resize: none;"></textarea>
                        <button class="btn btn-info" type="submit" id="btnCopilotSend">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                    <div class="mt-2 text-end">
                        <small class="text-muted" style="font-size: 0.75rem;">Powered by DeepSeek AI</small>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('js/copilot.js') }}?v={{ time() }}"></script>
</body>
</html>

