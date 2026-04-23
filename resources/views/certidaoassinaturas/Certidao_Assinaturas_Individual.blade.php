<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Certidão de Assinatura Individual - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor da CERTIDAO DE ASSINATURA INDIVIDUAL
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
                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 20pt;">CERTIDÃO DE ASSINATURA</strong>
                </p>

                <p style="text-align: right;">
                    <span>{{ $dadosArray['cidade'] ?? 'NÃO INFORMADO' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'NÃO INFORMADO') }}</span>
                </p>

                <p><br></p>

                <p>
                    <span>Referência: BOE: {{ $dadosArray['boe'] ?? 'NÃO INFORMADO' }}</span>
                </p>

                <p>
                    <span>Procedimento: {{ $dadosArray['ip'] ?? 'NÃO INFORMADO' }}</span>
                </p>

                <p><br></p>

                <p style="text-align: justify;">
                    <span>Certifico, para os devidos fins, que os atos cartorários relativos ao presente procedimento policial serão devidamente firmados, estando os conteúdos e peças submetidos à ciência e anuência das partes envolvidas, conforme lavrado nesta certidão. Dada e passada nesta cidade de {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, Estado de Pernambuco, aos {{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}. Eu, {{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }} _______________________, Escrivã(o), a digitei e subscrevo.</span>
                </p>

                <p><br></p>

                <!-- ENVOLVIDO -->
                <div style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: left;">
                    <p style="text-align: justify;">
                        <strong>NOME</strong>: {{ $dadosArray['nome'] ?? 'NÃO INFORMADO' }},
                        <strong>ALCUNHA</strong>: {{ $dadosArray['alcunha'] ?? 'NÃO INFORMADO' }},
                        <strong>NASCIMENTO</strong>: {{ $dadosArray['nascimento'] ?? 'NÃO INFORMADO' }},
                        <strong>IDADE</strong>: {{ $dadosArray['idade'] ?? 'NÃO INFORMADO' }},
                        <strong>ESTADO CIVIL</strong>: {{ $dadosArray['estcivil'] ?? 'NÃO INFORMADO' }},
                        <strong>NATURALIDADE</strong>: {{ $dadosArray['naturalidade'] ?? 'NÃO INFORMADO' }},
                        <strong>RG</strong>: {{ $dadosArray['rg'] ?? 'NÃO INFORMADO' }},
                        <strong>CPF</strong>: {{ $dadosArray['cpf'] ?? 'NÃO INFORMADO' }},
                        <strong>PROFISSÃO</strong>: {{ $dadosArray['profissao'] ?? 'NÃO INFORMADO' }},
                        <strong>INSTRUÇÃO</strong>: {{ $dadosArray['instrucao'] ?? 'NÃO INFORMADO' }},
                        <strong>TELEFONE</strong>: {{ $dadosArray['telefone'] ?? 'NÃO INFORMADO' }},
                        <strong>MÃE</strong>: {{ $dadosArray['mae'] ?? 'NÃO INFORMADO' }},
                        <strong>PAI</strong>: {{ $dadosArray['pai'] ?? 'NÃO INFORMADO' }},
                        <strong>ENDEREÇO</strong>: {{ $dadosArray['endereco'] ?? 'NÃO INFORMADO' }}.
                    </p>

                    <p><br></p>

                    <p style="text-align: justify;">
                        <span>Declaro que li, estou de acordo com o conteúdo e presenciei a assinatura eletrônica do meu Termo de Depoimento e do Termo de Apreensão.</span>
                    </p>

                    <div class="assinatura-area">
                        <p>Nome por extenso: _______________________________</p>
                        <p>Assinatura: ____________________ Data/Hora: ____/____/______ ____:____</p>
                        <p>Número do documento: ___________________________</p>
                    </div>

                    <p><br></p>

                    <p style="text-align: justify;">
                        <span><span class="campo-destaque">ADVOGADO</span>: DECLARO que li, estou ciente de acordo com o conteúdo e presenciei a assinatura eletrônica do Termo de Qualificação Interrogatório de meus clientes.</span>
                    </p>

                    <div class="assinatura-area">
                        <p>Nome por extenso: _______________________________</p>
                        <p>Assinatura: ____________________ Data/Hora: ____/____/______ ____:____</p>
                        <p>Número do documento: ___________________________</p>
                    </div>
                </div>
            </div>

            <div class="editor-stats">
                <div class="stat-item">
                    <i class="fas fa-keyboard"></i>
                    <span id="char-count">0 caracteres</span>
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
    <script src="{{ asset('js/DocumentoService.js') }}"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/certidaoassinaturas/CertidaoAssinaturasIndividual.js') }}"></script>
</body>
</html>

