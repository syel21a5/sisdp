<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OFÍCIOS APFD - FAMÍLIA - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
                'tipopenal' => $dadosArray['tipopenal'] ?? 'NÃO INFORMADO',
                'fianca' => $dadosArray['fianca'] ?? 'NÃO INFORMADO',
                'fianca_ext' => $dadosArray['fianca_ext'] ?? 'NÃO INFORMADO',
                'fianca_pago' => $dadosArray['fianca_pago'] ?? $dadosArray['FiancaPago'] ?? 'NAO'
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
                Editor de OFÍCIOS APFD - FAMÍLIA
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



        <!-- ÁREA DO EDITOR -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">
                <!-- CONTEÚDO DO TERMO DE INTERROGATÓRIO (ESPECÍFICO) -->
                <p class="preservar-espacamento ql-align-right">{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : 'NÃO INFORMADO' }}</p>

                <p class="preservar-espacamento ql-align-center"><strong>COMUNICAÇÃO A FAMÍLIA OU PESSOA INDICADA PELO PRESO</strong></p>

                <p class="preservar-espacamento"><br></p>
                <p class="preservar-espacamento">Prezado(a) Senhor(a),</p>

                <p class="preservar-espacamento"><br></p>

                <p class="ql-align-justify" style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Em conformidade com o que estabelece o Art. 5º, Inciso LXII, da Constituição Federal, levo ao conhecimento de V.Sª. que, nesta data, nesta Delegacia de Polícia, foi preso(a) e autuado(a) em flagrante delito a pessoa de <strong><?php echo exibirDado($dadosArray, 'autor1', 'nome'); ?></strong>, por infringir as penas do(a) <strong><?php echo exibirDado($dadosArray, 'autor1', 'tipopenal'); ?></strong>, fato ocorrido no dia <strong><?php echo exibirDado($dadosArray, 'data_fato'); ?></strong>, na(o) cidade de <strong><?php echo exibirDado($dadosArray, 'cidade'); ?></strong>/PE, de acordo com o Boletim de Ocorrência de nº <strong><?php echo exibirDado($dadosArray, 'boe'); ?></strong>.
                </p>

                <p class="ql-align-justify" style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php
                // ✅ LÓGICA ESPELHADA DE OFICIOS_APFD_DINAMICO.BLADE.PHP
                $autor = $dadosArray['autor1'] ?? [];
                $fianca = isset($autor['fianca']) ? $autor['fianca'] : 'NÃO INFORMADO';
                $fiancaExt = isset($autor['fianca_ext']) ? $autor['fianca_ext'] : '';

                // Normaliza fiancaPago (Exatamente como no arquivo funcional)
                $fiancaPagoRaw = $autor['fianca_pago'] ?? $autor['FiancaPago'] ?? false;
                $fiancaPago = filter_var($fiancaPagoRaw, FILTER_VALIDATE_BOOLEAN) || $fiancaPagoRaw === 1 || $fiancaPagoRaw === '1';

                // Existe valor de fiança? (Exatamente como no arquivo funcional)
                $temValor = ($fianca && $fianca !== 'NÃO INFORMADO' && floatval(str_replace(['.', ','], ['', '.'], $fianca)) > 0);

                // Depuração (Oculta)
                echo "<!-- DEBUG REF: Fiança: '$fianca', PagaRaw: '" . json_encode($fiancaPagoRaw) . "', PagaBool: " . ($fiancaPago ? 'SIM' : 'NÃO') . ", TemValor: " . ($temValor ? 'SIM' : 'NÃO') . " -->";

                if ($temValor && $fiancaPago) {
                    // PAGOU
                    echo 'É relevante frisar que após adotadas as medidas legais e de praxe, o(a) mesmo(a) foi posto(a) em liberdade após o pagamento da fiança arbitrada, no valor de <strong>' . $fianca . '</strong>' . (!empty($fiancaExt) && $fiancaExt !== 'NÃO INFORMADO' ? ' (' . $fiancaExt . ')' : '') . '.';
                } elseif ($temValor && !$fiancaPago) {
                    // ARBITRADA MAS NÃO PAGOU
                    echo 'É relevante frisar que após adotadas as medidas legais e de praxe, o(a) mesmo(a) foi recolhido ao xadrez desta delegacia de polícia, ficando à disposição da Justiça Pública desta Comarca onde passará por Audiência de Custódia, em virtude de não ter pago a fiança arbitrada, no valor de <strong>' . $fianca . '</strong>' . (!empty($fiancaExt) && $fiancaExt !== 'NÃO INFORMADO' ? ' (' . $fiancaExt . ')' : '') . '.';
                } else {
                    // SEM FIANÇA
                    echo 'É relevante frisar que após adotadas as medidas legais e de praxe, o(a) mesmo(a) foi recolhido ao xadrez desta delegacia de polícia, ficando à disposição da Justiça Pública desta Comarca onde passará por Audiência de Custódia.';
                }
                ?>
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Atenciosamente,
                <br><br><br>
                <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong><br>
                    Delegado(a) de Polícia
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;</p>
                <p style="line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;</p>
                 <p style="line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;</p>

                <div style="margin-top: 2px;">
                    <p class="preservar-espacamento">A Sua Senhoria</p>
                    <p class="preservar-espacamento">O(A) Senhor(a):__________________________________________________</p>
                    <p class="preservar-espacamento">Endereço:_______________________________________________________</p>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- TinyMCE 6 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}"></script>

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
        console.log('🟢 BLADE OFÍCIOS APFD CARREGADO');
        console.log('🟢 Rota:', '{{ request()->path() }}');
        console.log('🟢 Dados carregados:', window.dadosParaImpressao);
    </script>

    <!-- ✅ JS CORRIGIDO PARA APFD -->
    <script src="{{ asset('js/pages/apfd/oficios/oficio_familia_apfd_dinamico.js') }}?v=<?php echo time(); ?>"></script>

</body>
</html>

