<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Eficiência - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor do AUTO do TERMO DE EFICIÊNCIA DE ARMA DE FOGO
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
                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 20pt;">AUTO DE EFICIÊNCIA DE ARMA DE FOGO</strong>
                </p>

                <p><br></p>

                <p style="text-align: justify;">
                    Ao(s) <strong>{{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}</strong>, nesta cidade de {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, e no Cartório da Delegacia de Polícia, onde presente achava-se o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, comigo escrivão ao final assinado, <strong>tendo a autoridade policial nomeado como Peritos Criminais ad hoc os policiais civis {{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }} e {{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, deferindo-lhes o compromisso legal de bem e fielmente desempenharem a missão, determinado os exames necessários a fim de que os mesmos possam <span style="background-color: #ffff00;">CONSTATAR A EFICIÊNCIA DA ARMA DE FOGO</span>, referida nos autos, conforme boletim de ocorrência nº <strong>{{ $dadosArray['boe'] ?? 'NÃO INFORMADO' }}</strong>.
                </p>

                <p><br></p>

                <p style="text-align: justify;">
                    Os peritos acima indicados, após analisar a arma de fogo, efetuaram vários disparos, tendo todos percutido com sucesso as munições utilizadas, concluindo assim que a <strong>ARMA DE FOGO ACIMA INDICADA ENCONTRA-SE EM PERFEITO ESTADO DE CONSERVAÇÃO E FUNCIONAMENTO, ESTANDO APTA A EFETUAR DISPAROS</strong>.
                </p>

                <p><br></p>

                <div class="assinatura-area">
                    <p style="border-top: 1px solid #000; padding-top: 5px;"><strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong></p>
                    <p>Autoridade Policial</p>
                    <p><br></p>
                    <p>ESCRIVÃO DE POLÍCIA</p>
                    <p style="border-top: 1px solid #000; padding-top: 5px;"><strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong></p>
                    <p><br></p>
                    <p>PERITO</p>
                    <p style="border-top: 1px solid #000; padding-top: 5px;"><strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong></p>
                    <p><br></p>
                    <p>PERITO</p>
                    <p style="border-top: 1px solid #000; padding-top: 5px;"><strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong></p>
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
    <script src="{{ asset('js/pages/pecas/EficienciaArma.js') }}"></script>
</body>
</html>


