<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Liberação do Infrator - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor do TERMO DE LIBERAÇÃO DO INFRATOR
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
                <!-- CONTEÚDO DO TERMO DE LIBERAÇÃO -->
                <p style="text-align: center; line-height: 1.1; margin: 0.2em 0px; padding: 0px;">
                    <strong>
                        <span style="font-family: 'Arial Black', sans-serif; font-size: 14pt;">
                            TERMO DE LIBERAÇÃO, COMPROMISSO E RESPONSABILIDADE DE ADOLESCENTE INFRATOR
                        </span>
                    </strong>
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    <span style="font-family: Arial, sans-serif; font-size: 12pt;">
                        Ao(s) <strong>{{ !empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO' }}</strong>, nesta cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, onde presente se encontrava o(a) Bel(a). <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>, respectivo(a) Delegado(a) de Polícia, comigo, escrivão de seu cargo e ao final assinado, aí presente o Sr.(a) <strong>{{ !empty($dadosArray['nome']) ? $dadosArray['nome'] : 'NÃO INFORMADO' }}</strong>, apreendido por prática de ato infracional conforme registro de Boletim de Ocorrência nº: <strong>{{ !empty($dadosArray['boe']) ? $dadosArray['boe'] : 'NÃO INFORMADO' }}</strong>, <b>ENTREGUE</b> ao seu <b>REPONSÁVEL LEGAL</b>, abaixo assinado, tendo na ocasião assumido o compromisso de apresentá-lo ao representante do Ministério Público, no prazo de 24 horas ou no 1º dia útil, conforme dispõe o Art. 174, da Lei nº 8.069/90, de 13 de Julho de 1990 (Estatuto da Criança e do Adolescente), ficando ainda ciente de que o adolescente não poderá: frequentar casa de jogo ou mal afamada, conviver com pessoa viciosa ou de má vida, frequentar espetáculo capaz de pervertê-lo ou ofender lhe o pudor, ou participar de representação de igual natureza, residir ou trabalhar em casa de prostituição, mendigar ou servir a mendigo para excitar a comiseração pública, sob pena de responsabilidade do adulto que assim o permitir, de conformidade com o Art. 247, do Código Penal Brasileiro. Nada mais havendo a ser consignado, determinou a Autoridade que fosse lavrado este Termo que, depois de lido e achado conforme, o assina com o Representante Legal do adolescente mencionado e comigo, Escrivão, que o digitei.
                    </span>
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    <span style="font-family: Arial, sans-serif; font-size: 12pt;"><br /></span>
                </p>

                <div class="assinatura-area">
                    <p style="line-height: 1.4; margin: 0.1em 0px; padding: 0px;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0px; padding: 0px;"><span style="font-family: Arial, sans-serif; font-size: 12pt;">AUTORIDADE POLICIAL:</span></p>
                    <p style="line-height: 1.4; margin: 0.1em 0px; padding: 0px;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0px; padding: 0px;"><span style="font-family: Arial, sans-serif; font-size: 12pt;">ESCRIVÃO DE POLÍCIA:</span></p>
                    <p style="line-height: 1.4; margin: 0.1em 0px; padding: 0px;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0px; padding: 0px;"><span style="font-family: Arial, sans-serif; font-size: 12pt;">REPRESENTANTE LEGAL:</span></p>
                    <p style="line-height: 1.4; margin: 0.1em 0px; padding: 0px;"><span style="font-family: Arial, sans-serif; font-size: 12pt;"><br /></span></p>
                    <p style="line-height: 1.4; margin: 0.1em 0px; padding: 0px;"><span style="font-family: Arial, sans-serif; font-size: 12pt;">ENDEREÇO:</span></p>
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
    <script src="{{ asset('js/pages/pecas/LiberacaoInfrator.js') }}"></script>
</body>
</html>

</body>
</html>


