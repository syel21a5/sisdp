<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laudo Traumatológico (Hospitalar) - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Removido Quill CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Times New Roman', Times, serif;
        }

        .table-quesitos {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            margin-bottom: 15px;
        }

        .table-quesitos td {
            border: 1px solid black;
            padding: 6px 8px;
            vertical-align: top;
        }

        .table-quesitos td:first-child {
            width: 65%;
        }

        .table-quesitos td:not(:first-child) {
            text-align: center;
            width: 12%;
        }

        /* Ajustes para compactação e estilo Word */
        #editor p {
            margin-bottom: 8px !important;
            line-height: 1.3 !important;
            font-family: 'Times New Roman', Times, serif !important;
            font-size: 12pt !important;
        }

        #editor div {
            font-family: 'Times New Roman', Times, serif !important;
        }

        .signature-table td {
            padding-top: 10px !important;
            font-family: 'Times New Roman', Times, serif;
        }

        .document-container {
            background: white;
            margin-bottom: 20px;
        }

        .document-header {
            padding: 20px;
            border-bottom: 2px solid #000;
        }

        .editor-area {
            background: white;
            padding: 20px;
        }

        .toolbar-container {
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 10px 20px;
            display: flex;
            justify-content: flex-end;
        }

    </style>
</head>

<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-medical"></i>
                Editor do LAUDO TRAUMATOLÓGICO (HOSPITALAR)
            </h1>
        </div>

        <!-- CABEÇALHO DO DOCUMENTO -->
        <div class="document-container">
            <div class="document-header">
                <div class="header-content">
                    <img src="{{ asset('images/b_PE.jpg') }}" alt="Brasão de Pernambuco">
                    <div class="header-text">
                        <div class="orgao-principal">POLÍCIA CIVIL DE PERNAMBUCO - PCPE</div>
                        <div class="orgao-secundario">Diretoria Integrada do Interior - 2 da Policia Civil – DINTER - 2
                        </div>
                        <div class="orgao-secundario">Gerência de Controle Operacional do Interior - 2 – GCOI - 2</div>
                        <div class="orgao-secundario">20ª Delegacia Seccional de Polícia – Afogados da Ingazeira – 20ª
                            DESEC</div>
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
            <div class="toolbar-right">
                <button class="btn-custom" onclick="printDocument()">
                    <i class="fas fa-file-pdf"></i>
                    Gerar PDF
                </button>
            </div>
        </div>

        <!-- ÁREA DO EDITOR -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">

                <!-- ==================== PORTARIA ==================== -->
                <div style="text-align: center; font-size: 14pt; font-weight: bold; margin: 0 0 20px 0;">
                    P O R T A R I A
                </div>

                <p style="text-align: justify; text-indent: 50px; margin-bottom: 10px;">
                    Tendo de se proceder ao <strong>EXAME TRAUMATOLÓGICO</strong> em
                    <strong>{{ $dadosArray['nome'] ?? '[NOME]' }}</strong>,
                    usando de minhas atribuições legais, nomeio peritos os Srs.
                    _______________________________ e _______________________________,
                    os quais deverão comparecer ao local solicitado, a fim de prestar compromisso legal.
                    <strong>CUMPRA-SE.</strong>
                </p>

                <p style="text-align: center; margin-top: 30px; margin-bottom: 5px;">
                    <strong>{{ $dadosArray['delegado'] ?? '[DELEGADO]' }}</strong><br>
                    Delegado(a) de Polícia
                </p>

                <!-- ==================== TERMO DE COMPROMISSO ==================== -->
                <div style="text-align: center; font-size: 14pt; font-weight: bold; margin: 40px 0 15px 0;">
                    TERMO DE COMPROMISSO
                </div>

                <p style="text-align: justify; text-indent: 50px;">
                    Ao(s) <strong>{{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : ($dadosArray['data'] ?? '[DATA]') }}</strong>,
                    nesta Delegacia de Polícia, presente se achava o(a) Bel(a).
                    <strong>{{ $dadosArray['delegado'] ?? '[DELEGADO]' }}</strong>, respectivo(a) Delegado(a) de Polícia,
                    comigo escrivão, servindo ao seu cargo e ao final assinado, aí compareceram os peritos nomeados pela portaria supra,
                    senhores: __________________________ e __________________________
                    aos quais a autoridade policial deferiu o público e solene compromisso que aceitaram de bem e fielmente,
                    sem dolo e nem malícia, procederam ao <strong>EXAME TRAUMATOLÓGICO</strong> na pessoa de
                    <strong>{{ $dadosArray['nome'] ?? '[NOME]' }}</strong>, para constar, mandou a autoridade lavrar este termo
                    que assina com os peritos e comigo escrivão que o digitei.
                </p>

                <table style="width: 100%; margin-top: 25px; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; text-align: center; vertical-align: top;">
                            <strong>{{ $dadosArray['delegado'] ?? '[DELEGADO]' }}</strong><br>
                            Autoridade Policial
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: top;">
                            <strong>{{ $dadosArray['escrivao'] ?? '[ESCRIVÃO]' }}</strong><br>
                            Escrivão de Polícia
                        </td>
                    </tr>
                    <tr><td colspan="2" style="height: 30px;"></td></tr>
                    <tr>
                        <td style="text-align: center;">
                            _______________________________________________<br>
                            Perito
                        </td>
                        <td style="text-align: center;">
                            _______________________________________________<br>
                            Perito
                        </td>
                    </tr>
                </table>

                <!-- QUEBRA DE PÁGINA -->
                <!-- pagebreak -->

                <!-- ==================== AUTO DE EXAME TRAUMATOLÓGICO ==================== -->
                <div style="font-family: 'Times New Roman', Times, serif; font-size: 12pt;">
                    <div style="text-align: center; font-size: 14pt; font-weight: bold; margin: 0 0 20px 0;">
                        AUTO DE EXAME TRAUMATOLÓGICO
                    </div>

                    <p style="text-align: justify; text-indent: 50px;">
                        Ao(s) <strong>{{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : ($dadosArray['data'] ?? '[DATA]') }}</strong>,
                        nesta cidade de <strong>{{ $dadosArray['cidade'] ?? '[CIDADE]' }}</strong>, estado de Pernambuco,
                        no cartório desta delegacia, onde presente se encontrava o(a) Bel(a).
                        <strong>{{ $dadosArray['delegado'] ?? '[DELEGADO]' }}</strong>, respectivo(a) Delegado(a) de Polícia,
                        comigo, <strong>{{ $dadosArray['escrivao'] ?? '[ESCRIVÃO]' }}</strong>, escrivão de seu cargo, ao final assinado,
                        ai presentes os peritos _______________________________ e ______________________________,
                        designados por portaria, os quais sob juramento de seus cargos, procederam ao
                        <strong>EXAME TRAUMATOLÓGICO</strong> na pessoa de
                        <strong>{{ $dadosArray['nome'] ?? '[PERICIADO]' }}</strong>, verificando o que a seguir descrevem:
                    </p>

                    <p><strong>VISUM ET REPERTUM:</strong></p>
                    <p style="border-bottom: 1px solid black; min-height: 25px; margin-bottom: 15px;"></p>

                    <p>e em seguida responderam aos quesitos seguintes:</p>

                    <p>
                        <strong>1º - HOUVE LESÃO À INTEGRIDADE CORPORAL OU A SAÚDE DO EXAMINADO?</strong> – SIM: [ &nbsp; ] / NÃO: [ &nbsp; ]<br>
                        <strong>ESPECIFICAR:</strong> ____________________________________________________________________________
                    </p>

                    <p>
                        <strong>2º - QUAL O INSTRUMENTO OU MEIO QUE OCASIONOU?</strong><br>
                        <strong>ESPECIFICAR:</strong> ____________________________________________________________________________
                    </p>

                    <!-- QUESITO 3 -->
                    <p><strong>3º - DA LESÃO RESULTOU:</strong></p>
                    <table class="table-quesitos">
                        <tr><td style="border: 1px solid black; padding: 6px;">DEBILIDADE PERMANENTE DE MEMBRO, SENTIDO OU FUNÇÃO</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">PERIGO DE VIDA</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">ACELERAÇÃO DE PARTO</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">INCAPACIDADE PARA AS OCUPAÇÕES HABITUAIS POR MAIS DE 30 DIAS</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">AS ALTERAÇÕES ORGÂNICAS, FISIOLÓGICAS E/OU METABÓLICAS OBSERVADAS EM DECORRÊNCIA DA LESÃO SÃO CAPAZES DE CAUSAR LETALIDADE?</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td colspan="4" style="border: 1px solid black; padding: 6px;"><strong>EM CASO POSITIVO, ESPECIFICAR:</strong> _________________________________________________________________</td></tr>
                    </table>

                    <!-- QUESITO 4 -->
                    <p><strong>4º - DA LESÃO RESULTOU:</strong></p>
                    <table class="table-quesitos">
                        <tr><td style="border: 1px solid black; padding: 6px;">DEFORMIDADE PERMANENTE</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">PERDA OU INUTILIZAÇÃO DE MEMBRO, SENTIDO OU FUNÇÃO</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">ENFERMIDADE INCURÁVEL</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">INCAPACIDADE PERMANENTE PARA O TRABALHO</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">ABORTO</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td style="border: 1px solid black; padding: 6px;">EXISTE A NECESSIDADE DE INTERVENÇÃO CIRÚRGICA?</td><td style="border: 1px solid black; text-align: center;">SIM</td><td style="border: 1px solid black; text-align: center;">↔</td><td style="border: 1px solid black; text-align: center;">NÃO</td></tr>
                        <tr><td colspan="4" style="border: 1px solid black; padding: 6px;"><strong>EM CASO POSITIVO, ESPECIFICAR:</strong> _________________________________________________________________</td></tr>
                    </table>

                    <p style="text-align: justify; margin-top: 20px;">
                        Nada mais havendo a constar, mandou a autoridade que fosse lavrado o presente auto, que, depois de
                        lido e achado conforme, o assina juntamente com os peritos, e comigo, escrivão, que o digitei.
                    </p>

                    <table style="width: 100%; margin-top: 30px;">
                        <tr>
                            <td style="width: 50%; text-align: center;">
                                <strong>{{ mb_strtoupper($dadosArray['delegado'] ?? '[DELEGADO]', 'UTF-8') }}</strong><br>
                                AUTORIDADE POLICIAL
                            </td>
                            <td style="width: 50%; text-align: center;">
                                <strong>{{ mb_strtoupper($dadosArray['escrivao'] ?? '[ESCRIVÃO]', 'UTF-8') }}</strong><br>
                                ESCRIVÃO DE POLÍCIA
                            </td>
                        </tr>
                        <tr><td colspan="2" style="height: 40px;"></td></tr>
                        <tr>
                            <td style="text-align: center;">
                                _______________________________________________<br>
                                1º Perito(a)
                            </td>
                            <td style="text-align: center;">
                                _______________________________________________<br>
                                2º Perito(a)
                            </td>
                        </tr>
                    </table>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pericias/Traumatologico_Hospital.js') }}?v=<?php echo time(); ?>"></script>
</body>

</html>