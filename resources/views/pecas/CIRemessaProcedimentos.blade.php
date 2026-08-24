<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>C.I. - Remessa de Procedimento - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-paper-plane"></i>
                Editor: COMUNICAÇÃO INTERNA - REMESSA DE PROCEDIMENTO
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
                    <strong>COMUNICAÇÃO INTERNA Nº {{ $dadosArray['num_oficio'] ?? '_____' }}</strong>
                </p>

                <div style="margin-bottom: 30px; line-height: 1.5;">
                    <p style="margin: 0;"><strong>Do:</strong> {{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }} - Delegado(a) de Polícia</p>
                    <p style="margin: 0;"><strong>Ao:</strong> {{ mb_strtoupper($dadosArray['ci_destinatario'] ?? 'GESTOR(A) DA 20ª DESEC – AFOGADOS DA INGAZEIRA') }}</p>
                </div>
                
                <p style="text-align: left; line-height: 1.5; margin-bottom: 20px;">
                    Exmº Senhor(a) Delegado(a),
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Cumprimentando-o(a) inicialmente, sirvo-me da presente para encaminhar a V.Ex.ª, o {{ mb_strtoupper($dadosArray['ci_tipo_proc'] ?? 'PROCEDIMENTO POLICIAL') }} de nº {{ $dadosArray['ip'] ?? '________' }}, instaurado mediante Boletim de Ocorrência nº {{ $dadosArray['boe'] ?? '_______' }}, 
                    @if(!empty($dadosArray['ci_autores']))
                    em desfavor de <strong>{{ $dadosArray['ci_autores'] }}</strong>, 
                    @endif
                    @if(!empty($dadosArray['ci_vitimas']))
                    tendo como vítima(s) <strong>{{ $dadosArray['ci_vitimas'] }}</strong>, 
                    @endif
                    fato ocorrido no dia {{ $dadosArray['data_fato'] ?? '___/___/___' }}, 
                    @if(!empty($dadosArray['ci_local']))
                    em {{ $dadosArray['ci_local'] }}, 
                    @endif
                    nos termos do(a) {{ $dadosArray['tipificacao'] ?? 'legislação penal vigente' }}.
                </p>

                @if(!empty($dadosArray['ci_fianca']))
                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Ademais, salienta-se que o autuado foi posto em liberdade mediante o recolhimento de fiança criminal. Segue junto a este expediente a quantia em espécie de <strong>R$ {{ $dadosArray['ci_fianca'] }} ({{ $dadosArray['ci_fianca_extenso'] ?? 'valor correspondente' }})</strong>, relativa ao pagamento da referida fiança arbitrada por esta autoridade policial.
                </p>
                @endif

                @if(!empty($dadosArray['ci_apreensoes']))
                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Junto aos autos, seguem ainda os seguintes itens apreendidos:
                </p>
                <div style="margin-left: 60px; margin-bottom: 25px; line-height: 1.5; font-weight: bold;">
                    {!! nl2br(e($dadosArray['ci_apreensoes'])) !!}
                </div>
                @endif

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 30px;">
                    Sendo o que havia para o momento, apresento protestos de estima e consideração.
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

    <script src="{{ asset('js/pages/pecas/CIRemessaProcedimentos.js') }}?v={{ time() }}"></script>
</body>
</html>
