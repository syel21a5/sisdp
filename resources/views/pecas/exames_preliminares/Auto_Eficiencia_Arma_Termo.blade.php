<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Eficiência - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor do AUTO do TERMO DE EFICIÊNCIA DE ARMA DE FOGO
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
                <!-- AUTO DE EXAME -->
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">
                    <strong style="font-size: 20pt;">AUTO DE EXAME DE CONSTATAÇÃO PRELIMINAR</strong>
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Aos <strong>{{ !empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'DATA POR EXTENSO' }}</strong>, nesta cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'Afogados da Ingazeira' }}, Estado de Pernambuco, no Cartório da <strong>{{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADO' }}</strong>, onde presente se encontrava o(a) Bel(a). <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, comigo Escrivão de Polícia ao final assinado, compareceram os Peritos Criminais ad hoc: <strong>{{ !empty($dadosArray['policial_1']) ? $dadosArray['policial_1'] : 'NÃO INFORMADO' }}</strong> e <strong>{{ !empty($dadosArray['policial_2']) ? $dadosArray['policial_2'] : 'NÃO INFORMADO' }}</strong>, para procederem ao EXAME DE CONSTATAÇÃO PRELIMINAR, tendo a Autoridade formulado os seguintes quesitos:
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">1º. Que tipo de arma foi apresentada a exame?</p>
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">2º. Quais as suas características?</p>
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">3º. A arma apresentada a exame se encontra apta a realizar disparos?</p>
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">4º. A arma apresentada a exame apresenta vestígios de disparos recentes?</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    <strong>RESPOSTAS:</strong>
                </p>
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Em seguida, passaram os Peritos a fazerem as observações e exames, tendo constatado o seguinte:
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;"><strong>AO PRIMEIRO QUESITO:</strong></p>
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;"><span style="background-color: yellow;">[ TIPO DE ARMA ]</span></p>
                
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;"><strong>AO SEGUNDO QUESITO:</strong></p>
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;"><span style="background-color: yellow;">[ DADOS DA ARMA ]</span></p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;"><strong>AO TERCEIRO QUESITO:</strong></p>
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">SIM.</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;"><strong>AO QUARTO QUESITO:</strong></p>
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">PREJUDICADO.</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Nada mais havendo a lavrar, mandou a Autoridade encerrar o presente Auto, que lido e achado conforme, vai devidamente assinado.
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <!-- Área de assinaturas -->
                <div class="assinatura-area">
                    <p style="border-top: 1px solid rgb(0, 0, 0); line-height: 1.4; margin: 0.1em 0px; padding-top: 5px; margin-top: 40px; text-align: center;">
                        <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong><br>
                        Autoridade Policial
                    </p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    
                    <p style="border-top: 1px solid rgb(0, 0, 0); line-height: 1.4; margin: 0.1em 0px; padding-top: 5px; margin-top: 40px; text-align: center;">
                        <strong>{{ !empty($dadosArray['escrivao']) ? $dadosArray['escrivao'] : 'NÃO INFORMADO' }}</strong><br>
                        Escrivão de Polícia
                    </p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>

                    <p style="border-top: 1px solid rgb(0, 0, 0); line-height: 1.4; margin: 0.1em 0px; padding-top: 5px; margin-top: 40px; text-align: center;">
                        <strong>{{ !empty($dadosArray['policial_1']) ? $dadosArray['policial_1'] : 'NÃO INFORMADO' }}</strong><br>
                        Perito
                    </p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>

                    <p style="border-top: 1px solid rgb(0, 0, 0); line-height: 1.4; margin: 0.1em 0px; padding-top: 5px; margin-top: 40px; text-align: center;">
                        <strong>{{ !empty($dadosArray['policial_2']) ? $dadosArray['policial_2'] : 'NÃO INFORMADO' }}</strong><br>
                        Perito
                    </p>
                </div>
            </div>
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
    <input type="hidden" id="dados-impressao-json" value="{{ json_encode($dadosArray) }}">
    <script>
        try {
            window.dadosParaImpressao = JSON.parse(document.getElementById('dados-impressao-json').value);
        } catch (e) {
            console.error('Erro ao processar dados de impressão:', e);
            window.dadosParaImpressao = {};
        }
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pecas/EficienciaArma.js') }}"></script>
</body>
</html>