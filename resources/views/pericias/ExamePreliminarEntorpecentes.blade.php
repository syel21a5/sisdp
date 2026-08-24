<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Exame Preliminar de Entorpecentes - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor: EXAME PRELIMINAR DE ENTORPECENTES
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
                
                <p style="text-align: center; line-height: 1.6; margin: 20px 0 10px 0; padding: 0;">
                    <strong style="font-size: 16pt;">P O R T A R I A</strong>
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 5px;">
                    O(A) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, usando de suas atribuições legais, e, havendo necessidade de proceder a EXAME DE CONSTATAÇÃO PRELIMINAR na substância apreendida nos Autos, nomeia como Peritos: <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, ambos policiais civis, os quais deverão prestar o compromisso legal de estilo.
                </p>

                <p style="text-align: center; font-weight: bold; line-height: 1.5; margin: 15px 0 5px 0;">
                    C u m p r a – s e
                </p>

                <p style="text-align: right; line-height: 1.5; margin-bottom: 45px;">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'NÃO INFORMADO') }}.
                </p>

                <p style="text-align: center; line-height: 1.5; margin: 15px 0 45px 0;">
                    <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                    Delegado(a) de Polícia
                </p>

                <p style="text-align: center; line-height: 1.6; margin: 15px 0 10px 0; padding: 0;">
                    <strong style="font-size: 14pt;">TERMO DE COMPROMISSO</strong>
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 10px;">
                    Ao(s) {{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}, nesta cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, e no Cartório desta Delegacia de Polícia, onde presente se encontrava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, respectivo Delegado(a), comigo escrivão no final assinado, aí compareceram os PERITOS nomeados <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, a quem a Autoridade deferiu o compromisso legal de bem e fielmente desempenharem o encargo, descrevendo com verdade, sem dolo ou malícia, o que encontrarem e os encarregou de procederem ao EXAME DE CONSTATAÇÃO PRELIMINAR na substância acima mencionada. E, nada mais havendo, mandou a Autoridade encerrar o presente Auto que assina com os Peritos e comigo Escrivão que digitei.
                </p>

                <div class="assinatura-area" style="margin-top: 30px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 25px;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Autoridade Policial
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 25px;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Escrivão de Polícia
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; border: none;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Perito
                            </td>
                            <td style="width: 50%; text-align: center; border: none;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Perito
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- QUEBRA DE PÁGINA PARA O AUTO (PÁGINA 2) -->
                <p class="page-break-marker" style="page-break-before: always; break-before: page; height: 0; margin: 0; padding: 0; visibility: hidden;">&nbsp;</p>

                <p style="text-align: center; line-height: 1.6; margin: 5px 0 20px 0; padding: 0;">
                    <strong style="font-size: 16pt; letter-spacing: 1px;">AUTO DE EXAME DE CONSTATAÇÃO PRELIMINAR</strong>
                </p>

                @php
                    $drogasArr = !empty($dadosArray['lista_drogas']) ? $dadosArray['lista_drogas'] : [];
                    $descricoes = [];

                    foreach ($drogasArr as $droga) {
                        $drogaNome = is_array($droga) ? ($droga['nome'] ?? '') : $droga;
                        $drogaQtd = is_array($droga) ? ($droga['qtd'] ?? '______') : '______';
                        $drogaUpper = strtoupper($drogaNome);
                        $drogaQtd = trim($drogaQtd);
                        
                        if ($drogaUpper === 'MACONHA') {
                            $descricoes[] = 'aproximadamente <strong>' . $drogaQtd . '</strong> de material vegetal, apresentando características semelhantes às da erva de coloração castanha esverdeada, denominada <em>Cannabis sativa Linneu</em>, vulgarmente conhecida como <strong>MACONHA</strong>';
                        }
                        elseif ($drogaUpper === 'COCAÍNA' || $drogaUpper === 'COCAINA') {
                            $descricoes[] = 'aproximadamente <strong>' . $drogaQtd . '</strong> de material em pó de cor branca, que se presume ser a droga conhecida como <strong>COCAÍNA</strong>';
                        }
                        elseif ($drogaUpper === 'CRACK') {
                            $descricoes[] = $drogaQtd . ' de substância sólida de cor amarelada, apresentando características e odor da droga popularmente conhecida como <strong>CRACK</strong>';
                        }
                        else {
                            $descricoes[] = 'aproximadamente <strong>' . $drogaQtd . '</strong> de substância análoga a <strong>' . $drogaUpper . '</strong>';
                        }
                    }

                    $textoDescricao = "";
                    if (count($descricoes) >= 1) {
                        if (count($descricoes) > 1) {
                            $textoDescricao = "que as substâncias ora periciadas consistem em:<br>";
                        } else {
                            $textoDescricao = "que a substância ora periciada consiste em:<br>";
                        }
                        $textoDescricao .= '<ul style="list-style-type: none; padding-left: 50px; margin-top: 10px; margin-bottom: 15px; text-indent: 0;">';
                        foreach ($descricoes as $desc) {
                            $textoDescricao .= '<li style="margin-bottom: 8px; text-align: justify; line-height: 1.5; position: relative;">';
                            $textoDescricao .= '<span style="position: absolute; left: -15px;">&#8226;</span>' . $desc . ';</li>';
                        }
                        // Replace last semicolon with period
                        $textoDescricao = preg_replace('/;<\/li>$/', '.</li>', $textoDescricao);
                        $textoDescricao .= '</ul>';
                    } else {
                        $textoDescricao = "que a substância ora periciada trata-se de _______.";
                    }
                @endphp

                <div style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Ao(s) {{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}, nesta cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, e no Cartório da Delegacia de Polícia, onde presente achava-se o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, comigo escrivão ao final assinado, tendo a autoridade policial nomeado como Peritos as pessoas de <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, ambos policiais civis, deferindo-lhes o compromisso legal, de bem e fielmente, sem dolo nem malícia, desempenharem a missão, determinando os exames necessários a fim de que procedam ao EXAME DE CONSTATAÇÃO PRELIMINAR NA SUBSTÂNCIA, apreendida nos autos, conforme boletim de ocorrência nº {{ $dadosArray['boe'] ?? '____' }}, onde constataram {!! $textoDescricao !!}
                </div>

                <div style="text-align: justify; line-height: 1.5; text-indent: 40px;">
                    Nada mais havendo a acrescentar, mandou a Autoridade encerrar o presente auto, o qual depois de lido e achado conforme, segue devidamente assinado, pela Autoridade, pelos peritos e por mim Escrivão, que o digitei.
                </div>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.4;">
                    <table style="width: 100%; border-collapse: separate; border-spacing: 20px 40px;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                                <div style="width: 90%; margin: 0 auto; border-top: 1px solid #000; padding-top: 5px;">
                                    <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                    Autoridade Policial
                                </div>
                            </td>
                            <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                                <div style="width: 90%; margin: 0 auto; border-top: 1px solid #000; padding-top: 5px;">
                                    <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                    Escrivão de Polícia
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                                <div style="width: 90%; margin: 0 auto; border-top: 1px solid #000; padding-top: 5px;">
                                    <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong><br>
                                    Perito
                                </div>
                            </td>
                            <td style="width: 50%; text-align: center; border: none; vertical-align: top;">
                                <div style="width: 90%; margin: 0 auto; border-top: 1px solid #000; padding-top: 5px;">
                                    <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong><br>
                                    Perito
                                </div>
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
                    <i class="fas fa-file-word"></i>
                    <span id="word-count">0 palavras</span>
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

    <script src="{{ asset('js/pages/pericias/ExamePreliminarEntorpecentes.js') }}?v={{ time() }}"></script>
</body>
</html>
