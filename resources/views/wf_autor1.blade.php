<!-- Sub-abas de AUTOR1 -->
<ul class="nav nav-tabs mt-1" id="subAbasAutor1" role="tablist" style="margin-top: -10px;">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#dados-autor1" role="tab">Dados Pessoais</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#dados-complementares" role="tab">Dados Complementares</a>
    </li>
</ul>

<!-- Conteúdo das sub-abas de AUTOR1 -->
<div class="tab-content mt-2">
    <!-- DADOS PESSOAIS -->
    <div class="tab-pane fade show active" id="dados-autor1" role="tabpanel">
        <form id="formAutor1">
            @csrf
            <input type="hidden" name="id" id="autor1_id">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Nome" name="Nome" id="inputNomeAutor1"
                        required maxlength="100">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Alcunha" name="Alcunha"
                        id="inputAlcunhaAutor1" maxlength="100">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control date-mask" placeholder="Data de Nascimento (dd/mm/aaaa)"
                        name="Nascimento" id="inputDataNascimentoAutor1">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Idade" id="inputIdadeAutor1" readonly>
                </div>
                <div class="col-md-3">
                    <select class="form-control" name="EstCivil" id="inputEstadoCivilAutor1">
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
                        id="inputNaturalidadeAutor1" maxlength="50">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <select class="form-control" name="Instrucao" id="inputInstrucaoAutor1">
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
                    <input type="text" class="form-control rg-mask" placeholder="RG" name="RG" id="inputRGAutor1"
                        maxlength="50">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control cpf-mask" placeholder="CPF" name="CPF" id="inputCPFAutor1"
                        required maxlength="15">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control phone-mask" placeholder="Telefone" name="Telefone"
                        id="inputTelefoneAutor1" maxlength="20">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Profissão" name="Profissao"
                        id="inputProfissaoAutor1" maxlength="50">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Nome da Mãe" name="Mae" id="inputMaeAutor1" maxlength="100">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Nome do Pai" name="Pai" id="inputPaiAutor1" maxlength="100">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <input type="text" class="form-control" placeholder="Endereço" name="Endereco"
                        id="inputEnderecoAutor1" maxlength="200">
                </div>
            </div>

            <!-- Botões e Pesquisa Rápida de Documentos -->
            <div class="button-group d-flex flex-wrap gap-2 mt-4 mb-3">
                <button type="button" class="btn btn-sm btn-outline-primary btn-action" id="btnNovoAutor1"><i class="bi bi-file-earmark-plus"></i>
                    Novo</button>
                <button type="button" class="btn btn-sm btn-success btn-action" id="btnSalvarAutor1"><i class="bi bi-save"></i>
                    Salvar</button>
                <button type="button" class="btn btn-sm btn-info btn-action" id="btnAddAutor1ToChip"><i
                        class="bi bi-plus-circle-fill"></i> Add</button>
                <button type="button" class="btn btn-sm btn-warning btn-action" id="btnEditarAutor1" disabled><i
                        class="bi bi-pencil-square"></i> Editar</button>
                <button type="button" class="btn btn-sm btn-danger btn-action" id="btnExcluirAutor1" disabled><i class="bi bi-trash"></i>
                    Excluir</button>
                <button type="button" class="btn btn-sm btn-secondary btn-action" id="btnLimparAutor1"><i class="bi bi-x-circle"></i>
                    Limpar</button>
                <button type="button" class="btn btn-sm btn-outline-secondary btn-action" id="btnFecharAutor1">Fechar</button>

                <!-- Campo de autocomplete para documentos -->
                <div class="position-relative flex-grow-1"
                    style="min-width: 395px; max-width: 495px;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="termoDocumentoAutor1"
                            placeholder="Digite o documento (ex: INTERROGATÓRIO)...">
                        <button type="button" class="btn btn-info" id="btnImprimirDocumentoAutor1">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                    <div class="list-group mt-1 position-absolute w-100" id="sugestoesDocumentosAutor1"
                        style="display: none; z-index: 1000;">
                        <!-- Sugestões aparecerão aqui dinamicamente -->
                    </div>
                </div>
            </div>

            <!-- Área de Pesquisa -->
            <div class="mt-1 border p-3 rounded">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <select class="form-select" id="filtroAutor1">
                            <option value="Nome">Nome</option>
                            <option value="Alcunha">Alcunha</option>
                            <option value="RG">RG</option>
                            <option value="CPF">CPF</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <input type="text" class="form-control" id="termoPesquisaAutor1"
                            placeholder="Digite para pesquisar...">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" id="btnPesquisarAutor1">
                            <i class="bi bi-search"></i> Pesquisar
                        </button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover" id="tabelaResultadosAutor1">
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

    <!-- DADOS COMPLEMENTARES -->
    <div class="tab-pane fade" id="dados-complementares" role="tabpanel">
        <form id="formDadosComplementares">
            @csrf
            <input type="hidden" name="id" id="complementar_autor1_id">

            <!-- Linha 1: Tipo Penal, Nº Mandado e Data do Mandado -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Tipo Penal" name="TipoPenal"
                        id="inputTipoPenalAutor1">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Número do Mandado" name="Nmandado"
                        id="inputNmandadoAutor1">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Data do Mandado" name="DataMandado"
                        id="inputDataMandadoAutor1">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Fiança (valor)" name="Fianca"
                        id="inputFiancaAutor1">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" placeholder="Fiança por Extenso" name="FiancaExt"
                        id="inputFiancaExtAutor1">
                </div>
                <div class="col-md-2">
                    <div class="d-flex align-items-center">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="inputFiancaPagoAutor1"
                                name="FiancaPago">
                            <label class="form-check-label ms-1" for="inputFiancaPagoAutor1">Fiança</label>
                        </div>
                        <span id="badgeFiancaPagoAutor1" class="badge bg-danger ms-2">NÃO PAGA</span>
                    </div>
                </div>
            </div>

            <!-- Linha 3: Parente, Família e Advogado -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Parente" name="Parente"
                        id="inputParenteAutor1">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Família" name="Familia"
                        id="inputFamiliaAutor1">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Advogado" name="Advogado"
                        id="inputAdvogadoAutor1">
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

