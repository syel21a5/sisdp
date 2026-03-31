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
    /* Cores de chips personalizadas (mais escuras para melhor leitura) */
    .bg-chip-vitima { background-color: #f1c40f !important; color: #000 !important; } /* Amarelo ouro/escuro */
    .bg-chip-testemunha { background-color: #2980b9 !important; color: #fff !important; } /* Azul mais profundo */
    .bg-chip-autor { background-color: #c0392b !important; color: #fff !important; } /* Vermelho mais escuro/sólido */
    .bg-chip-envolvido { background-color: #7f8c8d !important; color: #fff !important; } /* Cinza asfalto */
    
    .chip-selecao:hover {
        filter: brightness(0.9);
        transform: scale(1.02);
    }
</style>

<!-- Conteúdo das sub-abas de INTIMAÇÃO -->
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
                    <button type="button" class="btn btn-sm btn-dark" id="btnFecharIntimacao" title="Fechar Aba">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body py-3">
                <form id="formIntimacao">
                    @csrf
                    <input type="hidden" name="id" id="intimacao_id">
                    <input type="hidden" name="Tipo" id="inputTipoEnvolvidoIntimacao">

                    <!-- Primeira linha: Data, Data Comp, Data Oitiva e Horário -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-2">
                            <label for="inputDataIntimacao" class="form-label">Data</label>
                            <input type="text" class="form-control flatpickr-date" placeholder="DD/MM/AAAA" name="data" id="inputDataIntimacao" required>
                        </div>
                        <div class="col-md-4">
                            <label for="inputDataCompIntimacao" class="form-label">Data Completa</label>
                            <input type="text" class="form-control" placeholder="DATA COMPLETA" name="data_comp" id="inputDataCompIntimacao" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="inputDataOitivaIntimacao" class="form-label">Data da Oitiva</label>
                            <input type="text" class="form-control flatpickr-date" placeholder="DD/MM/AAAA" name="dataoitiva" id="inputDataOitivaIntimacao">
                        </div>
                        <div class="col-md-3">
                            <label for="inputHorarioIntimacao" class="form-label">Horário</label>
                            <input type="text" class="form-control flatpickr-time" placeholder="HH:MM" name="hora" id="inputHorarioIntimacao">
                        </div>
                    </div>

                    <!-- Segunda linha: Delegado e Escrivão -->
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

                    <!-- Terceira linha: Delegacia e Cidade -->
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

                    <!-- Quarta linha: Referência (BOE), Nome e Telefone -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label for="inputReferenciaIntimacao" class="form-label">Referência (BOE)</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="BOE" name="BOE" id="inputReferenciaIntimacao">
                                <button class="btn btn-outline-primary" type="button" id="btnImportarBoeIntimacao" title="Carregar envolvidos">
                                    <i class="bi bi-cloud-download"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <label for="inputNomeIntimacao" class="form-label">Nome</label>
                            <input type="text" class="form-control" placeholder="NOME" name="Nome" id="inputNomeIntimacao" required>
                        </div>
                        <div class="col-md-2">
                            <label for="inputTelefoneIntimacao" class="form-label">Telefone</label>
                            <input type="text" class="form-control phone-mask" placeholder="TELEFONE" name="Telefone" id="inputTelefoneIntimacao">
                        </div>
                    </div>

                    <!-- Quinta linha: Endereço (largura total) -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-12">
                            <label for="inputEnderecoIntimacao" class="form-label">Endereço</label>
                            <input type="text" class="form-control" placeholder="ENDEREÇO" name="Endereco" id="inputEnderecoIntimacao">
                        </div>
                    </div>

                    <!-- Container para Chips -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-12">
                            <div id="chipsIntimacaoContainer" class="d-flex flex-wrap gap-2 mt-1"></div>
                        </div>
                    </div>


                </form>
            </div>
        </div>

        <!-- Área de Pesquisa - Mais próxima do primeiro card -->
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
                                <th>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Os resultados serão inseridos aqui via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTROLE DE INTIMAÇÕES -->
    <div class="tab-pane fade" id="controle-intimacao" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Controle de Intimações por Data</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle me-2 fs-5"></i>
                            <div>
                                Visualize as intimações agendadas para os próximos dias. Clique em uma data para ver os detalhes.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards de Resumo -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center py-3">
                                <h4 class="card-title mb-1" id="contador-hoje">0</h4>
                                <p class="card-text mb-0">Para Hoje</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center py-3">
                                <h4 class="card-title mb-1" id="contador-amanha">0</h4>
                                <p class="card-text mb-0">Para Amanhã</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center py-3">
                                <h4 class="card-title mb-1" id="contador-semana">0</h4>
                                <p class="card-text mb-0">Próximos 7 Dias</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-secondary text-white">
                            <div class="card-body text-center py-3">
                                <h4 class="card-title mb-1" id="contador-total">0</h4>
                                <p class="card-text mb-0">Total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="filtroDataInicio" class="form-label fw-semibold">Data Início</label>
                        <input type="date" class="form-control" id="filtroDataInicio">
                    </div>
                    <div class="col-md-4">
                        <label for="filtroDataFim" class="form-label fw-semibold">Data Fim</label>
                        <input type="date" class="form-control" id="filtroDataFim">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="btnFiltrarIntimacoes">
                            <i class="bi bi-funnel me-1"></i> Filtrar
                        </button>
                    </div>
                </div>

                <!-- Tabela de Intimações por Data -->
                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover" id="tabelaControleIntimacao">
                        <thead class="table-light">
                            <tr>
                                <th>DATA</th>
                                <th>QUANTIDADE</th>
                                <th>DETALHES</th>
                                <th>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabelaControle">
                            <!-- Os resultados serão inseridos aqui via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Modal para Detalhes da Data -->
                <div class="modal fade" id="modalDetalhesData" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="modalDetalhesDataTitulo">Detalhes das Intimações</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="conteudoDetalhesData">
                                    <!-- Conteúdo será carregado via JavaScript -->
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
<div class="modal fade" id="modalSucessoIntimacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Sucesso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="sucessoMensagemIntimacao"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalErroIntimacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Erro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="erroMensagemIntimacao"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmacaoIntimacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Confirmação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta intimação?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoIntimacao">Excluir</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Importar BOE Híbrido -->
<div class="modal fade" id="modalImportarBoeIntimacao" tabindex="-1" aria-labelledby="modalImportarBoeIntimacaoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportarBoeIntimacaoLabel">Importar Histórico do BOE com IA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Abas de Navegação -->
                <ul class="nav nav-tabs mb-3" id="boeImportTabsIntimacao" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-texto-intimacao" data-bs-toggle="tab" data-bs-target="#content-texto-intimacao" type="button" role="tab"><i class="bi bi-card-text me-1"></i> 📝 Colar Texto (Rápido)</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-pdf-intimacao" data-bs-toggle="tab" data-bs-target="#content-pdf-intimacao" type="button" role="tab"><i class="bi bi-file-earmark-pdf text-danger me-1"></i> 📄 Enviar Arquivo PDF</button>
                    </li>
                </ul>
                
                <div class="tab-content" id="boeImportTabsContentIntimacao">
                    <!-- Aba de Texto -->
                    <div class="tab-pane fade show active" id="content-texto-intimacao" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="bi bi-magic me-1"></i> <strong>Extração Inteligente:</strong> Copie o texto completo do Boletim de Ocorrência e cole abaixo.
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" id="textoBoeIntimacao" rows="11" placeholder="Cole o texto aqui..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Aba de PDF -->
                    <div class="tab-pane fade" id="content-pdf-intimacao" role="tabpanel">
                        <div class="alert alert-warning">
                            <i class="bi bi-cpu me-1"></i> <strong>Processamento Nativo:</strong> O arquivo PDF será aberto internamente e mapeado pela inteligência artificial.
                        </div>
                        <div class="mb-3">
                            <label for="pdfBoeIntimacao" class="form-label fw-bold">Selecione o arquivo PDF do BOE:</label>
                            <input class="form-control form-control-lg" type="file" id="pdfBoeIntimacao" accept=".pdf">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barra de Progresso da IA -->
            <div id="boeProgressWrapperIntimacao" class="px-3 pb-2" style="display:none;">
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted fw-bold" id="boeProgressLabelIntimacao">🤖 A IA está lendo o documento...</small>
                    <small class="text-muted" id="boeProgressPercentIntimacao">0%</small>
                </div>
                <div class="progress" style="height: 10px; border-radius: 10px;">
                    <div id="boeProgressBarIntimacao" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; border-radius: 10px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="btnProcessarBoeIntimacao">Processar com IA</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializa o flatpickr
    document.addEventListener('DOMContentLoaded', function() {
        if (window.flatpickr) {
            flatpickr(".flatpickr-date", {
                dateFormat: "d/m/Y",
                allowInput: true,
                locale: "pt"
            });

            flatpickr(".flatpickr-time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                allowInput: true,
                locale: "pt"
            });
        }
    });

    // Definir as rotas para a intimação
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

    // Rotas de impressão específicas para intimação
    var rotasImpressaoIntimacao = {
        'EDITOR DE INTIMAÇÃO': "{{ route('intimacao.editor', '--DADOS--') }}"
    };
</script>

<!-- Incluir o JavaScript específico da intimação -->
<script src="{{ asset('js/script_intimacao.js') }}"></script>
