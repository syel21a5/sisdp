<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Traumatológico IML - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-medical"></i>
                Editor do TERMO TRAUMATOLÓGICO IML
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
                    {{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : (!empty($dadosArray['data']) ? $dadosArray['data'] : 'NÃO INFORMADO') }}
                </p>

                <p><br /></p>

                <p>
                    <strong>Ofício nº {{ $numeroOficio ?? 'NÃO GERADO' }}</strong><br/>
                    BOE: <strong>{{ $dadosArray['boe'] ?? 'NÃO INFORMADO' }}</strong>
                </p>

                <p><br /></p>

                <p>
                    <strong>ILMO. SR. GESTOR<br />INSTITUTO DE MEDICINA LEGAL - IML - SDS/PE<br /></strong>
                    <strong>PERÍCIAS MÉDICO-LEGAIS - URPOCSP-PMLSP</strong>
                </p>

                <p><br /></p>

                <p>Senhor(a) Gestor,</p>

                <p><br /></p>

                <p style="text-align: justify; text-indent: 50px;">
                    Pelo presente solicito providências de V.Sª., no sentido de submeter a PERÍCIA TRAUMATOLÓGICA a pessoa de 
                    <strong>{{ $dadosArray['nome'] ?? 'NÃO INFORMADO' }}</strong>, 
                    <strong>NASCIMENTO:</strong> {{ $dadosArray['nascimento'] ?? 'NÃO INFORMADO' }}, 
                    <strong>IDADE:</strong> {{ $dadosArray['idade'] ?? 'NÃO INFORMADO' }} ANOS, 
                    <strong>RG:</strong> {{ $dadosArray['rg'] ?? 'NÃO INFORMADO' }}, 
                    <strong>CPF:</strong> {{ $dadosArray['cpf'] ?? 'NÃO INFORMADO' }},
                    <strong>MÃE:</strong> {{ $dadosArray['mae'] ?? 'NÃO INFORMADO' }}, 
                    <strong>PAI:</strong> {{ $dadosArray['pai'] ?? 'NÃO INFORMADO' }}, 
                    <strong>END. RESIDENCIAL:</strong> {{ $dadosArray['endereco'] ?? 'NÃO INFORMADO' }}.
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
    <script src="{{ asset('js/pages/pericias/Traumatologico_IML.js') }}"></script>
</body>
</html>

