<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laudo Comprobatório de Rompimento de Obstáculo - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor: PERÍCIA DE ARROMBAMENTO
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
                    O(A) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, usando de suas atribuições legais, e, havendo necessidade de proceder exame de corpo de delito na residência/estabelecimento localizado na <strong>{{ $dadosArray['local_fato'] ?? 'NÃO INFORMADO' }}</strong>, nomeia como Peritos os policiais civis: <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, os quais deverão prestar o compromisso legal de estilo.
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
                    Ao(s) {{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}, nesta cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, e no Cartório desta Delegacia de Polícia, onde presente se encontrava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, comigo <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong>, escrivão a seu cargo no final assinado, aí compareceram os peritos nomeados: <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, aos quais a Autoridade deferiu o compromisso legal de bem e fielmente desempenharem o encargo, descrevendo com verdade, sem dolo ou malícia, o que encontrarem e os encarregou de procederem ao exame de corpo de delito no local acima referenciado. E como aceitassem o encargo, mandou a autoridade lavrar este Termo, que lido e achado conforme, assina-o com os peritos e comigo escrivão que o digitei.
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

                <!-- QUEBRA DE PÁGINA PARA O LAUDO (PÁGINA 2) -->
                <p class="page-break-marker" style="page-break-before: always; break-before: page; height: 0; margin: 0; padding: 0; visibility: hidden;">&nbsp;</p>

                <p style="text-align: center; line-height: 1.6; margin: 5px 0 20px 0; padding: 0;">
                    <strong style="font-size: 16pt; letter-spacing: 1px;">LAUDO COMPROBATÓRIO DE ROMPIMENTO DE OBSTÁCULO</strong>
                </p>

                <div style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Ao(s) {{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}, nesta cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, e no Cartório desta Delegacia de Policia, onde presente se encontrava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, comigo <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong>, Escrivão de Policia a seu cargo no final assinado, aí presentes os policiais <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, que na falta de Peritos Oficiais, após devidamente nomeados e compromissados, passaram a realizar o exame de corpo de delito no local indicado (<strong>{{ $dadosArray['local_fato'] ?? 'NÃO INFORMADO' }}</strong>), e a responderem aos quesitos formulados pela Autoridade Policial, abaixo relacionados:
                </div>
                
                <div style="margin-left: 40px; margin-bottom: 15px; line-height: 1.5; text-align: justify;">
                    <strong>1º)</strong> No local acima referido ocorreu algum arrombamento em alguma porta, janela ou obstáculo?<br>
                    <strong>2º)</strong> Em caso afirmativo, o dano referido facilitaria a subtração ou destruição de algum bem móvel?
                </div>

                <div style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    A seguir, passam os peritos a descrever o local de crime e a responder os quesitos acima referidos:
                </div>
                
                <div style="text-align: justify; line-height: 2.0; margin-bottom: 15px; border: 1px dashed #ccc; padding: 15px; background-color: #fcfcfc;">
                    {!! !empty($dadosArray['descricao_laudo']) ? $dadosArray['descricao_laudo'] : '<br><br><br><br>' !!}
                </div>

                <div style="text-align: justify; line-height: 1.5; text-indent: 40px;">
                    Nada mais havendo, determinou a Autoridade o encerramento do presente laudo, que lido e achado conforme, segue assinado pela Autoridade, peritos nomeados e compromissados, e por mim escrivão que o digitei.
                </div>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.3;">
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

    <script src="{{ asset('js/pages/pericias/PericiaArrombamento.js') }}?v={{ time() }}"></script>
</body>
</html>
