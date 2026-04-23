<!-- Sub-abas de CONDUTOR -->
<ul class="nav nav-tabs mt-1" id="subAbasCondutor" role="tablist" style="margin-top: -10px;">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#dados-condutor" role="tab">Dados Pessoais</a>
    </li>
</ul>

<!-- Conteúdo das sub-abas de CONDUTOR -->
<div class="tab-content mt-2">
    <!-- DADOS PESSOAIS -->
    <div class="tab-pane fade show active" id="dados-condutor" role="tabpanel">
        <form id="formCondutor">
            @csrf
            <input type="hidden" name="id" id="condutor_id">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Nome" name="Nome" id="inputNomeCondutor"
                        required maxlength="100">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Alcunha" name="Alcunha" id="inputAlcunha" maxlength="100">
                </div>
            </div>



            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control date-mask" placeholder="Data de Nascimento (dd/mm/aaaa)"
                        name="Nascimento" id="inputDataNascimento">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Idade" id="inputIdade" readonly>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="EstCivil" id="inputEstadoCivil">
                        <option value="">ESTADO CIVIL</option>
                        <option value="Solteiro(a)">SOLTEIRO(A)</option>
                        <option value="Casado(a)">CASADO(A)</option>
                        <option value="Divorciado(a)">DIVORCIADO(A)</option>
                        <option value="Viúvo(a)">VIÚVO(A)</option>
                        <option value="Separado(a)">SEPARADO(A)</option>
                        <option value="União Estável">UNIÃO ESTÁVEL</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Naturalidade" name="Naturalidade"
                        id="inputNaturalidade" maxlength="50">
            </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <select class="form-control" name="Instrucao" id="inputInstrucao">
                        <option value="">GRAU DE INSTRUÇÃO</option>
                        <option value="Fundamental Completo">FUNDAMENTAL COMPLETO</option>
                        <option value="Fundamental Incompleto">FUNDAMENTAL INCOMPLETO</option>
                        <option value="Médio Completo">MÉDIO COMPLETO</option>
                        <option value="Médio Incompleto">MÉDIO INCOMPLETO</option>
                        <option value="Superior Completo">SUPERIOR COMPLETO</option>
                        <option value="Superior Incompleto">SUPERIOR INCOMPLETO</option>
                        <option value="Pós-graduação">PÓS-GRADUAÇÃO</option>
                        <option value="Analfabeto">ANALFABETO</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control rg-mask" placeholder="RG" name="RG" id="inputRG"
                        maxlength="50">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control cpf-mask" placeholder="CPF" name="CPF" id="inputCPF"
                        required maxlength="15">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control phone-mask" placeholder="Telefone" name="Telefone"
                        id="inputTelefone" maxlength="20">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Profissão" name="Profissao"
                        id="inputProfissao" maxlength="50">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Nome da Mãe" name="Mae" id="inputMae" maxlength="100">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Nome do Pai" name="Pai" id="inputPai" maxlength="100">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <input type="text" class="form-control" placeholder="Endereço" name="Endereco" id="inputEndereco" maxlength="200">
                </div>
            </div>

            <!-- Botões e Pesquisa Rápida de Documentos -->
            <div class="button-group d-flex flex-wrap gap-2 mt-4 mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary btn-action" id="btnNovoCondutor"><i
                    class="bi bi-file-earmark-plus"></i> Novo</button>
                <button type="button" class="btn btn-sm btn-success btn-action" id="btnSalvarCondutor"><i class="bi bi-save"></i>
                    Salvar</button>
                <button type="button" class="btn btn-sm btn-info btn-action" id="btnAddCondutorToChip"><i
                    class="bi bi-plus-circle-fill"></i> Add</button>
                <button type="button" class="btn btn-sm btn-warning btn-action" id="btnEditarCondutor" disabled><i
                    class="bi bi-pencil-square"></i> Editar</button>
                <button type="button" class="btn btn-sm btn-danger btn-action" id="btnExcluirCondutor" disabled><i class="bi bi-trash"></i>
                    Excluir</button>
                <button type="button" class="btn btn-sm btn-secondary btn-action" id="btnLimparCondutor"><i class="bi bi-x-circle"></i>
                    Limpar</button>
                <button type="button" class="btn btn-sm btn-outline-secondary btn-action" id="btnFecharCondutor">Fechar</button>

                <!-- Campo de autocomplete para documentos -->
                <div class="position-relative flex-grow-1"
                    style="min-width: 395px; max-width: 495px;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="termoDocumentoCondutor"
                            placeholder="Digite o documento (ex: DEPOIMENTO)...">
                        <button type="button" class="btn btn-info" id="btnImprimirDocumentoCondutor">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                    <div class="list-group mt-1 position-absolute w-100" id="sugestoesDocumentosCondutor"
                        style="display: none; z-index: 1000;">
                        <!-- Sugestões aparecerão aqui dinamicamente -->
                    </div>
                </div>
            </div>

            <!-- Área de Pesquisa -->
            <div class="mt-1 border p-3 rounded">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="filtroCondutor">
                            <option value="Nome">Nome</option>
                            <option value="Alcunha">Alcunha</option>
                            <option value="RG">RG</option>
                            <option value="CPF">CPF</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" class="form-control" id="termoPesquisaCondutor"
                            placeholder="Digite para pesquisar...">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" id="btnPesquisarCondutor">
                            <i class="bi bi-search"></i> Pesquisar
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover" id="tabelaResultadosCondutor">
                        <thead>
                            <tr>
                                <th>NOME</th>
                                <th>ALCUNHA</th>
                                <th>RG</th>
                                <th>CPF</th>
                                <th>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Os resultados serão inseridos aqui via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal para impressão -->
    <div class="modal fade" id="modalImpressao" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Opções de Impressão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Impressora</label>
                        <select class="form-select" id="seletorImpressora">
                            <option value="padrao">Impressora Padrão</option>
                            <option value="pdf">Salvar como PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Orientação</label>
                        <select class="form-select" id="orientacaoImpressao">
                            <option value="retrato">Retrato</option>
                            <option value="paisagem">Paisagem</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Margens (mm)</label>
                        <input type="number" class="form-control" id="margemImpressao" value="15" min="0" max="50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarImpressao">Imprimir</button>
                </div>
            </div>
        </div>
    </div>


</div>

