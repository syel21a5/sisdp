<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Certidão - Mídias em Nuvem (Drive) - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    <style>
        .qr-container {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 1px dashed #ccc;
            border-radius: 8px;
            display: inline-block;
            background-color: #f9f9f9;
        }
        .qr-container img {
            max-width: 150px;
            height: auto;
        }
        .link-text {
            font-family: monospace;
            font-size: 14px;
            word-break: break-all;
            margin-top: 10px;
            font-weight: bold;
            color: #0056b3;
        }
    </style>
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-cloud-download-alt"></i>
                Editor: CERTIDÃO DE MÍDIAS EM NUVEM (DRIVE)
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
                
                <h4 style="text-align: center; margin-top: 15px; margin-bottom: 15px;">
                    <strong>CERTIDÃO</strong>
                </h4>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Certifico que, em razão do volume dos arquivos e da inviabilidade de sua inserção integral no sistema, <strong>{{ $dadosArray['descricao_midias'] ?? 'as mídias/vídeos captados pelo sistema de videomonitoramento, os quais registram os fatos relacionados à presente investigação' }}</strong>, referente ao 
                    @if(!empty($dadosArray['boe']) && !empty($dadosArray['ip']))
                        Boletim de Ocorrência nº {{ $dadosArray['boe'] }} / Inquérito Policial nº {{ $dadosArray['ip'] }}
                    @elseif(!empty($dadosArray['boe']))
                        Boletim de Ocorrência nº {{ $dadosArray['boe'] }}
                    @elseif(!empty($dadosArray['ip']))
                        Inquérito Policial nº {{ $dadosArray['ip'] }}
                    @else
                        Boletim de Ocorrência nº ____________________
                    @endif
                    , encontram-se armazenados em ambiente de nuvem (<i>cloud storage</i>).
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Os referidos arquivos podem ser acessados na íntegra por meio do seguinte endereço eletrônico (link) ou apontando a câmera do celular para o QR Code abaixo:
                </p>

                <p style="text-align: center; margin-top: 10px; margin-bottom: 15px;">
                    @if(!empty($dadosArray['link_drive']))
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={{ urlencode($dadosArray['link_drive']) }}" alt="QR Code" style="max-width: 130px; height: auto; border: 1px dashed #ccc; padding: 5px; border-radius: 8px;"><br>
                        <span style="font-family: monospace; font-size: 13px; font-weight: bold; color: #000;">{{ $dadosArray['link_drive'] }}</span>
                    @else
                        <span style="display: inline-block; width: 130px; height: 130px; border: 1px dashed #ccc; line-height: 130px; color: #999; font-size: 12px; border-radius: 8px;">[QR CODE AQUI]</span><br>
                        <span style="font-family: monospace; font-size: 13px; font-weight: bold; color: #000;">Cole o link da nuvem aqui</span>
                    @endif
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Registre-se que o referido conteúdo digital passa a integrar os autos para todos os fins de direito, permanecendo disponível para consulta pela Autoridade Policial, Ministério Público e Poder Judiciário, mediante o acesso ao repositório informado.
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 25px;">
                    Certifico, ainda, que o link e o QR Code disponibilizados possuem permissão de acesso para visualização dos arquivos, de modo a possibilitar sua consulta pelas autoridades competentes, preservando-se a integridade do conteúdo original depositado nesta Delegacia.
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 25px;">
                    E, para constar, lavrei a presente certidão.
                </p>
                
                <p style="text-align: right; line-height: 1.5; margin-bottom: 0;">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'NÃO INFORMADO') }}.
                </p>

                <p>&nbsp;</p>
                <p>&nbsp;</p>

                <p style="text-align: center; line-height: 1.3; margin-top: 0;">
                    ________________________________________<br>
                    <strong>ESCRIVÃO(Ã) DE POLÍCIA</strong>
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

    <script src="{{ asset('js/pages/pecas/CertidaoMidiasDrive.js') }}?v={{ time() }}"></script>
</body>
</html>
