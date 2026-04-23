<!-- Sub-abas de OUTRO -->
<ul class="nav nav-tabs mt-1" id="subAbasOutro" role="tablist" style="margin-top: -10px;">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#dados-outro" role="tab">Dados Pessoais</a>
    </li>
</ul>

<!-- Conteúdo das sub-abas de OUTRO -->
<div class="tab-content mt-2">
    <!-- DADOS PESSOAIS -->
    <div class="tab-pane fade show active" id="dados-outro" role="tabpanel">
        <form id="formOutro">
            @csrf
            <input type="hidden" name="id" id="outro_id">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Nome" name="Nome" id="inputNomeOutro"
                        required maxlength="100">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Alcunha" name="Alcunha"
                        id="inputAlcunhaOutro" maxlength="100">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control date-mask" placeholder="Data de Nascimento (dd/mm/aaaa)"
                        name="Nascimento" id="inputDataNascimentoOutro">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Idade" id="inputIdadeOutro" readonly>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="EstCivil" id="inputEstadoCivilOutro">
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
                        id="inputNaturalidadeOutro" maxlength="50">
            </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <select class="form-control" name="Instrucao" id="inputInstrucaoOutro">
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
                    <input type="text" class="form-control rg-mask" placeholder="RG" name="RG" id="inputRGOutro"
                        maxlength="50">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control cpf-mask" placeholder="CPF" name="CPF"
                        id="inputCPFOutro" required maxlength="15">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control phone-mask" placeholder="Telefone" name="Telefone"
                        id="inputTelefoneOutro" maxlength="20">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Profissão" name="Profissao"
                        id="inputProfissaoOutro" maxlength="50">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Nome da Mãe" name="Mae"
                        id="inputMaeOutro" maxlength="100">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Nome do Pai" name="Pai"
                        id="inputPaiOutro" maxlength="100">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <input type="text" class="form-control" placeholder="Endereço" name="Endereco"
                        id="inputEnderecoOutro" maxlength="200">
                </div>
            </div>

            <!-- Botões e Pesquisa Rápida de Documentos -->
            <div class="button-group d-flex flex-wrap gap-2 mt-4 mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary btn-action" id="btnNovoOutro"><i
                        class="bi bi-file-earmark-plus"></i> Novo</button>
                <button type="button" class="btn btn-sm btn-success btn-action" id="btnSalvarOutro"><i class="bi bi-save"></i>
                    Salvar</button>
                <button type="button" class="btn btn-sm btn-info btn-action" id="btnAddOutroToChip"><i
                        class="bi bi-plus-circle-fill"></i> Add</button>
                <button type="button" class="btn btn-sm btn-warning btn-action" id="btnEditarOutro" disabled><i
                        class="bi bi-pencil-square"></i> Editar</button>
                <button type="button" class="btn btn-sm btn-danger btn-action" id="btnExcluirOutro" disabled><i
                        class="bi bi-trash"></i> Excluir</button>
                <button type="button" class="btn btn-sm btn-secondary btn-action" id="btnLimparOutro"><i class="bi bi-x-circle"></i>
                    Limpar</button>
                <button type="button" class="btn btn-sm btn-outline-secondary btn-action" id="btnFecharOutro">Fechar</button>

                <!-- Campo de autocomplete para documentos -->
                <div class="position-relative flex-grow-1"
                    style="min-width: 395px; max-width: 495px;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="termoDocumentoOutro"
                            placeholder="Digite o documento (ex: DEPOIMENTO)...">
                        <button type="button" class="btn btn-info" id="btnImprimirDocumentoOutro">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                    <div class="list-group mt-1 position-absolute w-100" id="sugestoesDocumentosOutro"
                        style="display: none; z-index: 1000;">
                        <!-- Sugestões aparecerão aqui dinamicamente -->
                    </div>
                </div>
            </div>

            <!-- Área de Pesquisa -->
            <div class="mt-1 border p-3 rounded">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="filtroOutro">
                            <option value="Nome">Nome</option>
                            <option value="Alcunha">Alcunha</option>
                            <option value="RG">RG</option>
                            <option value="CPF">CPF</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" class="form-control" id="termoPesquisaOutro"
                            placeholder="Digite para pesquisar...">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" id="btnPesquisarOutro">
                            <i class="bi bi-search"></i> Pesquisar
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover" id="tabelaResultadosOutro">
                        <thead>
                            <tr>
                                <th>NOME</th>
                                <th>MÃE</th>
                                <th>NASC.</th>
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
</div>

