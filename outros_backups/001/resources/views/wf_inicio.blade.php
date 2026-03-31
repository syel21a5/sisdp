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

    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!-- Flatpickr para seletores de data/hora -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
                                <li><a href="#" data-subtab-id="aba-celulares" data-subtab-title="Celulares"
                                        data-subtab-url="{{ route('celular.index') }}"><i class="bi bi-phone"></i>Celulares</a>
                                </li>
                            @endif
                            @if($canVeiculo)
                                <li><a href="#" data-subtab-id="aba-veiculos" data-subtab-title="Veículos"
                                        data-subtab-url="{{ route('veiculo.index') }}"><i
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
                    <a href="#" data-subtab-id="aba-intimacao-din" data-subtab-title="Intimação"
                        data-subtab-url="{{ route('intimacao.index') }}">
                        <i class="bi bi-envelope-paper"></i>Intimação
                    </a>
                </li>
            @endif
            <!-- NOVO MENU RELATÓRIOS (Dropdown) -->
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
                    </ul>
                </div>
            </li>

            <!-- BOTÃO DE SAIR - SEMPRE ACESSÍVEL -->
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
                <!-- Botão de Notificações Melhorado (Posicionado no canto superior direito) -->
                <button type="button" class="btn btn-warning position-absolute shadow-sm" id="btnAlertas" style="display: none; padding: 10px 20px; font-weight: 600; border-radius: 8px; transition: all 0.3s ease; top: 20px; right: 320px; z-index: 1000;">
                    <i class="bi bi-bell-fill me-2"></i>Pendências
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger shadow" id="badgeAlertas" style="font-size: 0.75rem; padding: 0.35em 0.65em;">
                        0
                        <span class="visually-hidden">mensagens não lidas</span>
                    </span>
                </button>
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
                                    <div class="col-md-4"><input type="text" class="form-control" placeholder="BOE PM"
                                            name="boe_pm" id="inputBOEPM"></div>
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
                                        style="min-width: 395px; max-width: 495px;">
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
                                            <select class="form-select flex-grow-0 border-start-0" id="ddlFiltro" style="width: auto; max-width: 150px;">
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
                                                        <th class="py-2 text-white" style="width: 16.66%;">BOE PC</th>
                                                        <th class="py-2 text-white" style="width: 16.66%;">BO PM</th>
                                                        <th class="py-2 text-white" style="width: 16.66%;">IP</th>
                                                        <th class="py-2 text-center text-white" style="width: 16.66%;">STATUS</th>
                                                        <th class="py-2 text-center text-white" style="width: 16.66%;">PRIORIDADE</th>
                                                        <th class="py-2 text-center text-white" style="width: 16.66%;">AÇÕES</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="6" class="text-center py-3 text-muted">
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
                                    <div class="col-md-4">
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

                                <!-- Apreensão (agora dentro de Dados Complementares) -->
                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <textarea class="form-control" name="Apreensao" id="inputApreensao" rows="6"
                                            placeholder="Descreva os itens apreendidos..."></textarea>
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

            <!-- Modal de Sucesso (Atualizado) -->
            <div class="modal fade" id="modalSucesso" tabindex="-1" aria-labelledby="modalSucessoLabel" aria-hidden="true" data-bs-backdrop="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="modalSucessoLabel">Sucesso</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center" id="modalSucessoMsg">
                            Operação realizada com sucesso!
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal">OK</button>
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
                            <div class="alert alert-light border-warning" role="alert">
                                <strong>Atenção!</strong> Os seguintes procedimentos estão sem movimentação há mais tempo do que o permitido para sua prioridade.
                                <span id="resumoPendencias" class="ms-3 badge bg-secondary"></span>
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

            <!-- Modal de Erro (Atualizado) -->
            <!-- Modal de Erro (Elegant) -->
            <div class="modal fade" id="modalErro" tabindex="-1" aria-labelledby="modalErroLabel" aria-hidden="true" data-bs-backdrop="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-3">
                        <div class="modal-header bg-danger text-white border-0">
                            <h5 class="modal-title fw-bold" id="modalErroLabel">Erro</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 text-center">
                            <h5 id="erroMensagem" class="fw-bold text-dark mb-0">
                                <!-- Mensagem de erro preenchida via JS -->
                            </h5>
                        </div>
                        <div class="modal-footer border-0 bg-light rounded-bottom-3">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- Modal para Importação Híbrida de BOE (Texto ou PDF) -->
            <div class="modal fade" id="modalImportarBoe" tabindex="-1" aria-labelledby="modalImportarBoeLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalImportarBoeLabel">Importar Histórico do BOE com IA</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Abas de Navegação -->
                            <ul class="nav nav-tabs mb-3" id="boeImportTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-texto" data-bs-toggle="tab" data-bs-target="#content-texto" type="button" role="tab"><i class="bi bi-card-text me-1"></i> 📝 Colar Texto (Rápido)</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-pdf" data-bs-toggle="tab" data-bs-target="#content-pdf" type="button" role="tab"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> 📄 Enviar Arquivo PDF</button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="boeImportTabsContent">
                                <!-- Aba de Texto -->
                                <div class="tab-pane fade show active" id="content-texto" role="tabpanel">
                                    <div class="alert alert-info">
                                        <i class="bi bi-magic me-1"></i> <strong>Extração Inteligente:</strong> Copie o texto completo do Histórico do BOE e cole abaixo.
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control" id="textoBoe" rows="11" placeholder="Copie todo o texto do BOE e cole aqui..."></textarea>
                                    </div>
                                </div>
                                
                                <!-- Aba de PDF -->
                                <div class="tab-pane fade" id="content-pdf" role="tabpanel">
                                    <div class="alert alert-warning">
                                        <i class="bi bi-cpu me-1"></i> <strong>Processamento Nativo:</strong> O arquivo PDF será aberto internamente e mapeado pela inteligência artificial.
                                    </div>
                                    <div class="mb-3">
                                        <label for="pdfBoe" class="form-label fw-bold">Selecione o arquivo PDF do BOE:</label>
                                        <input class="form-control form-control-lg" type="file" id="pdfBoe" accept=".pdf">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Progresso da IA -->
                        <div id="boeProgressWrapper" class="px-3 pb-2" style="display:none;">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted fw-bold" id="boeProgressLabel">🤖 A IA está lendo o documento...</small>
                                <small class="text-muted" id="boeProgressPercent">0%</small>
                            </div>
                            <div class="progress" style="height: 10px; border-radius: 10px;">
                                <div id="boeProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; border-radius: 10px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="button" class="btn btn-primary" id="btnProcessarBoe">Processar com IA</button>
                        </div>
                    </div>
                </div>
            </div>

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
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary btn-sm" id="btnConfirmarTrocaPapel">Mover</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Alerta Genérico (Elegante) - Mantido como estava -->
            <div class="modal fade" id="modalAlertaGenerico" tabindex="-1" aria-labelledby="modalAlertaGenericoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-warning text-dark border-0">
                            <h5 class="modal-title d-flex align-items-center" id="modalAlertaGenericoLabel">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Atenção
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <div class="mb-3">
                                <i class="bi bi-info-circle text-warning" style="font-size: 3rem;"></i>
                            </div>
                            <h5 id="modalAlertaGenericoMensagem" class="fw-normal">Este registro já foi adicionado!</h5>
                        </div>
                        <div class="modal-footer border-0 justify-content-center pb-4">
                            <button type="button" class="btn btn-warning px-5 fw-bold" data-bs-dismiss="modal">Entendi</button>
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

    <!-- ✅ CORREÇÃO: Rotas PRIMEIRO -->
    <script src="{{ asset('js/rotas_impressao.js') }}"></script>

    <!-- ✅✅✅ ORDEM CORRIGIDA DOS SCRIPTS -->
    <script src="{{ asset('js/vinculos_boe_simples.js') }}"></script>
    <script src="{{ asset('js/vinculos_completo.js') }}"></script>
    <script src="{{ asset('js/script.js') }}?v={{ time() }}_fix2"></script>
    <script src="{{ asset('js/menu_lateral.js') }}"></script>
    <script src="{{ asset('js/script_apfd.js') }}?v={{ time() }}"></script>
    <script>
        // Lógica de Alertas de Pendências com Filtros e Paginação
        $(document).ready(function() {
            let todasPendencias = [];
            let pendenciasFiltradas = [];
            let paginaAtual = 1;
            const itensPorPagina = 7;

            verificarPendencias();

            $('#btnAlertas').click(function() {
                $('#modalPendencias').modal('show');
                renderizarPendencias();
            });

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
                                <td><span class="badge ${badgeClass}">${item.prioridade}</span></td>
                                <td class="text-center">${item.data_fato || '-'}</td>
                                <td class="text-center">${item.BOE || '-'}</td>
                                <td class="text-center">${item.IP || '-'}</td>
                                <td class="text-center">${item.data_ult_mov}</td>
                                <td class="text-center text-danger fw-bold">${item.dias_parado} dias</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary btn-carregar-pendencia" data-id="${item.id}">
                                        <i class="bi bi-eye"></i> Ver
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
    <script src="{{ asset('js/chips_envolvidos.js') }}"></script>
    <script src="{{ asset('js/script_condutor_apfd.js') }}"></script>
    <script src="{{ asset('js/script_vitima1.js') }}"></script>
    <script src="{{ asset('js/script_testemunha1.js') }}"></script>
    <script src="{{ asset('js/script_autor1.js') }}"></script>
    <script src="{{ asset('js/script_outros.js') }}"></script>

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
</html>