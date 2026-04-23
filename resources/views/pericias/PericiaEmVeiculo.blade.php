<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ofício de Perícia em Veículo - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor do OFÍCIO de PERÍCIA EM VEÍCULO
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
                <p style="text-align: right;">
                    {{ $dadosArray['cidade'] ?? 'NÃO INFORMADO' }}, 
                    {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'NÃO INFORMADO') }}
                </p>

                <p><br /></p>

                <p>
                    <strong>Ofício nº {{ $numeroOficio ?? '____' }}</strong><br/>
                    <strong>BOE:</strong> {{ $dadosArray['boe'] ?? '____' }}<br/>
                    <strong>SEI nº.:</strong> {{ $dadosArray['sei'] ?? '____' }}
                </p>

                <p><br /></p>

                <p>
                    <strong>ILMO. SR. GESTOR<br/>UNIDADE REGIONAL DE POLÍCIA CIENTÍFICA DO SERTÃO DO PAJEÚ – URPOC<br/>AFOGADOS DA INGAZEIRA (SDS - GGPOC - GURPOCSP)</strong>
                </p>

                <p><br /></p>

                <p>Senhor(a) Gestor,</p>

                <p><br /></p>

                <p style="text-align: justify; text-indent: 50px;">
                    Sirvo-me do presente para solicitar os bons préstimos de V.S.ª no sentido de proceder a realização de perícia de identificação veicular
                    no(a) <strong>{{ $dadosArray['veiculo_descricao'] ?? '(DESCREVER VEÍCULO)' }}</strong>, apreendido(a) nos autos
                    do IP nº <strong>{{ $dadosArray['ip'] ?? '____' }}</strong>. Requeiro que no laudo pericial conste a informação se o veículo ora
                    periciado poderá ou não ser submetido a processo de reabertura dos números identificadores (Número do motor e chassi), a ser realizado
                    pelo DETRAN/PE ou dos demais Estados, de modo que o proprietário a quem for restituído possa regularizar a posse e livre circulação do
                    veículo.
                </p>

                <p style="text-align: justify; text-indent: 50px;">
                    Encaminhe-se o laudo à {{ $dadosArray['delegacia'] ?? 'NÃO INFORMADO' }}.
                </p>

                <p><br /></p>

                <p style="text-align: center;">Atenciosamente,</p>

                <p><br /></p>

                <p style="text-align: center;">
                    <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br />
                    Delegado(a) de Polícia
                </p>
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
    <script src="{{ asset('js/pages/pericias/PericiaEmVeiculo.js') }}"></script>
</body>
</html>

