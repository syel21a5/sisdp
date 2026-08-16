<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Início Simples | SYS-DP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="bg-light">
    <div class="container-fluid py-3" style="max-width: 1200px;">
        <!-- Cabeçalho do documento -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-center text-center">
                    <img src="{{ asset('images/b_PE.jpg') }}" alt="Brasão PE" style="width: 70px; height: 70px; object-fit: contain;">
                    <div class="mx-3">
                        <div class="fw-bold" style="font-size: 12pt;">POLÍCIA CIVIL DE PERNAMBUCO - PCPE</div>
                        <div style="font-size: 9.5pt;">Diretoria Integrada do Interior - 2 da Policia Civil – DINTER - 2</div>
                        <div style="font-size: 9.5pt;">Gerência de Controle Operacional do Interior - 2 – GCOI - 2</div>
                        <div style="font-size: 9.5pt;">20ª Delegacia Seccional de Polícia – Afogados da Ingazeira – 20ª DESEC</div>
                    </div>
                    <img src="{{ asset('images/b_PCPE.png') }}" alt="Brasão PCPE" style="width: 70px; height: 70px; object-fit: contain;">
                </div>
            </div>
        </div>

        <!-- BOE + Importar -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h5 class="card-title fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Documento de Pesquisa</h5>
                <div class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold">BOE</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="inputBOESimples" placeholder="Nº do BOE">
                            <button class="btn btn-info" type="button" id="btnImportarBoeSimples" title="Importar dados do BOE">
                                <i class="bi bi-upload"></i> Importar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-bold">Documento a gerar</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="termoDocumentoSimples" placeholder="Digite ou selecione o documento...">
                            <button class="btn btn-primary" type="button" id="btnImprimirSimples">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                        </div>
                        <div id="sugestoesDocumentosSimples" class="list-group position-absolute" style="display:none; z-index:1050; max-height:250px; overflow:auto;"></div>
                    </div>
                </div>
                <div class="text-muted small mt-2">
                    <i class="bi bi-info-circle me-1"></i> Os dados extraídos são <strong>descartáveis</strong>: nada é salvo no banco. Use apenas para imprimir a peça.
                </div>
            </div>
        </div>

        <!-- Dados gerais do documento (descartáveis) -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-secondary"><i class="bi bi-card-list me-2"></i>Dados do Documento</h6>
                <div class="row g-2">
                    <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="Data (DD/MM/AAAA)" id="inputDataSimples"></div>
                    <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="Data por Extenso" id="inputDataExtSimples"></div>
                    <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="IP" id="inputIPSimples"></div>
                    <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="BOE PM" id="inputBOEPMSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Delegado" id="inputDelegadoSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Escrivão" id="inputEscrivaoSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Delegacia" id="inputDelegaciaSimples"></div>
                    <div class="col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Cidade" id="inputCidadeSimples"></div>
                    <div class="col-md-6"><input type="text" class="form-control form-control-sm" placeholder="Natureza / Incidência Penal" id="inputNaturezaSimples"></div>
                </div>
            </div>
        </div>

        <!-- Chips dos envolvidos (voláteis) -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="fw-bold text-secondary"><i class="bi bi-people me-2"></i>Envolvidos no BOE</h6>
                <div id="chipsEnvolvidosSimples" class="d-flex flex-wrap gap-2">
                    <span class="text-muted small">Importe um BOE para ver os envolvidos aqui. Nada é salvo.</span>
                </div>
            </div>
        </div>

        <!-- Formulário único do envolvido -->
        <div class="card shadow-sm border-0 mb-3" id="cardFormEnvolvido" style="display:none;">
            <div class="card-body">
                <h6 class="fw-bold text-secondary">
                    <i class="bi bi-person me-2"></i>Dados do Envolvido
                    <button class="btn btn-sm btn-outline-danger float-end" type="button" id="btnLimparEnvolvidoSimples"><i class="bi bi-x-circle"></i> Limpar</button>
                </h6>
                <div class="row g-2">
                    <div class="col-md-8"><input type="text" class="form-control form-control-sm" placeholder="Nome completo" id="inputNomeEnvolvidoSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Alcunha" id="inputAlcunhaSimples"></div>
                    <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="Nascimento" id="inputNascimentoSimples"></div>
                    <div class="col-md-2"><input type="text" class="form-control form-control-sm" placeholder="Idade" id="inputIdadeSimples"></div>
                    <div class="col-md-3"><input type="text" class="form-control form-control-sm" placeholder="RG" id="inputRGSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="CPF" id="inputCPFSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Profissão" id="inputProfissaoSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Estado Civil" id="inputEstCivilSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Naturalidade" id="inputNaturalidadeSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Telefone" id="inputTelefoneSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Mãe" id="inputMaeSimples"></div>
                    <div class="col-md-4"><input type="text" class="form-control form-control-sm" placeholder="Pai" id="inputPaiSimples"></div>
                    <div class="col-12"><input type="text" class="form-control form-control-sm" placeholder="Endereço" id="inputEnderecoSimples"></div>
                    <div class="col-12"><textarea class="form-control form-control-sm" id="inputTipoPenalSimples" rows="1" placeholder="Tipificação Penal"></textarea></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Importação (Texto/PDF) - reutiliza o componente com suffix -->
    @include('components.modal_importacao_boe', ['suffix' => 'Simples', 'modalId' => 'modalImportarBoeSimples'])

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/DocumentoService.js') }}"></script>
    <script src="{{ asset('js/rotas_impressao.js') }}"></script>
    <script src="{{ asset('js/wf_inicio_simples.js') }}?v={{ time() }}"></script>
</body>
</html>
