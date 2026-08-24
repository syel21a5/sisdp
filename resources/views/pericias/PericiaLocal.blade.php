<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ofício - Perícia em Imóvel/Local - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-home"></i>
                Editor: OFÍCIO - PERÍCIA EM IMÓVEL/LOCAL
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
                    <p style="margin: 0;"><strong>De:</strong> {{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</p>
                    <p style="margin: 0;">Delegado(a) de Polícia Civil</p>
                    <p style="margin: 0;"><strong>{{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'DELEGACIA' }}</strong></p>
                    
                    <p style="margin: 20px 0 0 0;"><strong>Ao:</strong> Ilmo(a). Sr(a). Diretor(a) do Instituto de Criminalística</p>
                </div>

                <p style="text-align: left; line-height: 1.5; margin-bottom: 5px;">
                    <strong>ASSUNTO:</strong> Solicitação de perícia em imóvel/residência.
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
                    Senhor(a) Diretor(a),
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Através do presente, para instruir o procedimento policial em epígrafe, venho informar que {{ $dadosArray['relato_fato'] ?? '________________________________________________' }}, e desta feita, solicito que seja realizada a <strong>PERÍCIA NO IMÓVEL / LOCAL</strong> situado na <strong>{{ $dadosArray['endereco_imovel'] ?? '________________________________' }}</strong>, bem como, que seja utilizado o composto orgânico conhecido por <strong>LUMINOL</strong> e/ou outras técnicas forenses aplicáveis, buscando por indícios de sangue ou qualquer outro vestígio que vise esclarecer a materialidade delitiva e dinâmica dos fatos.
                </p>
                
                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 30px;">
                    Certo de vossa atenção, apresento protestos de estima e consideração.
                </p>

                <div class="assinatura-area" style="margin-top: 60px; line-height: 1.3;">
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

    <script src="{{ asset('js/pages/pericias/PericiaLocal.js') }}?v={{ time() }}"></script>
</body>
</html>
