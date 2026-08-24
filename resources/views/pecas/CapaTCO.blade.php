<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Capa de TCO - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-alt"></i>
                Editor: CAPA DE TCO ({{ mb_strtoupper($dadosArray['capa_tipo'] ?? 'FLAGRANTE') }})
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

                <p style="text-align: center; font-size: 16px; line-height: 1.5; margin: 20px 0 10px 0;">
                    <strong>TERMO CIRCUNSTANCIADO DE OCORRÊNCIA{{ isset($dadosArray['capa_tipo']) && $dadosArray['capa_tipo'] === 'Portaria' ? ' (PORTARIA)' : '' }}</strong><br>
                    <span style="font-size: 14px; font-weight: normal;">(Art. 69 da Lei nº 9.099 de 26 de setembro de 1995)</span>
                </p>

                <p style="text-align: center; font-size: 18px; margin-bottom: 30px;">
                    <strong>TCO Nº: _______________/______</strong>
                </p>

                <div style="line-height: 1.5; text-align: left; margin-bottom: 20px;">
                    <p style="margin: 0;"><strong>DATA DO CADASTRO:</strong> {{ $dadosArray['data_cadastro'] ?? 'NÃO INFORMADA' }}</p>
                    <p style="margin: 0;"><strong>HORA DO CADASTRO:</strong> {{ $dadosArray['hora_cadastro'] ?? 'NÃO INFORMADA' }}</p>
                    <p style="margin: 0;"><strong>DELEGACIA:</strong> {{ $dadosArray['delegacia'] ?? 'NÃO INFORMADA' }}</p>
                    <p style="margin: 0;"><strong>BO Nº:</strong> {{ $dadosArray['boe'] ?? '_______' }}</p>
                </div>
                
                <p style="font-size: 14px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 10px;">OCORRÊNCIA</p>
                <div style="line-height: 1.5; text-align: left; margin-bottom: 20px;">
                    <p style="margin: 0;"><strong>DATA DO FATO:</strong> {{ $dadosArray['data_fato'] ?? 'NÃO INFORMADA' }} <strong>- HORA DO FATO:</strong> {{ $dadosArray['hora_fato'] ?? 'NÃO INFORMADA' }}</p>
                    <p style="margin: 0;"><strong>LOCAL DO FATO:</strong> {{ $dadosArray['logradouro'] ?? '' }}{{ isset($dadosArray['numero']) ? ', ' . $dadosArray['numero'] : '' }}{{ isset($dadosArray['bairro']) ? ' - ' . $dadosArray['bairro'] : '' }}{{ isset($dadosArray['cidade_nome']) ? ', ' . $dadosArray['cidade_nome'] . '/PE' : '' }}</p>
                    <p style="margin: 0;"><strong>TIPIFICAÇÃO PENAL:</strong> {{ $dadosArray['natureza_fato'] ?? 'NÃO INFORMADA' }}</p>
                </div>

                <p style="font-size: 14px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 10px;">ENVOLVIDOS</p>
                
                <div style="line-height: 1.5; margin-bottom: 20px;">
                    <p style="margin: 0;"><strong>VÍTIMA(S):</strong><br>
                    {!! $dadosArray['vitimas'] ?? 'NENHUMA VÍTIMA CADASTRADA' !!}</p>
                </div>

                <div style="line-height: 1.5; margin-bottom: 20px;">
                    <p style="margin: 0;"><strong>INFRATOR(A / ES):</strong><br>
                    {!! $dadosArray['autores'] ?? 'NENHUM INFRATOR CADASTRADO' !!}</p>
                </div>

                @if(isset($dadosArray['capa_tipo']) && $dadosArray['capa_tipo'] === 'Flagrante')
                <div style="line-height: 1.5; margin-bottom: 20px;">
                    <p style="margin: 0;"><strong>CONDUTOR(A):</strong><br>
                    {!! $dadosArray['condutores'] ?? 'NENHUM CONDUTOR CADASTRADO' !!}</p>
                </div>
                @endif

                <div style="line-height: 1.5; margin-bottom: 20px;">
                    <p style="margin: 0;"><strong>TESTEMUNHA(S):</strong><br>
                    {!! $dadosArray['testemunhas'] ?? 'NENHUMA TESTEMUNHA CADASTRADA' !!}</p>
                </div>

                <p style="font-size: 14px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 10px;">HISTÓRICO DO REGISTRO</p>
                <div style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    <p>{!! nl2br(e($dadosArray['historico'] ?? 'NÃO INFORMADO')) !!}</p>
                </div>

                @if(isset($dadosArray['capa_tipo']) && $dadosArray['capa_tipo'] === 'Portaria')
                <p style="font-size: 14px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 10px;">OBJETOS APREENDIDOS</p>
                <div style="text-align: justify; line-height: 1.8; margin-bottom: 20px;">
                    <p>{{ $dadosArray['objetos_apreendidos'] ?? 'Nada apreendido.' }}</p>
                </div>
                @endif

                <p style="text-align: right; line-height: 1.5; margin-top: 40px; margin-bottom: 50px;">
                    {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADO' }}, {{ $dadosArray['data_ext'] ?? '____ de ________ de ____' }}
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

    <script src="{{ asset('js/pages/pecas/CapaTCO.js') }}?v={{ time() }}"></script>
</body>
</html>
