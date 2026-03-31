<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Intimação - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-intimacao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-gavel"></i>
                Editor de INTIMAÇÃO
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
        <div id="editor" class="preservar-espacamento">
        <!-- PRIMEIRA VIA -->
        <p style="text-align: center; font-weight: bold; margin-bottom: 5px !important;">MANDADO DE INTIMAÇÃO</p>

        <!-- ESPAÇAMENTO ENTRE MANDADO E TEXTO -->
        <p style="text-align: left; margin: 15px 0 !important;">&nbsp;</p>

        <p style="text-align: justify;">Pela presente fica a pessoa de <strong>{{ !empty($dadosArray['Nome']) ? $dadosArray['Nome'] : '[NOME]' }}</strong>, residente à {{ !empty($dadosArray['Endereco']) ? $dadosArray['Endereco'] : '[ENDEREÇO]' }}, telefone: {{ !empty($dadosArray['Telefone']) ? $dadosArray['Telefone'] : '[FONE]' }}. INTIMADA a comparecer nesta Delegacia de Polícia, situada no Município de Afogados da Ingazeira-PE, às <strong>{{ !empty($dadosArray['hora']) ? $dadosArray['hora'] : '[HORA]' }}</strong> horas, do dia <strong>{{ !empty($dadosArray['dataoitiva']) ? $dadosArray['dataoitiva'] : '[DATA]' }}</strong>, a fim de INQUIRIDA, visando esclarecer procedimento policial instaurado nesta Delegacia de Polícia, conforme boletim de ocorrência nº <strong>{{ !empty($dadosArray['BOE']) ? $dadosArray['BOE'] : '[BOE]' }}</strong>.</p>

        <p class="ql-align-justify preservar-espacamento">
        <br>
        </p>

        <p style="text-align: justify; font-weight: bold; margin: 8px 0 5px 0 !important; font-size: 7pt !important;">Atenção: CRIME DE DESOBEDIÊNCIA.</p>
        <p style="text-align: justify; margin: 2px 0 !important; font-size: 7pt !important;">Código Penal – Art. 330 – Desobedecer</p>
        <p style="text-align: justify; margin: 2px 0 !important; font-size: 7pt !important;">Ordem legal de funcionário público.</p>
        <p style="text-align: justify; margin: 2px 0 10px 0 !important; font-size: 7pt !important;">Pena: Detenção de quinze dias a seis meses.</p>

        <p style="text-align: center; font-weight: bold; margin: 8px 0 !important;">C U M P R A - S E</p>

        <p style="text-align: justify;">DADO E LAVRADO nesta Cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : '[CIDADE]' }}, Estado de Pernambuco, no Cartório desta Delegacia de Polícia da {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : '[CIRC]' }}, ao(s) {{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : '[DATA-1]' }}. EU, {{ !empty($dadosArray['escrivao']) ? $dadosArray['escrivao'] : '_____________________' }}, Escrivão, o digitei.</p>

        <p style="text-align: center; margin: 8px 0 !important;">{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : '[CIDADE]' }}, {{ !empty($dadosArray['data']) ? $dadosArray['data'] : '[DATA]' }}</p>

        <!-- DOIS ESPAÇAMENTOS ENTRE [CIDADE], [DATA] E DELEGADO -->
        <p style="text-align: left; margin: 30px 0 !important;">&nbsp;</p>

        <p style="text-align: center; font-weight: bold; margin: 15px 0 5px 0 !important;"><strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : '[DELEGADO]' }}</strong></p>
        <p style="text-align: center; margin: 2px 0 15px 0 !important;">DELEGADO (A) DE POLÍCIA</p>

        <p class="ql-align-justify preservar-espacamento">
                <br>
        </p>
        <p style="text-align: left; margin: 20px 0 30px 0 !important;">Recebedor(a):_____________________________________________________</p>


        <!-- SEGUNDO CABEÇALHO SERÁ INSERIDO AUTOMATICAMENTE AQUI PELO CONTROLLER -->

        <!-- SEGUNDA VIA -->
        <div style="margin-top: 20px;">
            <p style="text-align: center; font-weight: bold; margin-bottom: 5px !important;">MANDADO DE INTIMAÇÃO</p>

            <!-- ESPAÇAMENTO ENTRE MANDADO E TEXTO -->
            <p style="text-align: left; margin: 15px 0 !important;">&nbsp;</p>

            <p style="text-align: justify;">Pela presente fica a pessoa de <strong>{{ !empty($dadosArray['Nome']) ? $dadosArray['Nome'] : '[NOME]' }}</strong>, residente à {{ !empty($dadosArray['Endereco']) ? $dadosArray['Endereco'] : '[ENDEREÇO]' }}, telefone: {{ !empty($dadosArray['Telefone']) ? $dadosArray['Telefone'] : '[FONE]' }}. INTIMADA a comparecer nesta Delegacia de Polícia, situada no Município de Afogados da Ingazeira-PE, às <strong>{{ !empty($dadosArray['hora']) ? $dadosArray['hora'] : '[HORA]' }}</strong> horas, do dia <strong>{{ !empty($dadosArray['dataoitiva']) ? $dadosArray['dataoitiva'] : '[DATA]' }}</strong>, a fim de INQUIRIDA, visando esclarecer procedimento policial instaurado nesta Delegacia de Polícia, conforme boletim de ocorrência nº <strong>{{ !empty($dadosArray['BOE']) ? $dadosArray['BOE'] : '[BOE]' }}</strong>.</p>

            <p class="ql-align-justify preservar-espacamento">
                <br>
            </p>
            <p style="text-align: justify; font-weight: bold; margin: 8px 0 5px 0 !important; font-size: 7pt !important;">Atenção: CRIME DE DESOBEDIÊNCIA.</p>
            <p style="text-align: justify; margin: 2px 0 !important; font-size: 7pt !important;">Código Penal – Art. 330 – Desobedecer</p>
            <p style="text-align: justify; margin: 2px 0 !important; font-size: 7pt !important;">Ordem legal de funcionário público.</p>
            <p style="text-align: justify; margin: 2px 0 10px 0 !important; font-size: 7pt !important;">Pena: Detenção de quinze dias a seis meses.</p>

            <p style="text-align: center; font-weight: bold; margin: 8px 0 !important;">C U M P R A - S E</p>

            <p style="text-align: justify;">DADO E LAVRADO nesta Cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : '[CIDADE]' }}, Estado de Pernambuco, no Cartório desta Delegacia de Polícia da {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : '[CIRC]' }}, ao(s) {{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : '[DATA-1]' }}. EU, {{ !empty($dadosArray['escrivao']) ? $dadosArray['escrivao'] : '_____________________' }}, Escrivão, o digitei.</p>

            <p style="text-align: center; margin: 8px 0 !important;">{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : '[CIDADE]' }}, {{ !empty($dadosArray['data']) ? $dadosArray['data'] : '[DATA]' }}</p>

            <!-- DOIS ESPAÇAMENTOS ENTRE [CIDADE], [DATA] E DELEGADO -->
            <p style="text-align: left; margin: 30px 0 !important;">&nbsp;</p>

            <p style="text-align: center; font-weight: bold; margin: 15px 0 5px 0 !important;"><strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : '[DELEGADO]' }}</strong></p>
            <p style="text-align: center; margin: 2px 0 15px 0 !important;">DELEGADO (A) DE POLÍCIA</p>

            <p style="text-align: left; margin: 15px 0 !important;">&nbsp;</p>

            <p style="text-align: left; margin: 20px 0 10px 0 !important;">Recebedor(a):_____________________________________________________</p>
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
            nome: @json(isset($dadosArray['nome']) ? $dadosArray['nome'] : ''),
            alcunha: @json(isset($dadosArray['alcunha']) ? $dadosArray['alcunha'] : ''),
            nascimento: @json(isset($dadosArray['nascimento']) ? $dadosArray['nascimento'] : ''),
            idade: @json(isset($dadosArray['idade']) ? $dadosArray['idade'] : ''),
            estcivil: @json(isset($dadosArray['estcivil']) ? $dadosArray['estcivil'] : ''),
            naturalidade: @json(isset($dadosArray['naturalidade']) ? $dadosArray['naturalidade'] : ''),
            rg: @json(isset($dadosArray['rg']) ? $dadosArray['rg'] : ''),
            cpf: @json(isset($dadosArray['cpf']) ? $dadosArray['cpf'] : ''),
            profissao: @json(isset($dadosArray['profissao']) ? $dadosArray['profissao'] : ''),
            instrucao: @json(isset($dadosArray['instrucao']) ? $dadosArray['instrucao'] : ''),
            telefone: @json(isset($dadosArray['telefone']) ? $dadosArray['telefone'] : ''),
            mae: @json(isset($dadosArray['mae']) ? $dadosArray['mae'] : ''),
            pai: @json(isset($dadosArray['pai']) ? $dadosArray['pai'] : ''),
            endereco: @json(isset($dadosArray['endereco']) ? $dadosArray['endereco'] : ''),
            boe: @json(isset($dadosArray['boe']) ? $dadosArray['boe'] : ''),
            data_ext: @json(isset($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO')
        };
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/intimacao/Intimacao_editor.js') }}"></script>
</body>
</html>
