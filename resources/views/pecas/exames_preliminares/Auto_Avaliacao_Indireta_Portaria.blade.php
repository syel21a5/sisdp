<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Avaliação - Editor Profissional</title>
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
                Editor da PORTARIA do TERMO DE AVALIAÇÃO INDIRETA
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
    <!-- PRIMEIRA PÁGINA - PORTARIA E TERMO DE COMPROMISSO -->
    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">
        <strong style="font-size: 20pt;">P O R T A R I A</strong>
    </p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
        <span style="font-family: Arial, sans-serif; font-size: 12pt;">
            O(A) Bel(a). <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>,
            Delegado(a) de Polícia, usando de suas atribuições legais, e, havendo necessidade de proceder a
            <b>AVALIAÇÃO INDIRETA DE OBJETOS</b>, nomeia como Peritos: <strong>{{ !empty($dadosArray['policial_1']) ? $dadosArray['policial_1'] : 'NÃO INFORMADO' }}</strong> e
            <strong>{{ !empty($dadosArray['policial_2']) ? $dadosArray['policial_2'] : 'NÃO INFORMADO' }}</strong>,
            ambos policiais civis, os quais deverão prestar o compromisso legal de estilo.
        </span>
    </p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">
        <b>C u m p r a – s e</b>
    </p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">
        {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : 'NÃO INFORMADO' }}.
    </p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">&nbsp;</p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">
        <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>
    </p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">
        Delegado(a) de Polícia
    </p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

    <!-- TERMO DE COMPROMISSO -->
    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: center;">
        <b><u>TERMO DE COMPROMISSO</u></b>
    </p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
        <span style="font-family: Arial, sans-serif; font-size: 12pt;">
            Ao(s) <strong>{{ !empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO' }}</strong>, nesta cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }},
            e no Cartório desta Delegacia de Polícia, onde presente se encontrava o(a) Bel(a).
            <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>, respectivo Delegado(a), comigo escrivão no final
            assinado, aí compareceram os PERITOS nomeados <strong>{{ !empty($dadosArray['policial_1']) ? $dadosArray['policial_1'] : 'NÃO INFORMADO' }}</strong> e
            <strong>{{ !empty($dadosArray['policial_2']) ? $dadosArray['policial_2'] : 'NÃO INFORMADO' }}</strong>,
            <b>a quem a Autoridade deferiu o compromisso legal de bem e fielmente desempenharem o encargo,
            descrevendo com verdade, sem dolo ou malícia,</b> o que encontrarem e os encarregou de
            procederem a <b>AVALIAÇÃO INDIRETA DE OBJETOS</b> do procedimento policial em questão.
            E como aceitassem o encargo, mandou a Autoridade encerrar o presente Auto que assina com os Peritos e comigo
            Escrivão que digitei.
        </span>
    </p>

    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

<!-- Área de assinaturas -->
                <div class="assinatura-area">
                    <p style="border-top: 1px solid rgb(0, 0, 0); line-height: 1.4; margin: 0.1em 0px; padding-top: 40px; padding: 40px 0px 0px;">
                        <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>
                    </p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">AUTORIDADE POLICIAL:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">PERITO:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">PERITO:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">ESCRIVÃO(Ã):</p>
                    <p style="border-top: 1px solid rgb(0, 0, 0); line-height: 1.4; margin: 0.1em 0px; padding-top: 40px; padding: 40px 0px 0px;">
                        <strong>{{ !empty($dadosArray['escrivao']) ? $dadosArray['escrivao'] : 'NÃO INFORMADO' }}</strong>
                    </p>
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
    <script>
        window.dadosParaImpressao = {
            delegacia: @json(isset($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADO'),
            cidade: @json(isset($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO'),
            delegado: @json(isset($dadosArray['delegado']) ? $dadosArray['delegado'] : ''),
            escrivao: @json(isset($dadosArray['escrivao']) ? $dadosArray['escrivao'] : ''),
            policial_1: @json(isset($dadosArray['policial_1']) ? $dadosArray['policial_1'] : 'NÃO INFORMADO'),
            policial_2: @json(isset($dadosArray['policial_2']) ? $dadosArray['policial_2'] : 'NÃO INFORMADO'),
            data_comp: @json(isset($dadosArray['data_comp']) ? $dadosArray['data_comp'] : 'NÃO INFORMADO'),
            nome: @json(isset($dadosArray['nome']) ? $dadosArray['nome'] : ''),
            boe: @json(isset($dadosArray['boe']) ? $dadosArray['boe'] : ''),
            apreensao: @json(isset($dadosArray['apreensao']) ? $dadosArray['apreensao'] : ''),
            data_ext: @json(isset($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO')
        };
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pecas/Avaliacao.js') }}"></script>
</body>
</html>
