<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laudo Traumatológico (Hospitalar) - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    <style>
        .table-quesitos {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 9.5pt;
            margin-bottom: 5px;
        }
        .table-quesitos td {
            border: 1px solid black;
            padding: 2px 4px;
        }
        .shaded {
            background-color: #d0d0d0 !important;
            width: 25px;
            text-align: center;
        }
        .center-text {
            text-align: center;
        }
        /* Ajustes para compactação */
        #editor p {
            margin-bottom: 8px !important;
            line-height: 1.3 !important;
        }
        .signature-table td {
            padding-top: 5px !important;
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

                <p style="text-align: center; font-size: 14pt; margin-bottom: 10px;"><strong>P O R T A R I A</strong></p>

                <p style="text-align: justify; text-indent: 50px; font-size: 12pt;">
                    Tendo de se proceder ao <strong>EXAME TRAUMATOLÓGICO</strong> em <strong>{{ $dadosArray['nome'] ?? '[NOME]' }}</strong>, usando de minhas atribuições legais, nomeio peritos os Srs._______________________________ e _______________________________, os quais deverão comparecer ao local solicitado, a fim de prestar compromisso legal. <strong>CUMPRA-SE.</strong>
                </p>

                <div style="margin-top: 20px; margin-bottom: 20px; text-align: center;">
                    <p>
                        <strong>{{ $dadosArray['delegado'] ?? '[DELEGADO]' }}</strong><br />
                        Delegado(a) de Polícia
                    </p>
                </div>

                <p style="text-align: center; font-size: 14pt; margin-top: 15px; margin-bottom: 10px;"><strong>TERMO DE COMPROMISSO</strong></p>

                <p style="text-align: justify; text-indent: 50px; font-size: 12pt;">
                    Ao(s) <strong>{{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : ($dadosArray['data'] ?? '[DATA]') }}</strong>, nesta Delegacia de Polícia, presente se achava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? '[DELEGADO]' }}</strong>, respectivo(a) Delegado(a) de Polícia, comigo escrivão, servindo ao seu cargo e ao final assinado, aí compareceram os peritos nomeados pela portaria supra, senhores:__________________________ e _______________________ aos quais a autoridade policial deferiu o público e solene compromisso que aceitaram de bem e fielmente, sem dolo e nem malícia, procederam ao <strong>EXAME TRAUMATOLÓGICO</strong> na pessoa de <strong>{{ $dadosArray['nome'] ?? '[NOME]' }}</strong>, para constar, mandou a autoridade lavrar este termo que assina com os peritos e comigo escrivão que o digitei.
                </p>

                <table style="width: 100%; margin-top: 20px; border-collapse: collapse;">
                    <tr>
                        <td style="width: 50%; text-align: center; vertical-align: top;">
                            <p>
                                <strong>{{ $dadosArray['delegado'] ?? '[DELEGADO]' }}</strong><br />
                                Autoridade Policial
                            </p>
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: top;">
                            <p>
                                <strong>{{ $dadosArray['escrivao'] ?? '[ESCRIVÃO]' }}</strong><br />
                                Escrivão de Polícia
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="height: 40px;"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            _______________________________________________<br />
                            Perito
                        </td>
                        <td style="text-align: center;">
                            _______________________________________________<br />
                            Perito
                        </td>
                    </tr>
                </table>

                <div style="page-break-after: always;"></div>

                <!-- SEGUNDA PÁGINA: AUTO DE EXAME -->
                <div style="font-family: Arial, sans-serif !important; font-size: 11pt;">
                    
                    <p style="text-align: center; font-size: 14pt; margin-top: 0; margin-bottom: 12px;">
                        <strong>AUTO DE EXAME TRAUMATOLÓGICO</strong>
                    </p>

                    <p style="text-align: justify; text-indent: 50px; line-height: 1.3; margin-bottom: 10px;">
                        Ao(s) <strong>{{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : ($dadosArray['data'] ?? '[DATA]') }}</strong>, nesta cidade de <strong>{{ $dadosArray['cidade'] ?? '[CIDADE]' }}</strong>, estado de Pernambuco, no cartório desta delegacia, onde presente se encontrava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? '[DELEGADO]' }}</strong>, respectivo(a) Delegado(a) de Polícia, comigo, <strong>{{ $dadosArray['escrivao'] ?? '[ESCRIVÃO]' }}</strong>, escrivão de seu cargo, ao final assinado, ai presentes os peritos _______________________________ e ______________________________, designados por portaria, os quais sob juramento de seus cargos, procederam ao <strong>EXAME TRAUMATOLÓGICO</strong> na pessoa de <strong>{{ $dadosArray['nome'] ?? '[PERICIADO]' }}</strong>, verificando o que a seguir descrevem:
                    </p>

                    <p style="margin-bottom: 3px;"><strong>VISUM ET REPERTUM:</strong></p>
                    <p style="border-bottom: 1px solid black; margin-top: 0; margin-bottom: 10px; min-height: 18px;"></p>

                    <p style="margin-bottom: 4px; font-size: 10.5pt;">e em seguida responderam aos quesitos seguintes:</p>
                    <p style="margin-bottom: 8px;">
                        <strong>1º - HOUVE LESÃO À INTEGRIDADE CORPORAL OU A SAÚDE DO EXAMINADO?</strong> – SIM: [ &nbsp; ] / NÃO: [ &nbsp; ]<br>
                        <strong>ESPECIFICAR:</strong> ____________________________________________________________________________
                    </p>

                    <p style="margin-bottom: 12px;">
                        <strong>2º - QUAL O INSTRUMENTO OU MEIO QUE OCASIONOU?</strong><br>
                        <strong>ESPECIFICAR:</strong> ____________________________________________________________________________
                    </p>

                    <!-- QUESITO 3 -->
                    <table class="table-quesitos">
                        <tr>
                            <td colspan="5" style="background-color: #f0f0f0;"><strong>3º - DA LESÃO RESULTOU:</strong></td>
                        </tr>
                        <tr>
                            <td style="width: 70%;">DEBILIDADE PERMANENTE DE MEMBRO, SENTIDO OU FUNÇÃO</td>
                            <td style="text-align: center; width: 8%;">SIM</td>
                            <td style="width: 4%;"></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center; width: 12%;">NÃO</td>
                        </tr>
                        <tr>
                            <td>PERIGO DE VIDA</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td>ACELERAÇÃO DE PARTO</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td>INCAPACIDADE PARA AS OCUPAÇÕES HABITUAIS POR MAIS DE 30 DIAS</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td>AS ALTERAÇÕES ORGÂNICAS, FISIOLÓGICAS E/OU METABÓLICAS OBSERVADAS EM DECORRÊNCIA DA LESÃO SÃO CAPAZES DE CAUSAR LETALIDADE?</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td colspan="5" style="border-top: none;"><strong>EM CASO POSITIVO, ESPECIFICAR:</strong></td>
                        </tr>
                    </table>

                    <!-- QUESITO 4 -->
                    <table class="table-quesitos">
                        <tr>
                            <td colspan="5" style="background-color: #f0f0f0;"><strong>4º - DA LESÃO RESULTOU:</strong></td>
                        </tr>
                        <tr>
                            <td style="width: 70%;">DEFORMIDADE PERMANENTE</td>
                            <td style="text-align: center; width: 8%;">SIM</td>
                            <td style="width: 4%;"></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center; width: 12%;">NÃO</td>
                        </tr>
                        <tr>
                            <td>PERDA OU INUTILIZAÇÃO DE MEMBRO, SENTIDO OU FUNÇÃO</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td>ENFERMIDADE INCURÁVEL</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td>INCAPACIDADE PERMANENTE PARA O TRABALHO</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td>ABORTO</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td>EXISTE A NECESSIDADE DE INTERVENÇÃO CIRÚRGICA?</td>
                            <td style="text-align: center;">SIM</td>
                            <td></td>
                            <td class="shaded">|</td>
                            <td style="text-align: center;">NÃO</td>
                        </tr>
                        <tr>
                            <td colspan="5" style="border-top: none;"><strong>EM CASO POSITIVO, ESPECIFICAR:</strong></td>
                        </tr>
                    </table>

                    <p style="text-align: justify; line-height: 1.3; margin-bottom: 20px; font-size: 11pt;">
                        Nada mais havendo a constar, mandou a autoridade que fosse lavrado o presente auto, que, depois de lido e achado conforme, o assina juntamente com os peritos, e comigo, escrivão, que o digitei.
                    </p>

                    <!-- ASSINATURAS -->
                    <table style="width: 100%; border-collapse: collapse; font-size: 11pt;" class="signature-table">
                        <tr>
                            <td style="width: 50%; text-align: center; vertical-align: top;">
                                <strong>[{{ mb_strtoupper($dadosArray['delegado'] ?? '[DELEGADO]', 'UTF-8') }}]</strong><br>
                                <strong>AUTORIDADE POLICIAL</strong>
                            </td>
                            <td style="width: 50%; text-align: center; vertical-align: top;">
                                <strong>[{{ mb_strtoupper($dadosArray['escrivao'] ?? '[ESCRIVÃO]', 'UTF-8') }}]</strong><br>
                                <strong>ESCRIVÃO DE POLÍCIA</strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="height: 35px;"></td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; vertical-align: top;">
                                _______________________________________________<br>
                                <strong>1º Perito(a)</strong>
                            </td>
                            <td style="width: 50%; text-align: center; vertical-align: top;">
                                _______________________________________________<br>
                                <strong>2º Perito(a)</strong>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pericias/Traumatologico_Hospital.js') }}"></script>
</body>
</html>
