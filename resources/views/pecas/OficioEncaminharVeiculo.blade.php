<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ofício - Encaminhar Veículo (CIRETRAN) - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-motorcycle"></i>
                Editor: OFÍCIO - ENCAMINHAR VEÍCULO AO CIRETRAN
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
                
                <p style="text-align: left; line-height: 1.5; margin-bottom: 30px;">
                    <strong>REF.:</strong> 
                    @if(!empty($dadosArray['boe']) && !empty($dadosArray['ip']))
                        Boletim de Ocorrência nº {{ $dadosArray['boe'] }} / Inquérito Policial nº {{ $dadosArray['ip'] }}
                    @elseif(!empty($dadosArray['boe']))
                        Boletim de Ocorrência nº {{ $dadosArray['boe'] }}
                    @elseif(!empty($dadosArray['ip']))
                        Inquérito Policial nº {{ $dadosArray['ip'] }}
                    @else
                        Boletim de Ocorrência nº ____________________
                    @endif
                </p>

                <p style="text-align: left; line-height: 1.5; margin-bottom: 15px;">
                    <strong>ASSUNTO:</strong> REMESSA E ENCAMINHAMENTO DE VEÍCULO
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Cumprimentando-o cordialmente, encaminho para o pátio dess{{ mb_strtoupper($dadosArray['ciretran_destino'] ?? 'CIRETRAN') }}, o veículo automotor com as seguintes características: <strong>{{ mb_strtoupper($dadosArray['dados_veiculo'] ?? '________________________________________________') }}</strong>.
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Cabe salientar que o referido veículo {{ $dadosArray['justificativa'] ?? '________________________________________________' }}, conforme registro efetuado nesta Unidade Policial através do Boletim de Ocorrência em epígrafe.
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 30px;">
                    Sendo o que havia para o momento, apresento protestos de estima e consideração.
                </p>

                <div class="assinatura-area" style="margin-top: 40px; line-height: 1.3;">
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
                
                <div style="margin-top: 80px; line-height: 1.5;">
                    <p style="margin: 0;">À Sua Senhoria o(a)</p>
                    <p style="margin: 0;"><strong>GESTOR(A) DA {{ mb_strtoupper($dadosArray['ciretran_destino'] ?? 'CIRETRAN') }}</strong></p>
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

    <script src="{{ asset('js/pages/pecas/OficioEncaminharVeiculo.js') }}?v={{ time() }}"></script>
</body>
</html>
