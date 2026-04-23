<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OFÍCIOS APFD - 1 AUTOR - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    
</head>
<body class="body-declaracao">
    <?php
    // ✅ DECODIFICAR DADOS DA URL PARA OFÍCIOS APFD 1 AUTOR
    $dadosBase64 = request()->segment(2);
    $dadosArray = [];

    if ($dadosBase64) {
        try {
            $dadosJson = base64_decode($dadosBase64);
            $dadosArray = json_decode($dadosJson, true) ?? [];

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

                // ✅ GARANTIR QUE TODAS AS PESSOAS TENHAM ESTRUTURA VÁLIDA
                $pessoas = ['vitima1', 'vitima2', 'vitima3', 'testemunha1', 'testemunha2', 'testemunha3', 'autor1', 'autor2', 'autor3'];

                foreach ($pessoas as $pessoa) {
                    if (!isset($dadosArray[$pessoa]) || !is_array($dadosArray[$pessoa])) {
                        $dadosArray[$pessoa] = ['nome' => strtoupper(str_replace('1', '', $pessoa)) . ' 1'];
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
        } catch (Exception $e) {
            $dadosArray = [];
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
    <?php $tipoDoc = strtolower(request()->query('tipo', 'culpa')); ?>
    <?php
        $autorKey = 'autor1';
        if (!empty($dadosArray['autor1']) && is_array($dadosArray['autor1'])) {
            $autorKey = 'autor1';
        } elseif (!empty($dadosArray['autor']) && is_array($dadosArray['autor'])) {
            $autorKey = 'autor';
        } elseif (!empty($dadosArray['autor2']) && is_array($dadosArray['autor2'])) {
            $autorKey = 'autor2';
        } elseif (!empty($dadosArray['autor3']) && is_array($dadosArray['autor3'])) {
            $autorKey = 'autor3';
        }
    ?>

    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                <?php echo ($tipoDoc === 'ciencia')
                    ? 'Editor de NOTA DE CIÊNCIA - GARANTIAS CONSTITUCIONAIS | APFD - 1 AUTOR'
                    : 'Editor de NOTA DE CULPA | APFD - 1 AUTOR'; ?>
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
                    <img src="{{ asset('images/b_PCPE.png') }}" alt="Brasão da Polícia Civil"> </button>
                </div>
            </div>
        </div>

<!-- ÁREA DO EDITOR -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">
                <?php if ($tipoDoc === 'ciencia') { ?>
                    <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;"><strong style="font-size: 20pt;">NOTA DE CIÊNCIA DAS GARANTIAS CONSTITUCIONAIS</strong></p>
                    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;"><strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong> – Delegado(a) de Polícia, no uso de suas atribuições legais, etc...</p>
                    <p class="preservar-espacamento"><br></p>
                    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">FAZ SABER a <strong><?php echo exibirDado($dadosArray, $autorKey, 'nome'); ?></strong>, preso(a) e autuado(a) em Flagrante Delito na Delegacia de Polícia da <strong>{{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADO' }}</strong> - <strong>{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}</strong>, como incurso nas penas do(s) artigo(s) <strong><?php echo exibirDado($dadosArray, $autorKey, 'tipopenal'); ?></strong>, que lhes são assegurados, dentre outros, o direito de:</p>
                    <p class="preservar-espacamento"><br></p>
                    <p>1 – Ter respeitada a sua integridade física;</p>
                    <p>2 – Permanecer calado, sendo-lhe assegurada a assistência da família e de advogado;</p>
                    <p>3 – Comunicar a prisão à família ou à pessoa por ele indicada;</p>
                    <p>4 – Identificar os responsáveis por sua prisão ou por seu interrogatório policial.</p>
                    <p class="preservar-espacamento"><br></p>
                    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">Dada e lavrada nesta cidade de <strong>{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}</strong>, Estado de Pernambuco e na Delegacia de Polícia da <strong>{{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADO' }}</strong>, em <strong>{{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : 'NÃO INFORMADO' }}</strong>. Eu, <strong>{{ !empty($dadosArray['escrivao']) ? $dadosArray['escrivao'] : 'NÃO INFORMADO' }}</strong>, Escrivão que a digitei.</p>
                    <p class="preservar-espacamento"><br><br></p>
                    <p style="text-align: center; line-height: 1.6; margin: 1em 0; padding: 0;"><strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong><br>Delegado(a) de Polícia</p>
                    <p class="preservar-espacamento"><br><br><br><br><br></p>
                    <p style="text-align: center; line-height: 1.6; margin: 1em 0; padding: 0;">Eu, <strong><?php echo exibirDado($dadosArray, $autorKey, 'nome'); ?></strong>, RECEBI o original da presente, pelo que firmo este recibo.</p>
                <?php } else { ?>
                    <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;"><strong>NOTA DE CULPA</strong></p>
                    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;O(A) Bel(a). <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, dando cumprimento ao disposto no art. 306 do Código de Processo Penal pátrio em vigor,</p>
                    <p class="preservar-espacamento"><br></p>
                    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; Faz saber a <strong><?php echo exibirDado($dadosArray, $autorKey, 'nome'); ?></strong>, já devidamente qualificado(a) nos autos; que nesta cidade e na data de hoje, foi preso(a) e autuado(a) em Flagrante Delito, por ter praticado o crime incurso nas penas do(s) <strong><?php echo exibirDado($dadosArray, $autorKey, 'tipopenal'); ?></strong>.</p>
                    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Tendo sido lavrado o respectivo auto de prisão em flagrante delito, no qual depuseram como CONDUTOR: <strong><?php echo exibirDado($dadosArray, 'condutor', 'nome'); ?></strong> e TESTEMUNHA(S): <strong><?php echo exibirDado($dadosArray, 'testemunha1', 'nome'); ?></strong> e <strong><?php echo exibirDado($dadosArray, 'testemunha2', 'nome'); ?></strong>.</p>
                    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;E para sua ciência, mandou dar-lhe a presente NOTA DE CULPA. Eu, .......... <strong>{{ !empty($dadosArray['escrivao']) ? $dadosArray['escrivao'] : 'NÃO INFORMADO' }}</strong>, escrivão de Polícia, digitei e subscrevo.</p>
                    <p class="preservar-espacamento"><br></p>
                    <p class="preservar-espacamento ql-align-right">Afogados da Ingazeira, {{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : 'NÃO INFORMADO' }}.</p>
                    <p class="preservar-espacamento"><br><br></p>
                    <p style="text-align: center; line-height: 1.6; margin: 1em 0; padding: 0;"><strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong><br>Delegado de Policia</p>
                    <p class="preservar-espacamento"><br><br><br><br><br></p>
                    <p style="text-align: center; line-height: 1.6; margin: 1em 0; padding: 0;">Eu, <strong><?php echo exibirDado($dadosArray, $autorKey, 'nome'); ?></strong>, RECEBI o original da presente, pelo que firmo este recibo.</p>
                <?php } ?>
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
        autor3: <?php echo json_encode(isset($dadosArray['autor3']) ? $dadosArray['autor3'] : []); ?>
    };
    </script>

    <script>
        console.log('🟢 BLADE OFÍCIOS APFD 1 AUTOR CARREGADO');
        console.log('🟢 Rota:', '{{ request()->path() }}');
    </script>

    <!-- ✅ MESMO JS DO APFD CONDUTOR ORIGINAL QUE FUNCIONA -->
    <script src="{{ asset('js/pages/apfd/pecas/notadeculpa_apfd_1_autor.js') }}?v=<?php echo time(); ?>"></script>

</body>
</html>

