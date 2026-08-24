<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ofício - Encaminhamento de Cadáver ao IML - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-ambulance"></i>
                Editor: OFÍCIO - ENCAMINHAMENTO DE CADÁVER AO IML
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
                
                <p style="text-align: right; line-height: 1.5; margin-bottom: 20px;">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'NÃO INFORMADO') }}.
                </p>

                <p style="text-align: left; line-height: 1.5; margin: 10px 0;">
                    <strong>OFÍCIO Nº {{ $dadosArray['num_oficio'] ?? '_____' }}</strong>
                </p>

                <div style="margin-bottom: 30px; line-height: 1.5;">
                    <p style="margin: 0;"><strong>Ao Ilustríssimo Senhor(a) Diretor(a) do Instituto de Medicina Legal – IML</strong></p>
                    <p style="margin: 0;"><strong>{{ mb_strtoupper($dadosArray['iml_cidade'] ?? 'CARUARU-PE') }}</strong></p>
                    <p style="margin: 0; margin-top: 5px;"><strong>Assunto:</strong> ENCAMINHAMENTO DE CADÁVER PARA EXAME TANATOSCÓPICO</p>
                    <p style="margin: 0;"><strong>Ref.:</strong> Boletim de Ocorrência nº {{ $dadosArray['boe'] ?? '_______' }}</p>
                </div>
                
                <p style="text-align: left; line-height: 1.5; margin-bottom: 20px;">
                    Senhor(a) Diretor(a),
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Cumprimentando-o(a) cordialmente, sirvo-me do presente expediente para encaminhar a esse Instituto de Medicina Legal, a fim de ser submetido a Exame Tanatoscópico, o cadáver de <strong>{{ mb_strtoupper($dadosArray['vitima_nome'] ?? 'NÃO IDENTIFICADO') }}</strong>.
                </p>

                @if(!empty($dadosArray['iml_local_fato']))
                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Informa-se que o óbito / encontro do cadáver ocorreu no seguinte local: {{ $dadosArray['iml_local_fato'] }}.
                </p>
                @endif

                @if(!empty($dadosArray['iml_condutor']))
                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Informa-se ainda que o referido cadáver fora encaminhado por <strong>{{ mb_strtoupper($dadosArray['iml_condutor']) }}</strong>.
                </p>
                @endif

                @if(!empty($dadosArray['iml_familiar_nome']))
                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Outrossim, autorizo o(a) Sr(a). <strong>{{ mb_strtoupper($dadosArray['iml_familiar_nome']) }}</strong>{{ !empty($dadosArray['iml_familiar_parentesco']) ? ', ' . mb_strtoupper($dadosArray['iml_familiar_parentesco']) : '' }}{{ !empty($dadosArray['iml_familiar_rg']) ? ', portador(a) do documento nº ' . mb_strtoupper($dadosArray['iml_familiar_rg']) : '' }}, a providenciar a liberação do referido corpo e receber a respectiva Declaração de Óbito e Laudo Cadavérico.
                </p>
                @endif

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 30px;">
                    Sendo o que havia para o momento, e certos de contarmos com a Vossa colaboração, apresento protestos de elevada estima e distinta consideração.
                </p>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 100%; text-align: center; border: none;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Delegado(a) de Polícia
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

    <script src="{{ asset('js/pages/pecas/OficioIML.js') }}?v={{ time() }}"></script>
</body>
</html>
