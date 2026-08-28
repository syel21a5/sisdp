<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ofício de Remessa - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-envelope-open-text"></i>
                Editor: OFÍCIO DE REMESSA
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

                <div style="text-align: right; margin-bottom: 40px;">
                    {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADO' }}, {{ $dadosArray['data_comp'] ?? '____ de ________ de ____' }}.
                </div>
                
                <div style="text-align: left; margin-bottom: 30px; font-weight: bold;">
                    <p style="margin: 0;">Ofício nº {{ $dadosArray['numero_oficio'] ?? '______/____' }}</p>
                    <p style="margin: 0;">Ref.: {{ $dadosArray['remessa_tipo_proc'] ?? 'IP' }} nº {{ $dadosArray['remessa_tombo'] ?? '________' }}</p>
                </div>

                <div style="text-align: left; margin-bottom: 30px; font-weight: bold; line-height: 1.2;">
                    <p style="margin: 0;">A SUA EXCELÊNCIA O(A) SENHOR(A)</p>
                    <p style="margin: 0;">{{ mb_strtoupper($dadosArray['remessa_destinatario'] ?? 'PROMOTOR(A) DE JUSTIÇA DO ESTADO DE PERNAMBUCO') }}</p>
                </div>

                <p style="text-align: left; margin-bottom: 30px;">
                    Senhor(a) {{ $dadosArray['remessa_tratamento'] ?? 'Promotor(a)' }},
                </p>

                <p style="text-align: justify; line-height: 1.6; text-indent: 40px; margin-bottom: 20px;">
                    Cumprimentando-o cordialmente e em estrito cumprimento ao disposto no art. 10, § 1º, do Código de Processo Penal, sirvo-me do presente para encaminhar a Vossa Excelência os autos do <strong>{{ $dadosArray['remessa_tipo_proc'] ?? 'IP' }}</strong> tombado sob o nº <strong>{{ $dadosArray['remessa_tombo'] ?? '________' }}</strong> (Boletim de Ocorrência nº {{ $dadosArray['boe'] ?? '_______' }}), originário desta {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADA' }} – {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADA' }}.
                </p>

                <p style="text-align: justify; line-height: 1.6; text-indent: 40px; margin-bottom: 20px;">
                    O referido procedimento tem como escopo a apuração de fato delituoso ocorrido em {{ $dadosArray['data_fato'] ?? 'NÃO INFORMADA' }}, na cidade de {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADA' }}/PE{!! $dadosArray['tipificacao_trecho'] ?? '' !!}, figurando como {{ $dadosArray['texto_indiciado'] ?? 'indiciado(s)' }}: {!! $dadosArray['autores_qualificados'] ?? 'NENHUM AUTOR CADASTRADO' !!}, e figurando como {{ $dadosArray['texto_vitima'] ?? 'vítima(s)' }}: {!! $dadosArray['vitimas_qualificadas'] ?? 'NENHUMA VÍTIMA CADASTRADA' !!}.
                </p>

                <p style="text-align: justify; line-height: 1.6; text-indent: 40px; margin-bottom: 20px;">
                    Colocando-me à inteira disposição para eventuais esclarecimentos ou diligências complementares que este Douto Órgão julgar necessárias, aproveito a oportunidade para renovar protestos de elevada estima e distinta consideração.
                </p>

                <p>&nbsp;</p>

                <p style="text-align: center; margin: 0;">
                    Respeitosamente,
                </p>

                <p>&nbsp;</p>
                <p>&nbsp;</p>

                <p style="text-align: center; margin: 0;">
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

    <script src="{{ asset('js/pages/pecas/OficioRemessa.js') }}?v={{ time() }}"></script>
</body>
</html>
