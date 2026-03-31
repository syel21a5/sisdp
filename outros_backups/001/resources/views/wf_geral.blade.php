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
                        <li><a href="#" data-subtab-id="aba-celulares" data-subtab-title="Celulares" data-subtab-url="{{ route('celular.index') }}"><i class="bi bi-phone"></i>Celulares</a></li>
                        @endif
                        @if($canVeiculo)
                        <li><a href="#" data-subtab-id="aba-veiculos" data-subtab-title="Veículos" data-subtab-url="{{ route('veiculo.index') }}"><i class="bi bi-car-front"></i>Veículos</a></li>
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
                <a href="#" data-subtab-id="aba-intimacao-din" data-subtab-title="Intimação" data-subtab-url="{{ route('intimacao.index') }}">
                    <i class="bi bi-envelope-paper"></i>Intimação
                </a>
            </li>
            @endif
            <li>
                <div class="sidebar-group-card">
                    <button class="menu-toggle" type="button" onclick="toggleSubmenu('relatorios-submenu')">
                        <span><i class="bi bi-file-earmark-bar-graph-fill"></i>Relatórios</span>
                        <i class="bi bi-chevron-down"></i>
                    </button>
                    <ul class="sidebar-submenu" id="relatorios-submenu" style="display: none;">
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
                                    <div class="col-lg-4 col-md-12"><input type="text" class="form-control" placeholder="IP" name="ip" id="inputIP"></div>
                                    <div class="col-lg-4 col-md-6"><input type="text" class="form-control" placeholder="BOE" name="boe" id="inputBOE"></div>
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

                                <!-- Botões de ação -->
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <button type="button" class="btn btn-primary" id="btnNovo"><i class="bi bi-file-earmark-plus"></i> Novo</button>
                                    <button type="button" class="btn btn-success" id="btnSalvar"><i class="bi bi-save"></i> Salvar</button>
                                    <button type="button" class="btn btn-warning" id="btnEditar" disabled><i class="bi bi-pencil-square"></i> Editar</button>
                                    <button type="button" class="btn btn-danger" id="btnExcluir" disabled><i class="bi bi-trash"></i> Excluir</button>
                                    <button type="button" class="btn btn-secondary" id="btnLimpar"><i class="bi bi-x-circle"></i> Limpar</button>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <select class="form-select" id="ddlFiltro">
                                            <option value="BOE" selected>BOE</option>
                                            <option value="IP">IP</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="txtPesquisa" placeholder="Digite o termo para pesquisa">
                                    </div>
                                    <div class="col-md-3">
                                        <button class="btn btn-primary w-100" type="button" id="btnPesquisar">
                                            <i class="bi bi-search"></i> Pesquisar
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle" id="gridResultados">
                                        <thead>
                                            <tr>
                                                <th>BOE</th>
                                                <th>IP</th>
                                                <th>DATA</th>
                                                <th>AÇÕES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="4" class="text-center">Nenhum registro encontrado. Realize uma pesquisa.</td>
                                            </tr>
                                        </tbody>
                                    </table>
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

            <!-- Modal de Sucesso -->
            <div class="modal fade" id="modalSucesso" tabindex="-1" aria-labelledby="modalSucessoLabel" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="modalSucessoLabel">Sucesso</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            Registro salvo com sucesso!
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal">OK</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Erro -->
            <div class="modal fade" id="modalErro" tabindex="-1" aria-labelledby="modalErroLabel" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalErroLabel">Erro</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center" id="erroMensagem">
                            <!-- Mensagem de erro será preenchida via JavaScript -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Confirmação de Exclusão -->
            <div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-labelledby="modalConfirmacaoLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalConfirmacaoLabel">Confirmar Exclusão</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Tem certeza que deseja excluir este registro?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Confirmação de Exclusão para Condutor -->
            <div class="modal fade" id="modalConfirmacaoCondutor" tabindex="-1" aria-labelledby="modalConfirmacaoCondutorLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalConfirmacaoCondutorLabel">Confirmar Exclusão</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Tem certeza que deseja excluir este condutor?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoCondutor">Excluir</button>
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
    <script src="{{ asset('js/script_geral.js') }}"></script>
    <script src="{{ asset('js/script_condutor.js') }}"></script>
    <script src="{{ asset('js/menu_lateral.js') }}"></script>
    <script src="{{ asset('js/apreensao_celular/script.js') }}"></script>
</body>
</html>
