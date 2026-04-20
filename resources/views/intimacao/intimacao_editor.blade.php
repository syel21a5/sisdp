<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Intimação - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
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

        <!-- ÁREA DO EDITOR -->
        <div class="editor-area">
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
                <p style="text-align: left; margin: 8px 0 !important;">&nbsp;</p>

                <p style="text-align: center; font-weight: bold; margin: 15px 0 5px 0 !important;"><strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : '[DELEGADO]' }}</strong></p>
                <p style="text-align: center; margin: 2px 0 15px 0 !important;">DELEGADO (A) DE POLÍCIA</p>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>
                <p style="text-align: left; margin: 8px 0 10px 0 !important;">Recebedor(a):_____________________________________________________</p>


                <!-- SEGUNDO CABEÇALHO SERÁ INSERIDO AUTOMATICAMENTE AQUI PELO CONTROLLER -->

                <!-- SEGUNDA VIA -->
                <div style="margin-top: 8px;">
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
                    <p style="text-align: left; margin: 8px 0 !important;">&nbsp;</p>

                    <p style="text-align: center; font-weight: bold; margin: 15px 0 5px 0 !important;"><strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : '[DELEGADO]' }}</strong></p>
                    <p style="text-align: center; margin: 2px 0 15px 0 !important;">DELEGADO (A) DE POLÍCIA</p>

                    <p style="text-align: left; margin: 4px 0 !important;">&nbsp;</p>

                    <p style="text-align: left; margin: 8px 0 5px 0 !important;">Recebedor(a):_____________________________________________________</p>
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

    <script>
        window.dadosParaImpressao = {
            delegacia: @json(isset($dadosArray['delegacia']) ? strtoupper($dadosArray['delegacia']) : 'NÃO INFORMADO'),
            cidade: @json(isset($dadosArray['cidade']) ? strtoupper($dadosArray['cidade']) : 'NÃO INFORMADO'),
            delegado: @json(isset($dadosArray['delegado']) ? strtoupper($dadosArray['delegado']) : ''),
            escrivao: @json(isset($dadosArray['escrivao']) ? strtoupper($dadosArray['escrivao']) : ''),
            nome: @json(isset($dadosArray['Nome']) ? strtoupper($dadosArray['Nome']) : ''),
            endereco: @json(isset($dadosArray['Endereco']) ? strtoupper($dadosArray['Endereco']) : ''),
            telefone: @json(isset($dadosArray['Telefone']) ? $dadosArray['Telefone'] : ''),
            hora: @json(isset($dadosArray['hora']) ? $dadosArray['hora'] : ''),
            dataoitiva: @json(isset($dadosArray['dataoitiva']) ? $dadosArray['dataoitiva'] : ''),
            boe: @json(isset($dadosArray['BOE']) ? $dadosArray['BOE'] : ''),
            data: @json(isset($dadosArray['data']) ? $dadosArray['data'] : ''),
            data_comp: @json(isset($dadosArray['data_comp']) ? $dadosArray['data_comp'] : '')
        };
    </script>

    <!-- ✅ DEPENDÊNCIAS DO SISTEMA -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="{{ asset('js/DocumentoService.js') }}"></script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/intimacao/Intimacao_editor.js') }}"></script>
</body>
</html>
