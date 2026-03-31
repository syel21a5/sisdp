<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Certidão de Assinatura Individual - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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

        <!-- TOOLBAR DE FERRAMENTAS -->
        <div class="toolbar-container">
            <div class="toolbar-main">
                <div class="toolbar-left" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <div id="toolbar">
                        <span class="ql-formats">
                            <button class="ql-bold" title="Negrito (Ctrl+B)"></button>
                            <button class="ql-italic" title="Itálico (Ctrl+I)"></button>
                            <button class="ql-underline" title="Sublinhado (Ctrl+U)"></button>
                            <button class="ql-strike" title="Tachado"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-color" title="Cor do texto"></select>
                            <select class="ql-background" title="Cor de fundo"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered" title="Lista ordenada"></button>
                            <button class="ql-list" value="bullet" title="Lista com marcadores"></button>
                            <button class="ql-indent" value="-1" title="Diminuir recuo"></button>
                            <button class="ql-indent" value="+1" title="Aumentar recuo"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-align" title="Alinhamento"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-link" title="Inserir link"></button>
                            <button class="ql-image" title="Inserir imagem"></button>
                        </span>
                        <!-- BOTÕES PERSONALIZADOS -->
                        <span class="ql-formats">
                            <button class="ql-page-break" title="Quebra de Página (Ctrl+Enter)">
                                <i class="fas fa-file-alt"></i>
                            </button>
                            <button class="ql-text-case" title="Alternar Maiúsculas/Minúsculas (Shift+F3)">
                                <i class="fas fa-text-height"></i>
                            </button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean" title="Limpar formatação"></button>
                        </span>
                    </div>
                </div>

                <div class="toolbar-right">
                    <button class="btn-custom">
                        <i class="fas fa-file-pdf"></i>
                        Gerar PDF
                    </button>
                </div>
            </div>
        </div>

                <!-- ÁREA DO EDITOR -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">

            <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 20pt;">CERTIDÃO DE ASSINATURA</strong>
                </p>

            <p class="ql-align-right preservar-espacamento">
                <span>{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : 'NÃO INFORMADO' }}</span>
            </p>

            <p class="ql-align-justify preservar-espacamento">
                <br>
            </p>

            <p class="ql-align-justify preservar-espacamento">
                <span>Referência: BOE: {{ !empty($dadosArray['boe']) ? $dadosArray['boe'] : 'NÃO INFORMADO' }}</span>
            </p>

            <p class="ql-align-justify preservar-espacamento">
            <span>Procedimento: {{ !empty($dadosArray['ip']) ? $dadosArray['ip'] : 'NÃO INFORMADO' }}</span>
            </p>

            <p class="ql-align-justify preservar-espacamento">
                <br>
            </p>

            <p class="ql-align-justify preservar-espacamento">
                <span>Certifico, para os devidos fins, que os atos cartorários relativos ao presente procedimento policial serão devidamente firmados, estando os conteúdos e peças submetidos à ciência e anuência das partes envolvidas, conforme lavrado nesta certidão. Dada e passada nesta cidade de Afogados da Ingazeira, Estado de Pernambuco, aos {{ !empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO' }}. Eu, {{ !empty($dadosArray['escrivao']) ? $dadosArray['escrivao'] : 'NÃO INFORMADO' }} _______________________, Escrivã(o), a digitei e subscrevo.</span>
            </p>

            <p class="ql-align-justify preservar-espacamento">
                <br>
            </p>

            <!-- CONDUTOR -->
            <div style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: left;">
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    <strong>NOME</strong>: {{ !empty($dadosArray['nome']) ? $dadosArray['nome'] : 'NÃO INFORMADO' }},
                        <strong>ALCUNHA</strong>: {{ !empty($dadosArray['alcunha']) ? $dadosArray['alcunha'] : 'NÃO INFORMADO' }},
                        <strong>NASCIMENTO</strong>: {{ !empty($dadosArray['nascimento']) ? $dadosArray['nascimento'] : 'NÃO INFORMADO' }},
                        <strong>IDADE</strong>: {{ !empty($dadosArray['idade']) ? $dadosArray['idade'] : 'NÃO INFORMADO' }},
                        <strong>ESTADO CIVIL</strong>: {{ !empty($dadosArray['estcivil']) ? $dadosArray['estcivil'] : 'NÃO INFORMADO' }},
                        <strong>NATURALIDADE</strong>: {{ !empty($dadosArray['naturalidade']) ? $dadosArray['naturalidade'] : 'NÃO INFORMADO' }},
                        <strong>RG</strong>: {{ !empty($dadosArray['rg']) ? $dadosArray['rg'] : 'NÃO INFORMADO' }},
                        <strong>CPF</strong>: {{ !empty($dadosArray['cpf']) ? $dadosArray['cpf'] : 'NÃO INFORMADO' }},
                        <strong>PROFISSÃO</strong>: {{ !empty($dadosArray['profissao']) ? $dadosArray['profissao'] : 'NÃO INFORMADO' }},
                        <strong>INSTRUÇÃO</strong>: {{ !empty($dadosArray['instrucao']) ? $dadosArray['instrucao'] : 'NÃO INFORMADO' }},
                        <strong>TELEFONE</strong>: {{ !empty($dadosArray['telefone']) ? $dadosArray['telefone'] : 'NÃO INFORMADO' }},
                        <strong>MÃE</strong>: {{ !empty($dadosArray['mae']) ? $dadosArray['mae'] : 'NÃO INFORMADO' }},
                        <strong>PAI</strong>: {{ !empty($dadosArray['pai']) ? $dadosArray['pai'] : 'NÃO INFORMADO' }},
                        <strong>ENDEREÇO</strong>: {{ !empty($dadosArray['endereco']) ? $dadosArray['endereco'] : 'NÃO INFORMADO' }}.
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <span>Declaro que li, estou de acordo com o conteúdo e presenciei a assinatura eletrônica do meu Termo de Depoimento e do Termo de Apreensão.</span>
                </p>

                <div class="assinatura-area">
                    <p class="assinatura-linha">Nome por extenso: _______________________________</p>
                    <p class="assinatura-linha">Assinatura: ____________________ Data/Hora: ____/____/______ ____:____</p>
                    <p class="assinatura-linha">Número do documento: ___________________________</p>
                </div>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <p class="preservar-espacamento ql-align-justify">
                    <span><span class="campo-destaque">ADVOGADO</span>: DECLARO que li, estou ciente de acordo com o conteúdo e presenciei a assinatura eletrônica do Termo de Qualificação Interrogatório de meus clientes.</span>
                </p>

                <div class="assinatura-area">
                    <p class="assinatura-linha">Nome por extenso: _______________________________</p>
                    <p class="assinatura-linha">Assinatura: ____________________ Data/Hora: ____/____/______ ____:____</p>
                    <p class="assinatura-linha">Número do documento: ___________________________</p>
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

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = {
            delegacia: @json(isset($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADO'),
            cidade: @json(isset($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO'),
            delegado: @json(isset($dadosArray['delegado']) ? $dadosArray['delegado'] : ''),
            escrivao: @json(isset($dadosArray['escrivao']) ? $dadosArray['escrivao'] : ''),
            nome: @json(isset($dadosArray['nome']) ? $dadosArray['nome'] : ''),
            alcunha: @json(isset($dadosArray['alcunha']) ? $dadosArray['alcunha'] : ''),
            nascimento: @json(isset($dadosArray['nascimento']) ? $dadosArray['nascimento'] : ''),
            idade: @json(isset($dadosArray['idade']) ? $dadosArray['idade'] : ''),
            estcivil: @json(isset($dadosArray['estcivil']) ? $dadosArray['estcivil'] : ''),
            naturalidade: @json(isset($dadosArray['naturalidade']) ? $dadosArray['naturalidade'] : ''),
            rg: @json(isset($dadosArray['rg']) ? $dadosArray['rg'] : ''),
            cpf: @json(isset($dadosArray['cpf']) ? $dadosArray['cpf'] : ''),
            profissao: @json(isset($dadosArray['profissao']) ? $dadosArray['profissao'] : ''),
            instrucao: @json(isset($dadosArray['instrucao']) ? $dadosArray['instrucao'] : ''),
            telefone: @json(isset($dadosArray['telefone']) ? $dadosArray['telefone'] : ''),
            mae: @json(isset($dadosArray['mae']) ? $dadosArray['mae'] : ''),
            pai: @json(isset($dadosArray['pai']) ? $dadosArray['pai'] : ''),
            endereco: @json(isset($dadosArray['endereco']) ? $dadosArray['endereco'] : ''),
            boe: @json(isset($dadosArray['boe']) ? $dadosArray['boe'] : ''),
            ip: @json(isset($dadosArray['ip']) ? $dadosArray['ip'] : 'NÃO INFORMADO'),
            data_ext: @json(isset($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO')
        };
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/certidaoassinaturas/CertidaoAssinaturasIndividual.js') }}"></script>

</body>
</html>
