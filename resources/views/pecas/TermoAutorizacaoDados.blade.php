<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Autorização para Extração de Dados - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor: TERMO DE AUTORIZAÇÃO PARA EXTRAÇÃO DE DADOS
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
                
                <h4 style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
                    <strong>TERMO DE AUTORIZAÇÃO PARA EXTRAÇÃO DE DADOS EM DISPOSITIVO ELETRÔNICO</strong>
                </h4>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 40px;">
                    Aos <strong>{{ $dadosArray['data_ext'] ?? '__________' }}</strong>, nesta <strong>{{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'Delegacia de Polícia' }}</strong>, compareceu 
                    @if(isset($dadosArray['pessoa_autorizadora']))
                        <strong>{{ mb_strtoupper($dadosArray['pessoa_autorizadora']['nome']) }}</strong>, 
                        Nacionalidade: {{ mb_strtoupper($dadosArray['pessoa_autorizadora']['nacionalidade'] ?? 'NÃO INFORMADO') }}, 
                        Estado Civil: {{ mb_strtoupper($dadosArray['pessoa_autorizadora']['estadoCivil'] ?? 'NÃO INFORMADO') }}, 
                        Profissão: {{ mb_strtoupper($dadosArray['pessoa_autorizadora']['profissao'] ?? 'NÃO INFORMADO') }}, 
                        Data de Nascimento: {{ $dadosArray['pessoa_autorizadora']['nascimento'] ?? 'NÃO INFORMADO' }}, 
                        Filiação: {{ mb_strtoupper($dadosArray['pessoa_autorizadora']['filiacao'] ?? 'NÃO INFORMADO') }}, 
                        Documento: {{ $dadosArray['pessoa_autorizadora']['documento'] ?? 'NÃO INFORMADO' }}, 
                        Endereço: {{ mb_strtoupper($dadosArray['pessoa_autorizadora']['endereco'] ?? 'NÃO INFORMADO') }},
                    @else
                        a pessoa de nome <strong>______________________________________________</strong>,
                    @endif
                    e na presença da Autoridade Policial abaixo assinada, declarou de livre e espontânea vontade que <strong>AUTORIZA</strong>, irrevogavelmente, a verificação e extração de dados (tais como conversas de aplicativos, imagens, vídeos, áudios, contatos e registros de chamadas) contidos no(s) seguinte(s) dispositivo(s): <strong>{{ $dadosArray['dispositivo_eletronico'] ?? '________________________________________' }}</strong>, para subsidiar as investigações e instruir o 
                    @if(!empty($dadosArray['boe']) && !empty($dadosArray['ip']))
                        Boletim de Ocorrência nº {{ $dadosArray['boe'] }} / Inquérito Policial nº {{ $dadosArray['ip'] }}.
                    @elseif(!empty($dadosArray['boe']))
                        Boletim de Ocorrência nº {{ $dadosArray['boe'] }}.
                    @elseif(!empty($dadosArray['ip']))
                        Inquérito Policial nº {{ $dadosArray['ip'] }}.
                    @else
                        procedimento policial nº ____________________.
                    @endif
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 60px;">
                    E, por estarem de pleno acordo, firmam o presente termo.
                </p>

                <div class="assinatura-area" style="line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 40px;">
                                ________________________________________<br>
                                <strong>AUTORIDADE POLICIAL</strong><br>
                                {{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 40px;">
                                ________________________________________<br>
                                <strong>AUTORIZADOR(A)</strong><br>
                                {{ isset($dadosArray['pessoa_autorizadora']) ? mb_strtoupper($dadosArray['pessoa_autorizadora']['nome']) : 'NÃO INFORMADO' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 40px;">
                                ________________________________________<br>
                                <strong>TESTEMUNHA</strong><br>
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 40px;">
                                ________________________________________<br>
                                <strong>TESTEMUNHA</strong><br>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 100%; text-align: center; border: none;">
                                ________________________________________<br>
                                <strong>ESCRIVÃO(Ã) DE POLÍCIA</strong>
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

    <script src="{{ asset('js/pages/pecas/TermoAutorizacaoDados.js') }}?v={{ time() }}"></script>
</body>
</html>
