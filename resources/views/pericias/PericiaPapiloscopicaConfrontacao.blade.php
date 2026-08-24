<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ofício de Perícia Papiloscópica em Veículo - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor: PERÍCIA DE CONFRONTAÇÃO PAPILOSCÓPICA
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

                <p style="line-height: 1.5;">
                    <strong>Ofício nº {{ $numeroOficio ?? '____' }}</strong><br>
                    <strong>BOE:</strong> {{ $dadosArray['boe'] ?? '____' }}<br>
                    <strong>SEI nº:</strong>
                </p>

                <p><br /></p>

                <p style="line-height: 1.5;">
                    <strong>A SUA EXCELÊNCIA</strong><br>
                    <strong>SENHOR(A) GERENTE DO IITB</strong><br>
                    <strong>INSTITUTO DE IDENTIFICAÇÃO TAVARES BURIL</strong>
                </p>

                <p><br /></p>

                <p style="text-align: justify; line-height: 1.5;">
                    Prezado(a) Gerente,
                </p>
                
                @php
                    $autoresArr = !empty($dadosArray['lista_autores']) ? $dadosArray['lista_autores'] : (!empty($dadosArray['autores']) ? $dadosArray['autores'] : []);
                    $nomesAutores = array_map(function($a) { return !empty($a['nome']) ? strtoupper($a['nome']) : ''; }, $autoresArr);
                    $nomesAutores = array_filter($nomesAutores);
                    $strAutores = count($nomesAutores) > 0 ? implode(', ', $nomesAutores) : (!empty($dadosArray['autor_nome']) ? strtoupper($dadosArray['autor_nome']) : '[NOME DO AUTOR/SUSPEITO]');
                    
                    $vitimasArr = !empty($dadosArray['lista_vitimas']) ? $dadosArray['lista_vitimas'] : (!empty($dadosArray['vitimas']) ? $dadosArray['vitimas'] : []);
                    $nomesVitimas = array_map(function($v) { return !empty($v['nome']) ? strtoupper($v['nome']) : ''; }, $vitimasArr);
                    $nomesVitimas = array_filter($nomesVitimas);
                    $strVitimas = count($nomesVitimas) > 0 ? implode(', ', $nomesVitimas) : (!empty($dadosArray['vitima1_nome']) ? strtoupper($dadosArray['vitima1_nome']) : '[NOME DA VÍTIMA]');
                @endphp
                <p style="text-align: justify; line-height: 1.5;">
                    Cumprimentando-o cordialmente, solicito a V. Exª a realização de <strong>PERÍCIA DE CONFRONTAÇÃO PAPILOSCÓPICA</strong> entre os fragmentos papilares recolhidos no local do fato (vinculados ao presente Boletim de Ocorrência) e as impressões digitais do(s) suspeito(s)/autor(es) <strong>{{ $strAutores }}</strong>, figurando como vítima(s) <strong>{{ $strVitimas }}</strong>.
                </p>
                <div style="height: 12px;"></div>

                <p style="text-align: justify; line-height: 1.5;">
                    Solicito, outrossim, que o respectivo laudo pericial seja encaminhado a esta Delegacia após a sua conclusão.
                </p>

                <p><br /></p>

                <p style="text-align: center; line-height: 1.5;">
                    Atenciosamente,
                </p>
                
                <p><br /></p>
                <p><br /></p>

                <p style="text-align: center; line-height: 1.5;">
                    <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                    Delegado(a) de Polícia
                </p>

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

    <script src="{{ asset('js/pages/pericias/PericiaPapiloscopicaConfrontacao.js') }}?v={{ time() }}"></script>
</body>
</html>
