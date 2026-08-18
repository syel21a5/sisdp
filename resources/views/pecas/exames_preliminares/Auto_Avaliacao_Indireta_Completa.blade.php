<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AVALIAÇÃO INDIRETA - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor de AVALIAÇÃO INDIRETA</h1>
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
                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 20pt;">P O R T A R I A</strong>
                </p>

                

                <p style="text-align: justify;">
                    <span>O(A) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, usando de suas atribuições legais, e, havendo necessidade de proceder a <strong>AVALIAÇÃO INDIRETA DE OBJETOS</strong>, nomeia como Peritos: <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, ambos policiais civis, os quais deverão prestar o compromisso legal de estilo.</span>
                </p>

                <p style="text-align: center;"><strong>C u m p r a – s e</strong></p>

                <p style="text-align: center;">
                    {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'DATA') }}.
                </p>
                <p>&nbsp;</p>
                <p style="text-align: center;">
                    <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                    Delegado(a) de Polícia
                </p>
                <div style="height: 15px;"></div>
                <p style="text-align: center;"><strong>TERMO DE COMPROMISSO</strong></p>

                <p style="text-align: justify;">
                    <span>Ao(s) <strong>{{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}</strong>, nesta cidade de {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, e no Cartório desta Delegacia de Polícia, onde presente se encontrava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, respectivo Delegado(a), comigo escrivão no final assinado, aí compareceram os PERITOS nomeados <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, <strong>a quem a Autoridade deferiu o compromisso legal de bem e fielmente desempenharem o encargo, descrevendo com verdade, sem dolo ou malícia,</strong> o que encontrarem e os encarregou de procederem a <strong>AVALIAÇÃO INDIRETA DE OBJETOS</strong> do procedimento policial em questão. E como aceitassem o encargo, mandou a Autoridade encerrar o presente Auto que assina com os Peritos e comigo Escrivão que digitei.</span>
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
                            <td style="width: 50%; text-align: left; border: none; padding-bottom: 15px;">
                                <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 15px;">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: left; border: none;">
                                <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                            <td style="width: 50%; text-align: center; border: none;">
                            </td>
                        </tr>
                    </table>
                </div>
            



<p class="page-break-marker" style="page-break-before: always; break-before: page; height: 0; margin: 0; padding: 0; visibility: hidden;">--- QUEBRA DE PÁGINA ---</p>




                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 20pt;">AUTO DE AVALIAÇÃO INDIRETA</strong>
                </p>

                

                <p style="text-align: justify;">
                    Ao(s) <strong>{{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}</strong>, nesta cidade de {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, e no Cartório da Delegacia de Polícia, onde presente achava-se o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, comigo escrivão ao final assinado, tendo a autoridade policial nomeado como Peritos as pessoas de <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, ambos policiais civis, deferindo-lhes o compromisso legal, de bem e fielmente, sem dolo nem malícia, desempenharem a missão, determinado os exames necessários a fim de que procedam ao <strong>AVALIAÇÃO INDIRETA DE OBJETOS</strong>, referidos nos autos, conforme boletim de ocorrência nº <strong>{{ $dadosArray['boe'] ?? 'NÃO INFORMADO' }}</strong>.
                </p>

                <div style="height: 12px;"></div>
                <p style="text-align: justify; line-height: 1.1; {{ empty($dadosArray['apreensao']) ? 'background-color: #ffff00;' : '' }}">
                    <strong>{!! !empty($dadosArray['apreensao']) ? preg_replace("/\r?\n/", "<br><span style=\"display:block; height: 8px;\"></span>", e($dadosArray['apreensao'])) : 'DESCREVER OBJETOS A SER AVALIADOS INDIRETAMENTE;' !!}</strong>
                </p>

                <div style="height: 12px;"></div>
                <p style="text-align: justify;">
                    Ao procederem à avaliação, verificaram <span style="background-color: #00ffff;">QUE OS OBJETOS ACIMA DESCRITOS TÊM O VALOR ATUAL APROXIMADO DE R$: 2.500,00 (dois mil e quinhentos reais)</span>.
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
                            <td style="width: 50%; text-align: left; border: none; padding-bottom: 15px;">
                                <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 15px;">
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: left; border: none;">
                                <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                            <td style="width: 50%; text-align: center; border: none;">
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
    <script src="{{ asset('js/pages/pecas/Avaliacao.js') }}?v={{ time() }}"></script>
</body>
</html>








