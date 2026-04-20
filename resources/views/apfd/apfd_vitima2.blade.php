<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>APFD VÍTIMA 2 - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor do APFD VÍTIMA 2
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
            </d        <!-- ÁREA DO EDITOR -->
        <!-- ÁREA DO EDITOR - ESTRUTURA CORRIGIDA -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">
                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 16pt;">AUTO DE PRISÃO EM FLAGRANTE DELITO</strong><br>
                    <span style="font-size: 14px; font-weight: normal;">(ART. 304 DO CPP)</span>
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <!-- VITIMA 2 - SEM A DIV EXTRA -->
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Na sequência, passou a Autoridade a inquirir a <strong>VÍTIMA</strong>: {{ !empty($dadosArray['nome']) ? $dadosArray['nome'] : 'NÃO INFORMADO' }},
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
                    <strong>ENDEREÇO</strong>: {{ !empty($dadosArray['endereco']) ? $dadosArray['endereco'] : 'NÃO INFORMADO' }}. Sabendo ler e escrever, inquirida pela Autoridade
                    passou a declarar <strong>QUE</strong>: _________________
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Nada mais disse nem lhe foi perguntado. Lido e achado conforme, determinou a Autoridade que fosse encerrado o presente Termo, o
                    qual assina juntamente com o(a) declarante, e comigo Escrivão que digitei.
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br><br>
                </p>

                <!-- Área de assinaturas -->
                <div class="assinatura-area">
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">AUTORIDADE POLICIAL:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">VÍTIMA:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">ESCRIVÃO(Ã):</p>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Dados para JavaScript - ATUALIZADO -->
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
            data_ext: @json(isset($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO')
        };

        // DEBUG: Verificar se os dados estão chegando
        console.log('Dados carregados para APFD Vítima 2:', window.dadosParaImpressao);
    </script>

    <!-- JavaScript principal - ATUALIZADO -->
    <script src="{{ asset('js/pages/apfd/apfd_vitima2.js') }}"></script>
</body>
</html>
