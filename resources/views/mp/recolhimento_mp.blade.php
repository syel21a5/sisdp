<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OFÍCIO RECOLHIMENTO MP - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <?php
    // ✅ CORREÇÃO: Usar $dadosArray que vem do controller
    $dadosArray = $dadosArray ?? [];

    // Se não houver dados do controller, tenta decodificar da URL
    if (empty($dadosArray)) {
        $dadosBase64 = request()->segment(2);
        if ($dadosBase64) {
            try {
                $dadosJson = base64_decode($dadosBase64);
                $dadosArray = json_decode($dadosJson, true) ?? [];
            } catch (Exception $e) {
                $dadosArray = [];
            }
        }
    }

    // ✅ ESTRUTURAR OS DADOS CORRETAMENTE
    if (!empty($dadosArray)) {
        // ✅ ESTRUTURAR DADOS DO AUTOR1 SE CHEGAREM DE FORMA PLANA
        if (!isset($dadosArray['autor1']) && isset($dadosArray['nome'])) {
            $dadosArray['autor1'] = [
                'nome' => $dadosArray['nome'] ?? 'NÃO INFORMADO',
                'alcunha' => $dadosArray['alcunha'] ?? 'NÃO INFORMADO',
                'nascimento' => $dadosArray['nascimento'] ?? 'NÃO INFORMADO',
                'idade' => $dadosArray['idade'] ?? 'NÃO INFORMADO',
                'estcivil' => $dadosArray['estcivil'] ?? 'NÃO INFORMADO',
                'naturalidade' => $dadosArray['naturalidade'] ?? 'NÃO INFORMADO',
                'rg' => $dadosArray['rg'] ?? 'NÃO INFORMADO',
                'cpf' => $dadosArray['cpf'] ?? 'NÃO INFORMADO',
                'profissao' => $dadosArray['profissao'] ?? 'NÃO INFORMADO',
                'instrucao' => $dadosArray['instrucao'] ?? 'NÃO INFORMADO',
                'telefone' => $dadosArray['telefone'] ?? 'NÃO INFORMADO',
                'mae' => $dadosArray['mae'] ?? 'NÃO INFORMADO',
                'pai' => $dadosArray['pai'] ?? 'NÃO INFORMADO',
                'endereco' => $dadosArray['endereco'] ?? 'NÃO INFORMADO',
                'tipopenal' => $dadosArray['tipopenal'] ?? 'NÃO INFORMADO'
            ];
        }

        // ✅ GARANTIR QUE TODAS AS PESSOAS TENHAM ESTRUTURA VÁLIDA
        $pessoas = ['condutor', 'vitima1', 'vitima2', 'vitima3', 'testemunha1', 'testemunha2', 'testemunha3', 'autor1', 'autor2', 'autor3'];

        foreach ($pessoas as $pessoa) {
            if (!isset($dadosArray[$pessoa]) || !is_array($dadosArray[$pessoa])) {
                $dadosArray[$pessoa] = ['nome' => 'NÃO INFORMADO'];
            }

            $dadosArray[$pessoa] = array_merge([
                'nome' => 'NÃO INFORMADO',
                'alcunha' => 'NÃO INFORMADO',
                'nascimento' => 'NÃO INFORMADO',
                'idade' => 'NÃO INFORMADO',
                'estcivil' => 'NÃO INFORMADO',
                'naturalidade' => 'NÃO INFORMADO',
                'rg' => 'NÃO INFORMADO',
                'cpf' => 'NÃO INFORMADO',
                'profissao' => 'NÃO INFORMADO',
                'instrucao' => 'NÃO INFORMADO',
                'telefone' => 'NÃO INFORMADO',
                'mae' => 'NÃO INFORMADO',
                'pai' => 'NÃO INFORMADO',
                'endereco' => 'NÃO INFORMADO'
            ], $dadosArray[$pessoa]);
        }
    }

    // ✅ FUNÇÃO AUXILIAR PARA EXIBIR DADOS COM SEGURANÇA
    function exibirDado($array, $chave, $subchave = null, $padrao = 'NÃO INFORMADO') {
        if (!$array || !is_array($array)) {
            return $padrao;
        }

        if ($subchave) {
            return isset($array[$chave][$subchave]) && !empty($array[$chave][$subchave]) ? $array[$chave][$subchave] : $padrao;
        } else {
            return isset($array[$chave]) && !empty($array[$chave]) ? $array[$chave] : $padrao;
        }
    }
    ?>

    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor de OFÍCIO RECOLHIMENTO MP
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
                            <?php echo exibirDado($dadosArray, 'delegacia'); ?> –
                            <?php echo exibirDado($dadosArray, 'cidade'); ?>
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
                <!-- CONTEÚDO DO OFÍCIO RECOLHIMENTO -->
                <p class="preservar-espacamento ql-align-right"><?php echo exibirDado($dadosArray, 'cidade'); ?>, <?php echo exibirDado($dadosArray, 'data_comp'); ?></p>
                <p class="preservar-espacamento"><br></p>
                <p class="preservar-espacamento">Ofício nº <strong>{{ $numeroOficio ?? 'NÃO GERADO' }}</strong></p>
                <p class="preservar-espacamento">Referência – <strong>MANDADO DE PRISÃO</strong></p>
                <p class="preservar-espacamento">Assunto: <strong>ENCAMINHAMENTO DE PRESO</strong></p>
                <p class="preservar-espacamento"><br></p>                
                <p class="preservar-espacamento"><strong>Senhor(a) carcereiro(a) do Presídio (Cadeia Pública), ou quem suas vezes fizer.</strong></p>
                <p class="preservar-espacamento"><br></p>
                <p class="ql-align-justify" style="text-indent: 5em; text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">Encaminho a V.Sª., para que seja recolhido(a) nesse estabelecimento prisional, a pessoa de <strong><?php echo exibirDado($dadosArray, 'autor1', 'nome'); ?></strong>, em razão do cumprimento de MANDADO DE PRISÃO: <strong><?php echo exibirDado($dadosArray, 'nmandado'); ?></strong>, expedido pelo(a) senhor(a) Juiz(a) de Direito desta Comarca, de acordo com o Boletim de Ocorrência de nº <strong><?php echo exibirDado($dadosArray, 'boe'); ?></strong>. E que se cumpra na forma e sob as penas da Lei. Eu, _____________, Escrivão, que o digitei.</p>
                <p class="preservar-espacamento">Em anexo, seguem cópias do Auto de Exame Traumatológico.</p>               
                <p class="preservar-espacamento"><br></p>
                <p class="preservar-espacamento">Atenciosamente,</p>               
                <p class="preservar-espacamento"><br><br></p>
                <p class="preservar-espacamento ql-align-center"><strong><?php echo exibirDado($dadosArray, 'delegado'); ?></strong></p>
                <p class="preservar-espacamento ql-align-center">Delegado(a) de Polícia</p>
                <p class="preservar-espacamento"><br></p>
                <p class="preservar-espacamento"><br><br><br><br></p>                                
                <div style="margin-top: 2px;">
                    <p class="preservar-espacamento">ASSINATURA E MATRICULA: ______________________________________</p>
                    <p class="preservar-espacamento">DATA:_____/____/______</p>
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

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = {
            delegacia: <?php echo json_encode(exibirDado($dadosArray, 'delegacia')); ?>,
            cidade: <?php echo json_encode(exibirDado($dadosArray, 'cidade')); ?>,
            delegado: <?php echo json_encode(exibirDado($dadosArray, 'delegado')); ?>,
            escrivao: <?php echo json_encode(exibirDado($dadosArray, 'escrivao')); ?>,
            boe: <?php echo json_encode(exibirDado($dadosArray, 'boe')); ?>,
            data_ext: <?php echo json_encode(exibirDado($dadosArray, 'data_ext')); ?>,
            data_comp: <?php echo json_encode(exibirDado($dadosArray, 'data_comp')); ?>,
            nmandado: <?php echo json_encode(exibirDado($dadosArray, 'nmandado')); ?>,
            datamandado: <?php echo json_encode(exibirDado($dadosArray, 'datamandado')); ?>,
            ip: <?php echo json_encode(exibirDado($dadosArray, 'ip')); ?>,
            numero_oficio: <?php echo json_encode($numeroOficio ?? 'NÃO GERADO'); ?>,

            // ✅ DADOS DAS PESSOAS
            condutor: <?php echo json_encode(isset($dadosArray['condutor']) ? $dadosArray['condutor'] : []); ?>,
            vitima1: <?php echo json_encode(isset($dadosArray['vitima1']) ? $dadosArray['vitima1'] : []); ?>,
            vitima2: <?php echo json_encode(isset($dadosArray['vitima2']) ? $dadosArray['vitima2'] : []); ?>,
            vitima3: <?php echo json_encode(isset($dadosArray['vitima3']) ? $dadosArray['vitima3'] : []); ?>,
            testemunha1: <?php echo json_encode(isset($dadosArray['testemunha1']) ? $dadosArray['testemunha1'] : []); ?>,
            testemunha2: <?php echo json_encode(isset($dadosArray['testemunha2']) ? $dadosArray['testemunha2'] : []); ?>,
            testemunha3: <?php echo json_encode(isset($dadosArray['testemunha3']) ? $dadosArray['testemunha3'] : []); ?>,
            autor1: <?php echo json_encode(isset($dadosArray['autor1']) ? $dadosArray['autor1'] : []); ?>,
            autor2: <?php echo json_encode(isset($dadosArray['autor2']) ? $dadosArray['autor2'] : []); ?>,
            autor3: <?php echo json_encode(isset($dadosArray['autor3']) ? $dadosArray['autor3'] : []); ?>
        };
    </script>

    <script>
        console.log('🟢 BLADE RECOLHIMENTO MP CARREGADO');
        console.log('🟢 Rota:', '{{ request()->path() }}');
        console.log('🟢 Dados carregados:', window.dadosParaImpressao);
    </script>

    <!-- ✅ JS EXISTENTE (usa o mesmo) -->
    <script src="{{ asset('js/pages/mp/recolhimento_mp.js') }}?v=<?php echo time(); ?>"></script>

</body>
</html>
