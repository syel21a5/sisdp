<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ROL DE TESTEMUNHAS - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- FontAwesome atualizado para garantir ícones -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <?php
    // ✅ CORREÇÃO: Usar $dadosArray (singular) que vem do controller
    $dadosArray = $dadosArray ?? []; // CORRIGIDO: dadosArray → dadosArray

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
        if (!isset($dadosArray['condutor']) && isset($dadosArray['nome'])) {
            $dadosArray['condutor'] = [
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
                'endereco' => $dadosArray['endereco'] ?? 'NÃO INFORMADO'
            ];
        }

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

        // ✅ ESTRUTURAR DADOS DA VITIMA1 SE CHEGAREM DE FORMA PLANA
        if (!isset($dadosArray['vitima1']) && isset($dadosArray['vitima'])) {
            $dadosArray['vitima1'] = [
                'nome' => $dadosArray['vitima'] ?? 'NÃO INFORMADO',
                'alcunha' => $dadosArray['vitima_alcunha'] ?? 'NÃO INFORMADO',
                'nascimento' => $dadosArray['vitima_nascimento'] ?? 'NÃO INFORMADO',
                'idade' => $dadosArray['vitima_idade'] ?? 'NÃO INFORMADO',
                'estcivil' => $dadosArray['vitima_estcivil'] ?? 'NÃO INFORMADO',
                'naturalidade' => $dadosArray['vitima_naturalidade'] ?? 'NÃO INFORMADO',
                'rg' => $dadosArray['vitima_rg'] ?? 'NÃO INFORMADO',
                'cpf' => $dadosArray['vitima_cpf'] ?? 'NÃO INFORMADO',
                'profissao' => $dadosArray['vitima_profissao'] ?? 'NÃO INFORMADO',
                'instrucao' => $dadosArray['vitima_instrucao'] ?? 'NÃO INFORMADO',
                'telefone' => $dadosArray['vitima_telefone'] ?? 'NÃO INFORMADO',
                'mae' => $dadosArray['vitima_mae'] ?? 'NÃO INFORMADO',
                'pai' => $dadosArray['vitima_pai'] ?? 'NÃO INFORMADO',
                'endereco' => $dadosArray['vitima_endereco'] ?? 'NÃO INFORMADO'
            ];
        }

        // ✅ ESTRUTURAR DADOS GERAIS (DELEGACIA, TIPIFICAÇÃO, ETC.)
        if (!isset($dadosArray['delegacia'])) {
            $dadosArray['delegacia'] = $dadosArray['delegacia'] ?? $dadosArray['delegacia_nome'] ?? 'NÃO INFORMADO';
        }

        if (!isset($dadosArray['tipificacao'])) {
            $dadosArray['tipificacao'] = $dadosArray['tipificacao'] ?? $dadosArray['tipificacao_penal'] ?? 'NÃO INFORMADO';
        }

        if (!isset($dadosArray['data_fato'])) {
            $dadosArray['data_fato'] = $dadosArray['data_fato'] ?? $dadosArray['data_do_fato'] ?? 'NÃO INFORMADO';
        }

        if (!isset($dadosArray['hora_fato'])) {
            $dadosArray['hora_fato'] = $dadosArray['hora_fato'] ?? $dadosArray['hora_do_fato'] ?? 'NÃO INFORMADO';
        }

        if (!isset($dadosArray['local_fato'])) {
            $dadosArray['local_fato'] = $dadosArray['local_fato'] ?? $dadosArray['local_do_fato'] ?? 'NÃO INFORMADO';
        }

        if (!isset($dadosArray['relato'])) {
            $dadosArray['relato'] = $dadosArray['relato'] ?? $dadosArray['relato_fato'] ?? 'NÃO INFORMADO';
        }

        // ✅ GARANTIR QUE TODAS AS PESSOAS TENHAM ESTRUTURA VÁLIDA
        $pessoas = ['vitima1', 'vitima2', 'vitima3', 'testemunha1', 'testemunha2', 'testemunha3', 'autor1', 'autor2', 'autor3'];

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
                Editor de ROL DE TESTEMUNHAS
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
                <p class="ql-align-center" style="background-color: #e0e0e0; padding: 5px; font-weight: bold; border: 1px solid #000;">ROL DE TESTEMUNHAS / INFORMANTES</p>
                <p class="preservar-espacamento"><br></p>
                
                <p class="preservar-espacamento"><strong>Tombamento: nº <?php echo exibirDado($dadosArray, 'ip'); ?></strong></p>
                <p class="preservar-espacamento"><br></p>

                <?php 
                // ✅ LOGIC DINÂMICA: Encontrar todas as chaves que começam com 'testemunha'
                $chavesPessoas = [];
                if (isset($dadosArray) && is_array($dadosArray)) {
                    foreach (array_keys($dadosArray) as $key) {
                        if (preg_match('/^testemunha\d+$/', $key)) {
                            $chavesPessoas[] = $key;
                        }
                    }
                }
                
                // Ordenar chaves para garantir ordem correta (testemunha1, testemunha2...)
                natsort($chavesPessoas);

                $contador = 0;

                foreach ($chavesPessoas as $chave) {
                    $dadosPessoa = isset($dadosArray[$chave]) ? $dadosArray[$chave] : [];
                    $nome = isset($dadosPessoa['nome']) ? $dadosPessoa['nome'] : '';
                    
                    // Só exibe se tiver nome e não for "NÃO INFORMADO" (limpeza básica)
                    if ($nome && $nome !== 'NÃO INFORMADO' && $nome !== 'undefined') {
                        $contador++;
                        ?>
                        <p class="ql-align-justify" style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                            <strong>NOME:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'nome'), 'UTF-8'); ?>, 
                            <strong>ALCUNHA:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'alcunha'), 'UTF-8'); ?>, 
                            <strong>NASCIMENTO:</strong> <?php echo exibirDado($dadosArray, $chave, 'nascimento'); ?>, 
                            <strong>IDADE:</strong> <?php echo exibirDado($dadosArray, $chave, 'idade'); ?>, 
                            <strong>ESTADO CIVIL:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'estcivil'), 'UTF-8'); ?>, 
                            <strong>NATURALIDADE:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'naturalidade'), 'UTF-8'); ?>, 
                            <strong>RG:</strong> <?php echo exibirDado($dadosArray, $chave, 'rg'); ?>, 
                            <strong>CPF:</strong> <?php echo exibirDado($dadosArray, $chave, 'cpf'); ?>, 
                            <strong>PROFISSÃO:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'profissao'), 'UTF-8'); ?>, 
                            <strong>INSTRUÇÃO:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'instrucao'), 'UTF-8'); ?>, 
                            <strong>TELEFONE:</strong> <?php echo exibirDado($dadosArray, $chave, 'telefone'); ?>, 
                            <strong>MÃE:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'mae'), 'UTF-8'); ?>, 
                            <strong>PAI:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'pai'), 'UTF-8'); ?>, 
                            <strong>ENDEREÇO:</strong> <?php echo mb_strtoupper(exibirDado($dadosArray, $chave, 'endereco'), 'UTF-8'); ?>;
                        </p>
                        <p class="preservar-espacamento"><br></p>
                        <?php
                    }
                }

                if ($contador === 0) {
                    echo '<p class="preservar-espacamento"><em>Nenhuma testemunha selecionada para este procedimento.</em></p>';
                }
                ?>

                <p class="preservar-espacamento"><br></p>
                <p class="preservar-espacamento"><br></p>

                <p class="ql-align-center" style="text-align: center;"><strong><?php echo exibirDado($dadosArray, 'cidade'); ?>, <?php echo exibirDado($dadosArray, 'data_comp'); ?>.</strong></p>
                <p class="preservar-espacamento"><br></p>
                <p class="preservar-espacamento"><br></p>
                
                <p class="ql-align-center" style="text-align: center;"><strong><?php echo exibirDado($dadosArray, 'escrivao'); ?></strong></p>
                <p class="ql-align-center" style="text-align: center;">Escrivão(ã) de Polícia</p>
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
        autor3: <?php echo json_encode(isset($dadosArray['autor3']) ? $dadosArray['autor3'] : []); ?>,

        // ✅ NÚMEROS DE OFÍCIO
        numero_oficio_juiz: <?php echo json_encode($numeroOficioJuiz ?? 'NÃO GERADO'); ?>,
        numero_oficio_promotor: <?php echo json_encode($numeroOficioPromotor ?? 'NÃO GERADO'); ?>,
        numero_oficio_defensor: <?php echo json_encode($numeroOficioDefensor ?? 'NÃO GERADO'); ?>
    };
    </script>

    <script>
        console.log('🟢 BLADE OFÍCIOS MP CARREGADO');
        console.log('🟢 Rota:', '{{ request()->path() }}');
        console.log('🟢 Dados carregados:', window.dadosParaImpressao);
    </script>

    <!-- ✅ JS CORRIGIDO PARA MP -->
    <script src="{{ asset('js/pages/pecas/RoldeTestemunhas.js') }}?v=<?php echo time(); ?>"></script>

</body>
</html>
