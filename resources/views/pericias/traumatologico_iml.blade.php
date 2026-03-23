<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Traumatológico IML - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    <style>
        /* AJUSTE PRINCIPAL: Reduzir drasticamente o espaçamento entre parágrafos */
        #editor p {
            line-height: 1.6 !important;
            margin: 0.2em 0 !important;
        }

        .ql-editor p {
            line-height: 1.6 !important;
            margin: 0.2em 0 !important;
        }

        /* Espaçamento ainda menor para a área de assinaturas */
        .assinatura-area p {
            margin: 0.1em 0 !important;
            line-height: 1.4 !important;
        }

        /* PRESERVAR TABS E ESPAÇOS - IMPORTANTE PARA IMPRESSÃO */
        .preservar-espacamento {
            white-space: pre-wrap !important;
            word-wrap: break-word !important;
        }

        /* Área de assinaturas - não aplicar pre-wrap */
        .assinatura-area {
            white-space: normal !important;
        }

        /* Garantir que os spans dentro do editor também preservem espaçamento */
        .ql-editor .preservar-espacamento,
        #editor .preservar-espacamento {
            white-space: pre-wrap !important;
        }

        /* Remover bordas e elementos indesejados do editor */
        #editor {
            border: none !important;
            padding: 0 !important;
        }

        .ql-container.ql-snow {
            border: none !important;
        }

        .ql-toolbar.ql-snow {
            border: none !important;
            border-bottom: 1px solid #ccc !important;
        }
    </style>
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-medical"></i>
                Editor do TERMO TRAUMATOLÓGICO IML
            </h1>
        </div>

        <!-- CABEÇALHO DO DOCUMENTO -->
        <div class="document-container">
            <div class="document-header">
                <div class="header-content">
                    <img src="{{ asset('images/b_PE.jpg') }}" alt="Brasão de Pernambuco">
                    <div class="header-text">
                        <div class="orgao-principal">POLÍCIA CIVIL DE PERNAMBUCO - PCPE</div>
                        <div class="orgao-secundario">Diretoria Integrada do Interior - 2 da Policia Civil – DINTER - 2</div>
                        <div class="orgao-secundario">Gerência de Controle Operacional do Interior - 2 – GCOI - 2</div>
                        <div class="orgao-secundario">20ª Delegacia Seccional de Polícia – Afogados da Ingazeira – 20ª DESEC</div>
                        <div class="delegacia-info">
                            {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADO' }} –
                            {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}
                        </div>
                    </div>
                    <img src="{{ asset('images/b_PCPE.png') }}" alt="Brasão da Polícia Civil">
                </div>
            </div>
        </div>

        <!-- TOOLBAR DE FERRAMENTAS -->
        <div class="toolbar-container">
            <div class="toolbar-main">
                <div class="toolbar-left" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div id="toolbar">
                        <span class="ql-formats">
                            <button class="ql-bold" title="Negrito (Ctrl+B)"></button>
                            <button class="ql-italic" title="Itálico (Ctrl+I)"></button>
                            <button class="ql-underline" title="Sublinhado (Ctrl+U)"></button>
                            <button class="ql-strike" title="Tachado"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-color" title="Cor do texto"></select>
                            <select class="ql-background" title="Cor de fundo"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered" title="Lista ordenada"></button>
                            <button class="ql-list" value="bullet" title="Lista com marcadores"></button>
                            <button class="ql-indent" value="-1" title="Diminuir recuo"></button>
                            <button class="ql-indent" value="+1" title="Aumentar recuo"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-align" title="Alinhamento"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-link" title="Inserir link"></button>
                            <button class="ql-image" title="Inserir imagem"></button>
                        </span>
                        <!-- BOTÕES PERSONALIZADOS -->
                        <span class="ql-formats">
                            <button class="ql-page-break" title="Quebra de Página (Ctrl+Enter)">
                                <i class="fas fa-file-alt"></i>
                            </button>
                            <button class="ql-text-case" title="Alternar Maiúsculas/Minúsculas (Shift+F3)">
                                <i class="fas fa-text-height"></i>
                            </button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean" title="Limpar formatação"></button>
                        </span>
                    </div>
                </div>

                <div class="toolbar-right">
                    <button class="btn-custom">
                        <i class="fas fa-file-pdf"></i>
                        Gerar PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- ÁREA DO EDITOR -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">
                <!-- CONTEÚDO DO TERMO TRAUMATOLÓGICO IML -->
                <p align="right" style="text-align: right;">
                    <span face="Arial, sans-serif">{{ $dadosArray['cidade'] ?? 'NÃO INFORMADO' }}</span>
                    <span face="Arial, sans-serif">,&nbsp;</span>
                    <span style="text-align: left;">
                        <span face="Arial, sans-serif">
                            @if(!empty($dadosArray['data_comp']))
                                {{ $dadosArray['data_comp'] }}
                            @elseif(!empty($dadosArray['data']))
                                {{ $dadosArray['data'] }}
                            @else
                                NÃO INFORMADO
                            @endif
                        </span>
                    </span>
                </p>

                <p align="right" style="text-align: right;">
                    <span style="text-align: left;"><span face="Arial, sans-serif"><br /></span></span>
                </p>

                <p>
                    <strong><span face="Arial, sans-serif">Ofício nº&nbsp;</span></strong>
                    <b><span face="Arial, sans-serif">{{ $numeroOficio ?? 'NÃO GERADO' }}</span></b><br/>
                    <span face="Arial, sans-serif">BOE: <b>{{ $dadosArray['boe'] ?? 'NÃO INFORMADO' }}</b></span>
                </p>

                <p><span face="Arial, sans-serif">&nbsp;</span></p>

                <p>
                    <strong><span face="Arial, sans-serif">ILMO. SR.&nbsp;GESTOR<br />INSTITUTO DE MEDICINA LEGAL - IML - SDS/PE<br /></span></strong>
                    <b><span face="Arial, sans-serif">PERÍCIAS MÉDICO-LEGAIS - URPOCSP-PMLSP</span></b>
                    <span face="Arial, sans-serif"></span>
                </p>

                <p><span face="Arial, sans-serif">&nbsp;</span></p>

                <p><span face="Arial, sans-serif">Senhor(a) Gestor,</span></p>

                <p><span face="Arial, sans-serif">&nbsp;</span></p>

                <p style="line-height: 18pt; text-align: justify; text-indent: 56.15pt;">
                    <span face="Arial, sans-serif">Pelo presente solicito providências de V.Sª., no sentido de submeter a PERÍCIA TRAUMATOLÓGICA a pessoa de </span>
                    <b><span face="Arial, sans-serif">{{ $dadosArray['nome'] ?? 'NÃO INFORMADO' }}</span></b>
                    <span face="Arial, sans-serif">, </span>
                    <b><span face="Arial, sans-serif">NASCIMENTO:</span></b>
                    <span face="Arial, sans-serif"> {{ $dadosArray['nascimento'] ?? 'NÃO INFORMADO' }}, </span>
                    <b><span face="Arial, sans-serif">IDADE:</span></b>
                    <span face="Arial, sans-serif"> {{ $dadosArray['idade'] ?? 'NÃO INFORMADO' }} ANOS, </span>
                    <b><span face="Arial, sans-serif">RG:</span></b>
                    <span face="Arial, sans-serif"> {{ $dadosArray['rg'] ?? 'NÃO INFORMADO' }}, </span>
                    <b><span face="Arial, sans-serif">CPF:</span></b>
                    <span face="Arial, sans-serif"> {{ $dadosArray['cpf'] ?? 'NÃO INFORMADO' }},</span>
                    <b><span face="Arial, sans-serif">MÃE:</span></b>
                    <span face="Arial, sans-serif"> {{ $dadosArray['mae'] ?? 'NÃO INFORMADO' }}, </span>
                    <b><span face="Arial, sans-serif">PAI:</span></b>
                    <span face="Arial, sans-serif"> {{ $dadosArray['pai'] ?? 'NÃO INFORMADO' }}, </span>
                    <b><span face="Arial, sans-serif">END. RESIDENCIAL:</span></b>
                    <span face="Arial, sans-serif"> {{ $dadosArray['endereco'] ?? 'NÃO INFORMADO' }}.</span>
                </p>

                <p style="line-height: 18pt; text-align: justify; text-indent: 56.15pt;">
                    <span face="Arial, sans-serif">&nbsp;</span>
                </p>

                <p style="line-height: 18pt; text-align: justify; text-indent: 56.15pt;">
                    <span face="Arial, sans-serif">Encaminhe-se o laudo à {{ $dadosArray['delegacia'] ?? 'NÃO INFORMADO' }}.</span>
                </p>

                <p><span face="Arial, sans-serif">&nbsp;</span></p>

                <p align="center" style="text-align: center;">
                    <span face="Arial, sans-serif">Atenciosamente,</span>
                </p>

                <p align="center" style="text-align: center;">
                    <span face="Arial, sans-serif">&nbsp;</span>
                </p>

                <p style="font-family: Arial, sans-serif; line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">
                    <b><span style="font-size: 12pt;">{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}<br /></span></b>
                    <span style="font-size: 12pt;">Delegado(a) de Polícia</span>
                </p>
            </div>

            <div class="editor-stats">
                <div class="stat-item">
                    <i class="fas fa-keyboard"></i>
                    <span id="char-count">0 caracteres</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-paragraph"></i>
                    <span id="paragraph-count">0 parágrafos</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock"></i>
                    <span>Última alteração: <span id="last-modified">Agora</span></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = {
            delegacia: "{{ $dadosArray['delegacia'] ?? 'NÃO INFORMADO' }}",
            cidade: "{{ $dadosArray['cidade'] ?? 'NÃO INFORMADO' }}",
            delegado: "{{ $dadosArray['delegado'] ?? '' }}",
            escrivao: "{{ $dadosArray['escrivao'] ?? '' }}",
            nome: "{{ $dadosArray['nome'] ?? '' }}",
            alcunha: "{{ $dadosArray['alcunha'] ?? '' }}",
            nascimento: "{{ $dadosArray['nascimento'] ?? '' }}",
            idade: "{{ $dadosArray['idade'] ?? '' }}",
            estcivil: "{{ $dadosArray['estcivil'] ?? '' }}",
            naturalidade: "{{ $dadosArray['naturalidade'] ?? '' }}",
            rg: "{{ $dadosArray['rg'] ?? '' }}",
            cpf: "{{ $dadosArray['cpf'] ?? '' }}",
            profissao: "{{ $dadosArray['profissao'] ?? '' }}",
            instrucao: "{{ $dadosArray['instrucao'] ?? '' }}",
            telefone: "{{ $dadosArray['telefone'] ?? '' }}",
            mae: "{{ $dadosArray['mae'] ?? '' }}",
            pai: "{{ $dadosArray['pai'] ?? '' }}",
            endereco: "{{ $dadosArray['endereco'] ?? '' }}",
            boe: "{{ $dadosArray['boe'] ?? '' }}",
            data: "{{ $dadosArray['data'] ?? '' }}",
            data_comp: "{{ $dadosArray['data_comp'] ?? '' }}"
        };
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pericias/Traumatologico_IML.js') }}"></script>
</body>
</html>
