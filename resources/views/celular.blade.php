<!-- Sub-abas de CELULAR -->
<ul class="nav nav-tabs mt-1" id="subAbasCelular" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#dados-celular" role="tab">
            <i class="bi bi-phone me-1"></i> Dados do Celular
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#controle-celular" role="tab">
            <i class="bi bi-bar-chart-line me-1"></i> Controle de Celulares
        </a>
    </li>
</ul>

<!-- Conteúdo das sub-abas de CELULAR -->
<div class="tab-content mt-2">
    <!-- DADOS DO CELULAR -->
    <div class="tab-pane fade show active" id="dados-celular" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-2">
                <h5 class="mb-0"><i class="bi bi-phone me-2"></i>Cadastro de Celular</h5>
            </div>
            <div class="card-body py-3">
                <form id="formCelular">
                    @csrf
                    <input type="hidden" name="id" id="celular_id">

                    <!-- Primeira linha: Data e IP -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label for="inputDataCelular" class="form-label">Data</label>
                            <input type="text" class="form-control flatpickr-date" placeholder="DD/MM/AAAA" name="data" id="inputDataCelular" required>
                        </div>
                        <div class="col-md-3">
                            <label for="inputIpCelular" class="form-label">IP</label>
                            <input type="text" class="form-control" placeholder="IP" name="ip" id="inputIpCelular">
                        </div>
                        <div class="col-md-3">
                            <label for="inputBoeCelular" class="form-label">BOE</label>
                            <input type="text" class="form-control" placeholder="BOE" name="boe" id="inputBoeCelular" required>
                        </div>
                        <div class="col-md-3">
                            <label for="inputProcessoCelular" class="form-label">Processo - SEI</label>
                            <input type="text" class="form-control" placeholder="0000000000.000000/0000-00" name="processo" id="inputProcessoCelular">
                        </div>
                    </div>

                    <!-- Segunda linha: Pessoa e Telefone -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label for="inputPessoaCelular" class="form-label">Pessoa</label>
                            <input type="text" class="form-control" placeholder="PESSOA" name="pessoa" id="inputPessoaCelular">
                        </div>
                        <div class="col-md-6">
                            <label for="inputTelefoneCelular" class="form-label">Especificação</label>
                            <input type="text" class="form-control" placeholder="ESPECIFICAÇÃO (MARCA/MODELO...)" name="telefone" id="inputTelefoneCelular" maxlength="150">
                        </div>
                    </div>

                    <!-- Terceira linha: IMEI 1, IMEI 2 e Status -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label for="inputImei1Celular" class="form-label">IMEI 1</label>
                            <input type="text" class="form-control" placeholder="IMEI 1" name="imei1" id="inputImei1Celular">
                        </div>
                        <div class="col-md-4">
                            <label for="inputImei2Celular" class="form-label">IMEI 2</label>
                            <input type="text" class="form-control" placeholder="IMEI 2" name="imei2" id="inputImei2Celular">
                        </div>
                        <div class="col-md-4">
                            <label for="inputStatusCelular" class="form-label">Status</label>
                            <select class="form-select" name="status" id="inputStatusCelular">
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
                        <button type="button" class="btn btn-primary" id="btnNovoCelular">
                            <i class="bi bi-file-earmark-plus me-1"></i> NOVO
                        </button>
                        <button type="button" class="btn btn-success" id="btnSalvarCelular">
                            <i class="bi bi-save me-1"></i> SALVAR
                        </button>
                        <button type="button" class="btn btn-warning" id="btnEditarCelular" disabled>
                            <i class="bi bi-pencil-square me-1"></i> EDITAR
                        </button>
                        <button type="button" class="btn btn-danger" id="btnExcluirCelular" disabled>
                            <i class="bi bi-trash me-1"></i> EXCLUIR
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnLimparCelular">
                            <i class="bi bi-x-circle me-1"></i> LIMPAR
                        </button>
                        <button type="button" class="btn btn-secondary" id="btnFecharCelular">
                            <i class="bi bi-x-lg me-1"></i> FECHAR
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Área de Pesquisa -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-light py-2">
                <h5 class="mb-0"><i class="bi bi-search me-2"></i>Pesquisa de Celulares</h5>
            </div>
            <div class="card-body py-3">
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <select class="form-select" id="filtroCelular">
                            <option value="boe" selected>BOE</option>
                            <option value="pessoa">Pessoa</option>
                            <option value="processo">Processo</option>
                            <option value="imei1">IMEI 1</option>
                            <option value="imei2">IMEI 2</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="termoPesquisaCelular" placeholder="Digite para pesquisar...">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" id="btnPesquisarCelular">
                            <i class="bi bi-search me-1"></i> Pesquisar
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="emptyStatePesquisaCelular" class="text-center py-5 rounded bg-light border border-dashed text-muted mt-3">
                    <i class="bi bi-search display-4 text-secondary mb-3 opacity-50"></i>
                    <h5 class="fw-normal">Nenhum resultado exibido</h5>
                    <p class="mb-0 small">Utilize o campo acima para buscar por celulares cadastrados.</p>
                </div>

                <div id="conteinerTabelaPesquisaCelular" style="display: none;">
                    <!-- Paginação info -->
                    <div id="infoPaginacaoCelular" class="d-flex justify-content-between align-items-center mb-2" style="display:none!important;">
                        <small class="text-muted" id="textoTotalCelular"></small>
                        <div class="d-flex gap-1" id="paginacaoCelular"></div>
                    </div>

                    <div class="table-responsive mt-2">
                    <table class="table table-striped table-hover mt-3 w-100" id="tabelaResultadosCelular" style="font-size: 0.95rem; white-space: nowrap; table-layout: fixed; min-width: 1050px;">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 25%;">PESSOA</th>
                                <th style="width: 18%;">PROCESSO - SEI</th>
                                <th style="width: 12%;">BOE</th>
                                <th style="width: 13%;">IMEI 1</th>
                                <th style="width: 13%;">IMEI 2</th>
                                <th style="width: 10%;">STATUS</th>
                                <th style="width: 9%;">AÇÕES</th>
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
    </div>

    <!-- CONTROLE DE CELULARES -->
    <div class="tab-pane fade" id="controle-celular" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Controle de Celulares por Status</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light text-success fw-semibold" id="btnExportarExcelCelular" title="Baixar planilha Excel">
                        <i class="bi bi-file-earmark-excel me-1"></i> Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-light text-danger fw-semibold" id="btnExportarPdfCelular" title="Baixar relatório PDF">
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
                        <label for="filtroDataInicioCelular" class="form-label fw-semibold">Data Início</label>
                        <input type="date" class="form-control" id="filtroDataInicioCelular">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroDataFimCelular" class="form-label fw-semibold">Data Fim</label>
                        <input type="date" class="form-control" id="filtroDataFimCelular">
                    </div>
                    <div class="col-md-3">
                        <label for="filtroStatusCelular" class="form-label fw-semibold">Status</label>
                        <select class="form-select" id="filtroStatusCelular">
                            <option value="">Todos</option>
                            <option value="APREENDIDO">Apreendido</option>
                            <option value="DEVOLVIDO">Devolvido</option>
                            <option value="EM PERÍCIA">Em Perícia</option>
                            <option value="ARQUIVADO">Arquivado</option>
                            <option value="OUTROS">Outros</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-primary w-100" id="btnFiltrarCelulares">
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
                                <canvas id="graficoCelular" style="max-height:200px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Celulares por Status -->
                    <div class="col-md-8">
                        <div class="card border shadow-sm h-100">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="bi bi-table me-1"></i>Detalhamento por Status</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0" id="tabelaControleCelular">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>STATUS</th>
                                                <th class="text-center">QTD</th>
                                                <th>DETALHES</th>
                                                <th class="text-center">AÇÕES</th>
                                            </tr>
                                        </thead>
                                        <tbody id="corpoTabelaControleCelular">
                                            <!-- Os resultados serão inseridos aqui via JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo narrativo -->
                <div class="alert alert-info mt-3 mb-0 py-2" id="resumoNarrativoCelular" style="display:none;">
                    <i class="bi bi-info-circle me-1"></i>
                    <span id="textoResumoNarrativoCelular"></span>
                </div>

                <!-- Modal para Detalhes do Status -->
                <div class="modal fade" id="modalDetalhesStatusCelular" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="modalDetalhesStatusCelularTitulo">Detalhes dos Celulares</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="conteudoDetalhesStatusCelular">
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
<div class="modal fade" id="modalSucessoCelular" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="tituloModalSucessoCelular" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="tituloModalSucessoCelular">Sucesso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="sucessoMensagemCelular"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalErroCelular" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="tituloModalErroCelular" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="tituloModalErroCelular">Erro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="erroMensagemCelular"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmacaoCelular" tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="tituloModalConfirmacaoCelular" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="tituloModalConfirmacaoCelular">Confirmação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir este celular?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarExclusaoCelular">Excluir</button>
            </div>
        </div>
    </div>
</div>

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

    // Definir as rotas para o celular
    var rotasCelular = {
        pesquisar: "{{ route('celular.pesquisar') }}",
        salvar: "{{ route('celular.salvar') }}",
        buscar: "{{ url('/celular/buscar') }}",
        atualizar: "{{ url('/celular/atualizar') }}",
        excluir: "{{ url('/celular/excluir') }}",
        controleStatus: "{{ route('celular.controle.status') }}",
        ultimos: "{{ route('celular.ultimos') }}",
        exportarExcel: "{{ route('celular.exportar.excel') }}",
        exportarPdf: "{{ route('celular.exportar.pdf') }}"
    };
</script>

<!-- Incluir o JavaScript específico do celular -->
<script src="{{ asset('js/celular.js') }}?v={{ time() }}"></script>
