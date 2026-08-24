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
                Editor: PERÍCIA PAPILOSCÓPICA EM LOCAL DE CRIME
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
                
                <p style="text-align: justify; line-height: 1.5;">
                    Cumprimentando-o cordialmente, solicito a V. Exª que encaminhe uma equipe de Peritos Papiloscopistas com o intuito de realizar a PERÍCIA PAPILOSCÓPICA EM LOCAL DE CRIME do(a) <strong>{{ !empty($dadosArray['incidencia_penal']) ? strtoupper($dadosArray['incidencia_penal']) : '[INCIDÊNCIA PENAL]' }}</strong>, figurando como vítima(s) a(s) pessoa(s) abaixo qualificada(s); fato ocorrido no <strong>{{ !empty($dadosArray['local_fato']) ? strtoupper($dadosArray['local_fato']) : '[DESCREVER LOCAL DO FATO]' }}</strong>, no dia de <strong>{{ !empty($dadosArray['data_fato']) ? $dadosArray['data_fato'] : '[DATA DO FATO]' }}</strong>.
                </p>

                @if(!empty($dadosArray['lista_vitimas']) && count($dadosArray['lista_vitimas']) > 0)
                    <p style="text-align: justify; line-height: 1.5;">
                        <strong>QUALIFICAÇÃO DAS VÍTIMAS:</strong>
                    </p>
                    <ol style="margin-left: 20px;">
                    @foreach($dadosArray['lista_vitimas'] as $vitima)
                        <li style="text-align: justify; line-height: 1.5; margin-bottom: 12px;">
                            <strong>{{ !empty($vitima['nome']) ? strtoupper($vitima['nome']) : '[NOME DA VÍTIMA]' }}</strong>, NASCIMENTO: <strong>{{ !empty($vitima['nascimento']) ? $vitima['nascimento'] : (!empty($vitima['data_nascimento']) ? $vitima['data_nascimento'] : '[DATA_NASC]') }}</strong>, IDADE: <strong>{{ !empty($vitima['idade']) ? $vitima['idade'] : '[IDADE]' }}</strong> ANOS, RG: <strong>{{ !empty($vitima['rg']) ? $vitima['rg'] : '[RG]' }}</strong>, CPF: <strong>{{ !empty($vitima['cpf']) ? $vitima['cpf'] : '[CPF]' }}</strong>, MÃE: <strong>{{ !empty($vitima['mae']) ? strtoupper($vitima['mae']) : '[NOME DA MÃE]' }}</strong>, PAI: <strong>{{ !empty($vitima['pai']) ? strtoupper($vitima['pai']) : '[NOME DO PAI]' }}</strong>, END. RESIDENCIAL: <strong>{{ !empty($vitima['endereco']) ? strtoupper($vitima['endereco']) : '[ENDEREÇO]' }}</strong>
                        </li>
                    @endforeach
                    </ol>
                @else
                    <p style="text-align: justify; line-height: 1.5;">
                        <strong>QUALIFICAÇÃO DA VÍTIMA:</strong>
                    </p>
                    
                    <div style="height: 12px;"></div>
                    <p style="text-align: justify; line-height: 1.5; margin-left: 30px;">
                        @php
                            // Tenta pegar de vitima1_nome ou o primeiro item de vitimas caso venha de outro lugar
                            $singleVitima = !empty($dadosArray['vitimas'][0]) ? $dadosArray['vitimas'][0] : null;
                            $nomeVit = $dadosArray['vitima1_nome'] ?? ($singleVitima['nome'] ?? '[NOME DA VÍTIMA 1]');
                            $nascVit = $dadosArray['vitima1_nascimento'] ?? ($singleVitima['nascimento'] ?? '[DATA_NASC]');
                            $idadeVit = $dadosArray['vitima1_idade'] ?? ($singleVitima['idade'] ?? '[IDADE]');
                            $rgVit = $dadosArray['vitima1_rg'] ?? ($singleVitima['rg'] ?? '[RG]');
                            $cpfVit = $dadosArray['vitima1_cpf'] ?? ($singleVitima['cpf'] ?? '[CPF]');
                            $maeVit = $dadosArray['vitima1_mae'] ?? ($singleVitima['mae'] ?? '[NOME DA MÃE]');
                            $paiVit = $dadosArray['vitima1_pai'] ?? ($singleVitima['pai'] ?? '[NOME DO PAI]');
                            $endVit = $dadosArray['vitima1_endereco'] ?? ($singleVitima['endereco'] ?? '[ENDEREÇO]');
                        @endphp
                        <strong>{{ strtoupper($nomeVit) }}</strong><br>
                        <strong>NASCIMENTO:</strong> {{ $nascVit }} 
                        <strong>IDADE:</strong> {{ $idadeVit }} ANOS<br>
                        <strong>RG:</strong> {{ $rgVit }} 
                        <strong>CPF:</strong> {{ $cpfVit }}<br>
                        <strong>MÃE:</strong> {{ strtoupper($maeVit) }}<br>
                        <strong>PAI:</strong> {{ strtoupper($paiVit) }}<br>
                        <strong>END. RESIDENCIAL:</strong> {{ strtoupper($endVit) }}
                    </p>
                    <div style="height: 12px;"></div>
                @endif

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}?v={{ time() }}"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <script src="{{ asset('js/pages/pericias/PericiaPapiloscopicaEmLocalDeCrime.js') }}?v={{ time() }}"></script>
</body>
</html>
