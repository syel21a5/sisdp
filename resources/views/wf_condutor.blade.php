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
                <div class="col-lg-6 col-12">
                    <input type="text" class="form-control" placeholder="Nome" name="Nome" id="inputNomeCondutor" required>
                </div>
                <div class="col-lg-6 col-12">
                    <input type="text" class="form-control" placeholder="Alcunha" name="Alcunha" id="inputAlcunha">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <input type="text" class="form-control date-mask" placeholder="Data de Nascimento (dd/mm/aaaa)" name="Nascimento" id="inputDataNascimento">
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <input type="text" class="form-control" placeholder="Idade" id="inputIdade" readonly>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <select class="form-control" name="EstCivil" id="inputEstadoCivil">
                        <option value="">Estado Civil</option>
                        <option value="Solteiro(a)">Solteiro(a)</option>
                        <option value="Casado(a)">Casado(a)</option>
                        <option value="Divorciado(a)">Divorciado(a)</option>
                        <option value="Viúvo(a)">Viúvo(a)</option>
                    </select>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <input type="text" class="form-control" placeholder="Naturalidade" name="Naturalidade" id="inputNaturalidade">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-xl-3 col-lg-6 col-md-12">
                    <select class="form-control" name="Instrucao" id="inputInstrucao">
                        <option value="">GRAU DE INSTRUÇÃO</option>
                        <option value="FUNDAMENTAL COMPLETO">FUNDAMENTAL COMPLETO</option>
                        <option value="FUNDAMENTAL INCOMPLETO">FUNDAMENTAL INCOMPLETO</option>
                        <option value="MÉDIO COMPLETO">MÉDIO COMPLETO</option>
                        <option value="MEDIO INCOMPLETO">MEDIO INCOMPLETO</option>
                        <option value="SUPERIOR COMPLETO">SUPERIOR COMPLETO</option>
                        <option value="SUPERIOR INCOMPLETO">SUPERIOR INCOMPLETO</option>
                        <option value="ANALFABETO">ANALFABETO</option>
                        <option value="ASSINA O NOME">ASSINA O NOME</option>
                    </select>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <input type="text" class="form-control rg-mask" placeholder="RG" name="RG" id="inputRG" maxlength="50">
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <input type="text" class="form-control cpf-mask" placeholder="CPF" name="CPF" id="inputCPF" required>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-12">
                    <input type="text" class="form-control phone-mask" placeholder="Telefone" name="Telefone" id="inputTelefone">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-lg-3 col-md-12">
                    <input type="text" class="form-control" placeholder="Profissão" name="Profissao" id="inputProfissao">
                </div>
                <div class="col-lg-4 col-md-6">
                    <input type="text" class="form-control" placeholder="Nome da Mãe" name="Mae" id="inputMae">
                </div>
                <div class="col-lg-5 col-md-6">
                    <input type="text" class="form-control" placeholder="Nome do Pai" name="Pai" id="inputPai">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12">
                    <input type="text" class="form-control" placeholder="Endereço" name="Endereco" id="inputEndereco">
                </div>
            </div>

            <!-- Botões e Pesquisa Rápida de Documentos -->
            <div class="button-group d-flex flex-wrap gap-2 mt-4 mb-3">
                <button type="button" class="btn btn-primary" id="btnNovoCondutor"><i class="bi bi-file-earmark-plus"></i> Novo</button>
                <button type="button" class="btn btn-success" id="btnSalvarCondutor"><i class="bi bi-save"></i> Salvar</button>
                <button type="button" class="btn btn-warning" id="btnEditarCondutor" disabled><i class="bi bi-pencil-square"></i> Editar</button>
                <button type="button" class="btn btn-danger" id="btnExcluirCondutor" disabled><i class="bi bi-trash"></i> Excluir</button>
                <button type="button" class="btn btn-secondary" id="btnLimparCondutor"><i class="bi bi-x-circle"></i> Limpar</button>

                <!-- Campo de autocomplete para documentos -->
                <div class="position-relative flex-grow-1" style="min-width: 250px; max-width: 350px;">
                    <div class="input-group">
                        <input type="text" class="form-control" id="termoDocumentoCondutor"
                               placeholder="Digite o documento (ex: TERMO DE DECLARAÇÃO)...">
                        <button type="button" class="btn btn-info" id="btnImprimirDocumentoCondutor">
                            <i class="bi bi-printer"></i> Imprimir
                        </button>
                    </div>
                    <div class="list-group mt-1 position-absolute w-100" id="sugestoesDocumentosCondutor" style="display: none; z-index: 1000;">
                        <!-- Sugestões aparecerão aqui dinamicamente -->
                    </div>
                </div>
            </div>

            <!-- Área de Pesquisa -->
            <div class="mt-4 border p-3 rounded">
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
                        <input type="text" class="form-control" id="termoPesquisaCondutor" placeholder="Digite para pesquisar...">
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

<script>
    // Definir as rotas para impressão de documentos
    var rotasImpressao = {
        // DEFINIAR AS ROTAS DE IMPRESSAO DESVINCULADAS A NUMOFICIOCONTROLLER
        'TERMO DE DECLARACAO': "{{ route('declaracao', ['dados' => '--DADOS--']) }}",
        'TERMO DE DEPOIMENTO': "{{ route('depoimento', ['dados' => '--DADOS--']) }}",
        'TERMO DE INTERROGATORIO': "{{ route('interrogatorio', ['dados' => '--DADOS--']) }}",

        // DEFINIAR AS ROTAS DE IMPRESSAO VINCULADAS A NUMOFICIOCONTROLLER
        'AUTO DE APRESENTACAO E APREENSAO': "{{ route('auto.apreensao', ['dados' => '--DADOS--']) }}",
        'TERMO DE RESTITUICAO': "{{ route('termo.restituicao', ['dados' => '--DADOS--']) }}",
        'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO': "{{ route('termo.renuncia.representacao', ['dados' => '--DADOS--']) }}",
        'TERMO DE REPRESENTACAO': "{{ route('termo.representacao', ['dados' => '--DADOS--']) }}",
        'TERMO DE COMPROMISSO': "{{ route('termo.compromisso.juizo', ['dados' => '--DADOS--']) }}",

        // DEFINIAR AS ROTAS DE IMPRESSAO p/ PERICIAS OU LAUDOS AQUI ABAIXO
        "LAUDO TRAUMATOLOGICO IML": "{{ route('termo.traumatologico.iml', ['dados' => '--DADOS--']) }}",

        // DEFINIAR AS ROTAS DE IMPRESSAO ( MANDADO DE PRISAO ) AQUI ABAIXO
        'OFICIOS MANDADO DE PRISAO': "{{ route('numoficio.mp', ['dados' => '--DADOS--']) }}"
    };
</script>

