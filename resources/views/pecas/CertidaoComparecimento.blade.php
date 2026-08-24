<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Certidão de Comparecimento - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-stamp"></i>
                Editor: CERTIDÃO DE COMPARECIMENTO
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

                <p style="text-align: center; font-size: 18px; line-height: 1.5; margin: 30px 0;">
                    <strong>CERTIDÃO DE COMPARECIMENTO</strong>
                </p>

                <div style="margin-bottom: 20px; line-height: 1.5; text-align: left;">
                    <p style="margin: 0;"><strong>Boletim de Ocorrência nº:</strong> {{ $dadosArray['boe'] ?? '_______' }}</p>
                </div>
                
                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 30px;">
                    <strong>CERTIFICO</strong>, a pedido da parte interessada, que o(a) Sr(a). <strong>{{ mb_strtoupper($dadosArray['comparecimento_nome'] ?? 'NOME DA PESSOA') }}</strong>{{ !empty($dadosArray['comparecimento_doc']) ? ', portador(a) do documento ' . mb_strtoupper($dadosArray['comparecimento_doc']) : '' }}, compareceu a esta Delegacia de Polícia Civil, localizada na cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}-PE, no dia <strong>{{ $dadosArray['comparecimento_data'] ?? '___/___/____' }}</strong>, permanecendo nas dependências desta Unidade Policial no período compreendido entre as <strong>{{ $dadosArray['comparecimento_hora_chegada'] ?? '___:___' }}</strong> e as <strong>{{ $dadosArray['comparecimento_hora_saida'] ?? '___:___' }}</strong>, a fim de prestar esclarecimentos e/ou participar de atos de polícia judiciária referentes à ocorrência em epígrafe.
                </p>

                <p style="text-align: right; line-height: 1.5; margin-bottom: 50px;">
                    O referido é verdade e dou fé.
                </p>

                <p style="text-align: right; line-height: 1.5; margin-bottom: 50px;">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'NÃO INFORMADO') }}.
                </p>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 100%; text-align: center; border: none;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Escrivão de Polícia
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

    <script src="{{ asset('js/pages/pecas/CertidaoComparecimento.js') }}?v={{ time() }}"></script>
</body>
</html>
