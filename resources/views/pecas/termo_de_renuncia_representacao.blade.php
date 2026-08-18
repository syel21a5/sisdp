<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Renúncia de Representação - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor do TERMO DE RENÚNCIA E DESISTÊNCIA DE REPRESENTAÇÃO
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
                <!-- CONTEÚDO DO TERMO DE RENÚNCIA -->
                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 20pt;">TERMO DE RENÚNCIA E DESISTÊNCIA DE REPRESENTAÇÃO</strong>
                </p>
                <p style="line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;</p>
                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    Ao(s) <strong>{{ $dadosArray['data_ext'] ?? ($dadosArray['data_extenso'] ?? 'NÃO INFORMADO') }}</strong>, nesta cidade de Afogados da Ingazeira, onde presente
                    se encontrava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, respectivo(a) Delegado(a)
                    de Polícia, comigo Escrivão, servindo ao seu cargo e ao final assinado,
                    aí compareceu o(a) Sr(ª). <strong>{{ $dadosArray['nome'] ?? 'NÃO INFORMADO' }}</strong>, já qualificado(a) nos autos,
                    o(a) qual vem perante a autoridade Policial, <strong>EXPOR O DESEJO DE
                    RENUNCIAR AO DIREITO DE REPRESENTAÇÃO</strong>, conforme dispõe o Art. 50 do
                    CPP, no que se refere ao fato registrado no <strong>Boletim de
                    Ocorrência</strong> nº <strong>{{ $dadosArray['boe'] ?? 'NÃO INFORMADO' }}</strong>, alegando não mais ter interesse em
                    prosseguir com a referida queixa. Nada mais havendo, mandou a Autoridade encerrar o
                    presente Termo que, depois de lido e achado conforme, assina com a
                    Representante e comigo, Escrivão, que o digitei.
                </p>
                <p style="line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;</p>

                <!-- Área de assinaturas -->
                <div class="assinatura-area">
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;"><strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong></p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">Autoridade Policial</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">RENUNCIANTE:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;"><strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong></p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">Escrivão de Policia</p>
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
    <script src="{{ asset('js/pages/pecas/RenunciaRepresentacao.js') }}"></script>
</body>
</html>

