<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Auto de Depósito Fiel - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-box-open"></i>
                Editor: AUTO DE DEPÓSITO FIEL
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
                    <strong>AUTO DE DEPÓSITO FIEL</strong>
                </p>

                <div style="margin-bottom: 20px; line-height: 1.5; text-align: left;">
                    <p style="margin: 0;"><strong>Boletim de Ocorrência nº:</strong> {{ $dadosArray['boe'] ?? '_______' }}</p>
                </div>
                
                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    Aos {{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}, nesta cidade de {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADO' }}, Estado de Pernambuco, na {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'Delegacia de Polícia Civil' }}, onde se encontrava o(a) Excelentíssimo(a) Senhor(a) Delegado(a) de Polícia Titular que este subscreve, comigo Escrivão(ã) ao seu cargo. 
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    Aí sendo, pela Autoridade Policial foi nomeado(a) como <strong>FIEL DEPOSITÁRIO(A)</strong> a pessoa de <strong>{{ mb_strtoupper($dadosArray['deposito_fiel_nome'] ?? 'NOME DO DEPOSITÁRIO') }}</strong>, portador(a) do RG nº {{ $dadosArray['deposito_fiel_rg'] ?? '_____' }} e CPF nº {{ $dadosArray['deposito_fiel_cpf'] ?? '_____' }}, residente e domiciliado(a) na(o) {{ $dadosArray['deposito_fiel_endereco'] ?? '_____' }}, o(a) qual recebe neste ato, assumindo o compromisso legal de guarda, conservação e apresentação quando solicitado, os seguintes bens/objetos vinculados à ocorrência em epígrafe:
                </p>

                <p style="text-align: justify; line-height: 1.8; margin-left: 40px; margin-bottom: 20px;">
                    <em>{{ !empty($dadosArray['deposito_fiel_objetos']) ? $dadosArray['deposito_fiel_objetos'] : '(Descrever detalhadamente os objetos, veículos ou bens entregues em depósito)' }}</em>
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 50px;">
                    O(a) Depositário(a) fica ciente de que assume o encargo sob as penas da Lei, comprometendo-se a não vender, alugar, emprestar, ceder ou se desfazer de qualquer forma do(s) bem(ns) acima descrito(s) sem a prévia e expressa autorização judicial. E, por estarem justos e acertados, assinam o presente termo, que vai assinado por todos.
                </p>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-right: 20px; vertical-align: top;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Delegado(a) de Polícia
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-left: 20px; vertical-align: top;">
                                ________________________________________<br>
                                <strong>{{ mb_strtoupper($dadosArray['deposito_fiel_nome'] ?? 'NOME DO DEPOSITÁRIO') }}</strong><br>
                                Fiel Depositário(a)
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 100%; text-align: center; border: none; padding-top: 40px;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Escrivão(ã) de Polícia
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

    <script src="{{ asset('js/pages/pecas/AutoDepositoFiel.js') }}?v={{ time() }}"></script>
</body>
</html>
