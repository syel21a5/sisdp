<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro Inicial - SYS-DP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 e Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Font Awesome para ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- jQuery UI para autocomplete -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

    <!-- SweetAlert2 para notificações e loadings -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Flatpickr para seletores de data/hora -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- Seu CSS personalizado -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dark_mode.css') }}?v={{ time() }}">

    <!-- jQuery + jQuery UI + jQuery Mask -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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
                <!-- nome - Acesso Restrito - foi removido aqui -->
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
                <a href="{{ route('inicio') }}" class="{{ !$hasMenuAccess ? 'permission-tooltip' : '' }}">
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
                        <li><a href="{{ route('celular') }}" target="_blank"><i class="bi bi-phone"></i>Celulares</a></li>
                        @endif
                        @if($canVeiculo)
                        <li><a href="{{ route('veiculo') }}" target="_blank"><i class="bi bi-car-front"></i>Veículos</a></li>
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
            @if($hasMenuAccess && $canRelatorios)
            <li>
                <div class="sidebar-group-card">
                    <button class="menu-toggle" type="button" onclick="toggleSubmenu('relatorios-submenu')">
                        <span><i class="bi bi-file-earmark-bar-graph-fill"></i>Relatórios</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="sidebar-submenu" id="relatorios-submenu">
                        <li>
                            <a href="{{ url('/relatorios/procedimentos') }}">
                                <i class="bi bi-list-check"></i>Procedimentos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('consulta.pessoa.index') }}">
                                <i class="bi bi-person-bounding-box"></i>Antecedentes
                            </a>
                        </li>
                        
                        @if($canAuditoriaChips || (Auth::check() && Auth::user()->nivel_acesso === 'administrador'))
                        <li>
                            <a href="{{ route('administrativo.auditoria_chips') }}" style="color: #f59e0b !important;">
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

            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#modalConfigPessoais">
                    <i class="bi bi-gear-fill"></i> Minhas Configurações
                </a>
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
            <div class="header-container">
                <div class="page-header">
                    <h1 class="page-title">
                        <img src="{{ asset('images/police_avatar.ico') }}" alt="Logo" class="me-2">
                        SisDP - Sistema de Procedimentos Policiais
                    </h1>
                </div>
                <div class="system-info">
                    <div class="system-date" id="currentDateTime">{{ date('d/m/Y H:i:s') }}</div>
                    <div class="system-user">Usuário: {{ Auth::user()->nome ?? 'Administrador' }}</div>
                </div>
            </div>

            <!-- ABAS PRINCIPAIS - Adicionei a classe personalizada -->
            <ul class="nav nav-tabs nav-tabs-custom" id="abasPrincipais" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#aba-inicio" role="tab">Início</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#aba-condutor" role="tab">Envolvido</a>
                </li>

            </ul>

            <div class="tab-content mt-3">
                <!-- ABA INÍCIO -->
                <div class="tab-pane fade show active" id="aba-inicio" role="tabpanel">
                    <!-- SUB-ABAS -->
                    <ul class="nav nav-tabs mt-3" id="subAbasInicio" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#dados" role="tab">Dados Gerais</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#apreensao" role="tab">Apreensão</a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3">
                        <!-- Dados Gerais -->
                        <div class="tab-pane fade show active" id="dados" role="tabpanel">
                            <form id="formInicio">
                                @csrf
                                <div class="row g-3 mb-3">
                                    <div class="col-xl-2 col-lg-3 col-md-4 pe-1">
                                        <input type="text" class="form-control" placeholder="DD/MM/AAAA" name="data" id="inputData">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 col-md-8 ps-0 pe-1">
                                        <input type="text" class="form-control" placeholder="Data Completa" name="data_comp" id="inputDataComp">
                                    </div>
                                    <div class="col-xl-6 col-lg-5 col-md-12 ps-0">
                                        <input type="text" class="form-control" placeholder="Data por Extenso" name="data_ext" id="inputDataExt">
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-lg-4 col-md-12"><input type="text" class="form-control" placeholder="IP" name="ip" id="inputIP" value="0000.0000.000000-00" readonly style="background-color: #e9ecef; cursor: not-allowed;"></div>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="BOE" name="boe" id="inputBOE">
                                            <button class="btn btn-info" type="button" data-bs-toggle="modal" data-bs-target="#modalImportarBoeGeral" title="Importar Histórico do BOE"><i class="bi bi-upload"></i></button>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-6"><input type="text" class="form-control" placeholder="BOE PM" name="boe_pm" id="inputBOEPM"></div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-lg-6 col-12"><input type="text" class="form-control" placeholder="Delegado" name="delegado" id="inputDelegado"></div>
                                    <div class="col-lg-6 col-12"><input type="text" class="form-control" placeholder="Escrivão" name="escrivao" id="inputEscrivao"></div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-lg-6 col-12"><input type="text" class="form-control" placeholder="Delegacia" name="delegacia" id="inputDelegacia"></div>
                                    <div class="col-lg-6 col-12"><input type="text" class="form-control" placeholder="Cidade" name="cidade" id="inputCidade"></div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-lg-6 col-12"><input type="text" class="form-control" placeholder="Policial 1" name="policial_1" id="inputPolicial1"></div>
                                    <div class="col-lg-6 col-12"><input type="text" class="form-control" placeholder="Policial 2" name="policial_2" id="inputPolicial2"></div>
                                </div>

                                <!-- Box para Envolvidos Extraídos -->
                                <div class="row mt-4" id="areaEnvolvidosGeral">
                                    <div class="col-12">
                                        <div class="card border border-primary border-opacity-25 shadow-sm">
                                            <div class="card-header bg-primary bg-opacity-10 border-bottom-0 py-3">
                                                <h6 class="card-title text-primary mb-0 fw-bold"><i class="bi bi-people-fill me-2"></i>Envolvidos Extraídos do BOE</h6>
                                            </div>
                                            <div class="card-body p-4 text-center" id="emptyStateEnvolvidos">
                                                <i class="bi bi-person-bounding-box text-muted opacity-50 mb-2" style="font-size: 2.5rem; display: block;"></i>
                                                <p class="text-muted mb-0">Nenhum BOE importado ainda.</p>
                                                <small class="text-secondary">Os envolvidos identificados aparecerão aqui. Clique neles para preencher o formulário.</small>
                                            </div>
                                            <div class="card-body p-3 d-none" id="populatedStateEnvolvidos">
                                                <div id="chipsEnvolvidosGeral" class="d-flex flex-wrap gap-2 mb-2 justify-content-center"></div>
                                                <div class="text-center mt-3">
                                                    <small class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Clique em um chip acima para preencher automaticamente o formulário do envolvido.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>

                        <!-- Apreensão -->
                        <div class="tab-pane fade" id="apreensao" role="tabpanel">
                            <form id="formApreensao">
                                <div class="row g-3 mb-3">
                                    <div class="col-12">
                                        <textarea class="form-control" name="Apreensao" id="inputApreensao"
                                                rows="6" placeholder="Descreva os itens apreendidos..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div> <!-- fecha tab-content das sub-abas -->
                </div> <!-- fecha tab-pane aba-inicio -->

                <!-- ABA CONDUTOR (INCLUÍDA VIA BLADE) -->
                <div class="tab-pane fade" id="aba-condutor" role="tabpanel">
                    @include('wf_condutor')
                </div>

                <!-- ABA INTIMAÇÃO removida para abrir como sub-aba dinâmica -->
                
            </div> <!-- fecha tab-content das abas principais -->

    <!-- ✅ MODAL EXTRAÇÃO DE BOE (SOMETNE NATIVO) -->
    @include('components.modal_importacao_boe', [
        'modalId' => 'modalImportarBoeGeral',
        'suffix' => 'Geral',
        'somenteNativo' => true
    ])

    <script src="{{ asset('js/core.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/core_extractor.js') }}?v={{ time() }}"></script>

    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Rotas UNIFICADAS -->
    <script>
        const rotas = {
            geral: {
                pesquisar: "{{ route('geral.pesquisar') }}",
                salvar: "{{ route('geral.salvar') }}",
                buscar: "{{ url('/geral/buscar') }}",
                atualizar: "{{ url('/geral/atualizar') }}",
                excluir: "{{ url('/geral/excluir') }}"
            },
            condutor: {
                pesquisar: "{{ route('condutor.pesquisar') }}",
                salvar: "{{ route('condutor.salvar') }}",
                buscar: "{{ url('/condutor/buscar') }}",
                atualizar: "{{ url('/condutor/atualizar') }}",
                excluir: "{{ url('/condutor/excluir') }}",
                ultimos: "{{ route('condutor.ultimos') }}"
            },
            celular: {
                pesquisar: "{{ route('celular.pesquisar') }}",
                salvar: "{{ route('celular.salvar') }}",
                buscar: "{{ url('/celular/buscar') }}",
                atualizar: "{{ url('/celular/atualizar') }}",
                excluir: "{{ url('/celular/excluir') }}"
            }
        };
    </script>

    <!-- Scripts específicos -->
    <script src="{{ asset('js/script_geral.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/script_condutor.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/menu_lateral.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/config_pessoais.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/apreensao_celular/script.js') }}?v={{ time() }}"></script>

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
</body>
</html>

