<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EFICIÊNCIA DE ARMA - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 20px;">
            <ul style="margin-bottom: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor: EXAME DE EFICIÊNCIA DE ARMA DE FOGO (COM DISPARO)
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
                <!-- PORTARIA -->
                <p style="text-align: center; line-height: 1.6; margin: 20px 0 20px 0; padding: 0;">
                    <strong style="font-size: 16pt;">P O R T A R I A</strong>
                </p>

                <p style="text-align: justify;">
                    O(A) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, usando de suas atribuições legais, e, havendo necessidade de proceder a EXAME DE EFICIÊNCIA DE ARMA DE FOGO, nomeia como Peritos: <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, ambos policiais civis, os quais deverão prestar o compromisso legal de estilo.
                </p>

                <p style="text-align: center;">C u m p r a – s e</p>

                <p style="text-align: center;">
                    {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, <strong>{{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'DATA') }}</strong>.
                </p>
                <p>&nbsp;</p>
                <div class="assinatura-area" style="margin-top: 10px; line-height: 1.3;">
                    <p style="text-align: center;">
                        <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                        Delegado(a) de Polícia
                    </p>
                </div>

                <!-- TERMO DE COMPROMISSO (NA MESMA PÁGINA DA PORTARIA) -->
                <p style="text-align: center; line-height: 1.6; margin: 25px 0 10px 0; padding: 0;">
                    <strong style="font-size: 14pt;">TERMO DE COMPROMISSO</strong>
                </p>

                <p style="text-align: justify;">
                    Ao(s) <strong>{{ $dadosArray['data_ext'] ?? 'DATA POR EXTENSO' }}</strong>, nesta cidade de {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, e no Cartório desta Delegacia de Polícia, onde presente se encontrava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, respectivo Delegado(a), comigo escrivão no final assinado, aí compareceram os PERITOS nomeados <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, a quem a Autoridade deferiu o compromisso legal de bem e fielmente desempenharem o encargo, descrevendo com verdade, sem dolo ou malícia, o que encontrarem e os encarregou de procederem ao EXAME DE EFICIÊNCIA DE ARMA DE FOGO. E como aceitassem o encargo, mandou a Autoridade encerrar o presente Auto que assina com os Peritos e comigo Escrivão que digitei.
                </p>
                <p>&nbsp;</p>
                <div class="assinatura-area" style="margin-top: 10px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 35px;">
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                AUTORIDADE POLICIAL
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 35px;">
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                ESCRIVÃO(Ã)
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; border: none;">
                                <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                            <td style="width: 50%; text-align: center; border: none;">
                                <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- QUEBRA DE PÁGINA PARA O AUTO (PÁGINA 2) -->
                <p class="page-break-marker" style="page-break-before: always; break-before: page; height: 0; margin: 0; padding: 0; visibility: hidden;">&nbsp;</p>

                <!-- AUTO DE EXAME DE EFICIÊNCIA DE ARMA DE FOGO -->
                <p style="text-align: center; line-height: 1.6; margin: 20px 0 20px 0; padding: 0;">
                    <strong style="font-size: 16pt;">AUTO DE EXAME DE EFICIÊNCIA DE ARMA DE FOGO</strong>
                </p>

                <p style="text-align: justify;">
                    Ao(s) <strong>{{ $dadosArray['data_ext'] ?? 'DATA POR EXTENSO' }}</strong>, nesta cidade de {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, e no Cartório da Delegacia de Polícia, onde presente achava-se o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, comigo escrivão ao final assinado, tendo a autoridade policial nomeado como Peritos as pessoas de <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, ambos policiais civis, deferindo-lhes o compromisso legal, de bem e fielmente, sem dolo nem malícia, desempenharem a missão, determinado os exames necessários a fim de que procedam ao EXAME DE EFICIÊNCIA DE ARMA DE FOGO, apreendida nos autos, conforme boletim de ocorrência nº <strong>{{ $dadosArray['boe'] ?? 'NÃO INFORMADO' }}</strong>, e responderem aos seguintes quesitos:
                </p>
                <div style="height: 12px;"></div>
                <p style="text-align: justify; margin-left: 20px;">
                    1º) Qual a espécie da arma submetida a exame? <br>
                    2º) Quais as características? <br>
                    3º) No estado em que se encontram, poderia ter sido utilizada para a prática de crime? <br>
                    4º) Apresenta alguma mancha? <br>
                    5º) Qual a natureza da mancha? 
                </p>
                <div style="height: 12px;"></div>
                <p style="text-align: justify;">
                    Em seguida, passaram os peritos a fazer os exames necessários, verificando os mecanismos de funcionamento da arma de fogo, <strong>tendo sido efetuado um disparo com uma das munições apreendidas</strong>. Concluídas as pesquisas realizadas, responderam aos quesitos formulados da seguinte forma:
                </p>
                <p style="text-align: justify; margin-left: 20px;">
                    1º) <strong>[TIPO DE ARMA]</strong>;<br>
                    2º) <strong>[DADOS DA ARMA]</strong>;<br>
                    3º) <strong>Sim</strong>;<br>
                    4º) <strong>Não</strong>;<br>
                    5º) <strong>Não</strong>;
                </p>
                <div style="height: 12px;"></div>
                <p style="text-align: justify;">
                    Nada mais havendo a acrescentar, mandou a Autoridade, encerrar o presente auto, o qual depois de lido e achado conforme, segue devidamente assinado, pela Autoridade, pelos peritos e por mim Escrivão, que o digitei.
                </p>
                <p>&nbsp;</p>
                <div class="assinatura-area" style="margin-top: 10px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 35px;">
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                AUTORIDADE POLICIAL
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 35px;">
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                ESCRIVÃO(Ã)
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; border: none;">
                                <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                            <td style="width: 50%; text-align: center; border: none;">
                                <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
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
    <script src="{{ asset('js/DocumentoService.js') }}?v={{ time() }}"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pericias/ExameEficienciaArmaComDisparo.js') }}?v={{ time() }}"></script>
</body>
</html>
