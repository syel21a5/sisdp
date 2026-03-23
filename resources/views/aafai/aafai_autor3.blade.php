<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AAFAI AUTOR 3 - Editor Profissional</title>
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
                Editor do AAFAI AUTOR 3
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
                        <!-- MESMA TOOLBAR DO INTERROGATÓRIO -->
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
            <strong style="font-size: 16pt;">AUTO DE APREENSAO EM FLAGRANTE DE ATO INFRACIONAL</strong><br>
            <span style="font-size: 14px; font-weight: normal;">(ART. 173, I DA LEI Nº 8.069, DE 13/07/1990)</span>
            </p>

             <p class="ql-align-justify preservar-espacamento">
                <br>
            </p>

            <!-- CONDUTOR -->
            <div style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: left;">
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                        Prosseguindo, passou a Autoridade a qualificar e inquirir o <strong>INFRATOR</strong>: {{ !empty($dadosArray['nome']) ? $dadosArray['nome'] : 'NÃO INFORMADO' }},
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
                    <span>A seguir, tendo a Autoridade Policial dado ciência a(o) adolescente infrator(a), do nome do(a) responsável pela sua apreensão, declinou-lhe, ainda, o próprio nome, tornando-o conhecedor de seus direitos individuais constantes da Lei Federal nº. 8.069/90 - Estatuto da Criança e do Adolescente - solicitando que fosse avisado(a) <span style="background-color: cyan;">(sua respectiva genitora, em seu endereço residencial)</span>. Interrogado(a) acerca da imputação que lhe é feita, RESPONDEU: <strong>QUE</strong>,</span>
                </p>

                 <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <span><strong>QUE</strong>, nada mais tem a declarar.</span>
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br><br>
                </p>

                <!-- Área de assinaturas -->
                <div class="assinatura-area">
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">AUTORIDADE POLICIAL:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">INFRATOR:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">RESPONSÁVEL:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">ESCRIVÃO(Ã):</p>
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
            cpf: @json(isset($dadosArray['cpf']) ? $dadosArray['cpf'] : ''),
            boe: @json(isset($dadosArray['boe']) ? $dadosArray['boe'] : ''),
            data_ext: @json(isset($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO')
        };
    </script>

    <!-- JavaScript principal - CAMINHO ALTERADO -->
    <script src="{{ asset('js/pages/aafai/aafai_autor3.js') }}"></script>
</body>
</html>
