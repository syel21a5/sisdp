<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ordem de Serviço de Intimação - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-clipboard-list"></i>
                Editor: ORDEM DE SERVIÇO DE INTIMAÇÃO
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
                    <strong>ORDEM DE SERVIÇO - INTIMAÇÃO</strong>
                </p>

                <div style="margin-bottom: 20px; line-height: 1.5; text-align: left;">
                    <p style="margin: 0;"><strong>Boletim de Ocorrência nº:</strong> {{ $dadosArray['boe'] ?? '_______' }}</p>
                    <p style="margin: 0;"><strong>Data de Registro:</strong> {{ $dadosArray['data_cadastro'] ?? 'NÃO INFORMADA' }}</p>
                    <p style="margin: 0;"><strong>Natureza:</strong> {{ $dadosArray['natureza_fato'] ?? 'NÃO INFORMADA' }}</p>
                </div>
                
                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    O(a) Delegado(a) de Polícia Titular da {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'Delegacia de Polícia Civil' }} de {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADO' }}/PE, no uso de suas atribuições legais, nos autos do procedimento em epígrafe:
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    <strong>DETERMINA</strong> ao Agente de Polícia Civil (ou a quem esta couber por distribuição) que proceda, com a máxima brevidade e diligência necessárias, à <strong>INTIMAÇÃO</strong> da(s) pessoa(s) abaixo relacionada(s), para que compareça(m) a esta Unidade Policial a fim de prestar os devidos esclarecimentos sobre os fatos noticiados.
                </p>

                <p style="font-size: 14px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 10px;">PESSOA(S) A SER(EM) INTIMADA(S):</p>
                
                <div style="line-height: 1.5; margin-bottom: 30px;">
                    {!! $dadosArray['intimados_lista'] ?? '<p>NENHUMA PESSOA SELECIONADA.</p>' !!}
                </div>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 50px;">
                    Cumpra-se. Após a realização da diligência, o respectivo relatório, acompanhado da via de recebimento (ciente) ou certidão circunstanciada em caso de recusa/ausência, deverá ser juntado aos autos.
                </p>

                <p style="text-align: right; line-height: 1.5; margin-top: 40px; margin-bottom: 50px;">
                    {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADO' }}, {{ $dadosArray['data_ext'] ?? '____ de ________ de ____' }}
                </p>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 100%; text-align: center; border: none; vertical-align: top;">
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

    <script src="{{ asset('js/pages/pecas/OrdemServicoIntimacao.js') }}?v={{ time() }}"></script>
</body>
</html>
