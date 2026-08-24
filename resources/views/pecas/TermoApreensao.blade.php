<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Apreensão - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-hand-holding-box"></i>
                Editor: TERMO DE APREENSÃO
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

                <p style="text-align: center; font-size: 18px; line-height: 1.5; margin: 30px 0;">
                    <strong>TERMO DE APREENSÃO</strong>
                </p>

                <div style="margin-bottom: 20px; line-height: 1.5; text-align: left;">
                    <p style="margin: 0;"><strong>Boletim de Ocorrência nº:</strong> {{ $dadosArray['boe'] ?? '_______' }}</p>
                </div>
                
                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    Aos {{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}, nesta cidade de {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADO' }}, Estado de Pernambuco, na {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'Delegacia de Polícia Civil' }}, onde se encontrava o(a) Excelentíssimo(a) Senhor(a) Delegado(a) de Polícia Titular que este subscreve, comigo Escrivão(ã) ao seu cargo. 
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    Aí sendo, compareceu o(a) Sr.(a) <strong>{{ mb_strtoupper($dadosArray['apreensao_apresentador'] ?? 'NOME DO APRESENTADOR') }}</strong>, o(a) qual apresentou nesta Unidade Policial o(s) objeto(s)/bem(ns) abaixo discriminado(s), os quais foram arrecadados no(a) <strong>{{ mb_strtoupper($dadosArray['apreensao_local'] ?? 'LOCAL DA APREENSÃO') }}</strong>. 
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    Sendo assim, em face das fundadas suspeitas de que os referidos bens guardem relação com as infrações noticiadas no Boletim de Ocorrência em epígrafe, a Autoridade Policial determinou a <strong>APREENSÃO</strong> dos seguintes objetos:
                </p>

                <p style="text-align: justify; line-height: 1.8; margin-left: 40px; margin-bottom: 20px;">
                    <em>{{ !empty($dadosArray['apreensao_objetos']) ? $dadosArray['apreensao_objetos'] : '(Descrever detalhadamente os objetos apreendidos: marca, modelo, cor, quantidade, estado de conservação, números de série etc)' }}</em>
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 50px;">
                    E, para constar, lavrou-se o presente termo, que, lido e achado conforme, vai devidamente assinado.
                </p>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-right: 20px; vertical-align: top;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Delegado(a) de Polícia
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-left: 20px; vertical-align: top;">
                                ________________________________________<br>
                                <strong>{{ mb_strtoupper($dadosArray['apreensao_apresentador'] ?? 'NOME DO APRESENTADOR') }}</strong><br>
                                Condutor(a) / Apresentador(a)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 100%; text-align: center; border: none; padding-top: 40px;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Escrivão(ã) de Polícia
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

    <script src="{{ asset('js/pages/pecas/TermoApreensao.js') }}?v={{ time() }}"></script>
</body>
</html>
