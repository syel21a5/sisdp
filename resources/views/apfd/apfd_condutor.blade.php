<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CONDUTOR APFD - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>

<body class="body-declaracao">
    <?php
// ✅ DECODIFICAR DADOS DA URL PARA APFD CONDUTOR - VERSÃO CORRIGIDA
$dadosBase64 = request()->segment(2); // Pega o segundo segmento da URL
$dadosArray = [];

if ($dadosBase64) {
    try {
        $dadosJson = base64_decode($dadosBase64);
        $dadosArray = json_decode($dadosJson, true) ?? [];

        // ✅ CORREÇÃO: ESTRUTURAR OS DADOS CORRETAMENTE
        if (!empty($dadosArray)) {
            // Se os dados do condutor já vierem estruturados como 'condutor', usar assim
            // Caso contrário, criar a estrutura correta
            if (!isset($dadosArray['condutor']) && isset($dadosArray['nome'])) {
                // Se os dados vierem no formato plano (dados individuais)
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

            // ✅ CORREÇÃO: GARANTIR QUE TODAS AS PESSOAS TENHAM ESTRUTURA VÁLIDA
            $pessoas = ['vitima1', 'vitima2', 'vitima3', 'testemunha1', 'testemunha2', 'testemunha3', 'autor1', 'autor2', 'autor3'];

            foreach ($pessoas as $pessoa) {
                if (!isset($dadosArray[$pessoa]) || !is_array($dadosArray[$pessoa])) {
                    $dadosArray[$pessoa] = ['nome' => strtoupper(str_replace('1', '', $pessoa)) . ' 1'];
                }

                // Garantir que todos os campos existam
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
        // Em caso de erro na decodificação, usar valores padrão
        $dadosArray = [];
    }
}

// ✅ CORREÇÃO: FUNÇÃO AUXILIAR PARA EXIBIR DADOS COM SEGURANÇA
function exibirDado($array, $chave, $subchave = null, $padrao = 'NÃO INFORMADO')
{
    if (!$array || !is_array($array)) {
        return $padrao;
    }

    if ($subchave) {
        return isset($array[$chave][$subchave]) && !empty($array[$chave][$subchave]) ? $array[$chave][$subchave] : $padrao;
    } else {
        return isset($array[$chave]) && !empty($array[$chave]) ? $array[$chave] : $padrao;
    }
}

// ✅ LÓGICA DINÂMICA PARA AUTORES (CHIPS)
$autores = [];
if (isset($dadosArray['autores'])) {
    $rawAutores = $dadosArray['autores'];
    if (is_string($rawAutores)) {
        $decoded = json_decode($rawAutores, true);
        if (is_array($decoded)) {
            $autores = $decoded;
        }
    } elseif (is_array($rawAutores)) {
        $autores = $rawAutores;
    }
}

// Filtra nomes inválidos/placeholders
$autores = array_values(array_filter($autores, function ($a) {
    $n = strtoupper(trim($a['nome'] ?? ''));
    return $n !== '' && $n !== 'NÃO INFORMADO' && strpos($n, 'AUTOR') === false;
}));

// Fallback: Tenta coletar autor1..autor5 se lista dinâmica estiver vazia
if (empty($autores)) {
    for ($i = 1; $i <= 5; $i++) {
        if (
            isset($dadosArray["autor$i"]) &&
            !empty($dadosArray["autor$i"]['nome']) &&
            strtoupper($dadosArray["autor$i"]['nome']) !== 'NÃO INFORMADO' &&
            strpos(strtoupper($dadosArray["autor$i"]['nome']), 'AUTOR') === false
        ) {
            $autores[] = $dadosArray["autor$i"];
        }
    }
}

// Se não encontrou autores válidos, usa autor1 como padrão
if (empty($autores)) {
    $autores[] = $dadosArray['autor1'] ?? ['nome' => 'NÃO INFORMADO'];
}

// Formatar lista de nomes de autores
$autoresNomes = array_map(function ($a) {
    return $a['nome'] ?? 'NÃO INFORMADO';
}, $autores);
if (count($autoresNomes) === 1) {
    $listaAutores = $autoresNomes[0];
} else {
    $ultimo = array_pop($autoresNomes);
    $listaAutores = implode(', ', $autoresNomes) . ' e ' . $ultimo;
}

// ✅ LÓGICA DINÂMICA PARA VÍTIMAS (CHIPS)
$vitimas = [];
if (isset($dadosArray['vitimas'])) {
    $rawVitimas = $dadosArray['vitimas'];
    if (is_string($rawVitimas)) {
        $decoded = json_decode($rawVitimas, true);
        if (is_array($decoded)) {
            $vitimas = $decoded;
        }
    } elseif (is_array($rawVitimas)) {
        $vitimas = $rawVitimas;
    }
}

// Filtra nomes inválidos
$vitimas = array_values(array_filter($vitimas, function ($v) {
    $n = strtoupper(trim($v['nome'] ?? ''));
    return $n !== '' && $n !== 'NÃO INFORMADO' && strpos($n, 'VITIMA') === false;
}));

// Fallback: vitima1..vitima3
if (empty($vitimas)) {
    for ($i = 1; $i <= 3; $i++) {
        if (
            isset($dadosArray["vitima$i"]) &&
            !empty($dadosArray["vitima$i"]['nome']) &&
            strtoupper($dadosArray["vitima$i"]['nome']) !== 'NÃO INFORMADO' &&
            strpos(strtoupper($dadosArray["vitima$i"]['nome']), 'VITIMA') === false
        ) {
            $vitimas[] = $dadosArray["vitima$i"];
        }
    }
}

if (empty($vitimas)) {
    $vitimas[] = $dadosArray['vitima1'] ?? ['nome' => 'NÃO INFORMADO'];
}

// Formatar lista de nomes de vítimas
$vitimasNomes = array_map(function ($v) {
    return $v['nome'] ?? 'NÃO INFORMADO';
}, $vitimas);
if (count($vitimasNomes) === 1) {
    $listaVitimas = $vitimasNomes[0];
} else {
    $ultimo = array_pop($vitimasNomes);
    $listaVitimas = implode(', ', $vitimasNomes) . ' e ' . $ultimo;
}

// ✅ LÓGICA DINÂMICA PARA TESTEMUNHAS (CHIPS)
$testemunhas = [];
if (isset($dadosArray['testemunhas'])) {
    $rawTestemunhas = $dadosArray['testemunhas'];
    if (is_string($rawTestemunhas)) {
        $decoded = json_decode($rawTestemunhas, true);
        if (is_array($decoded)) {
            $testemunhas = $decoded;
        }
    } elseif (is_array($rawTestemunhas)) {
        $testemunhas = $rawTestemunhas;
    }
}

// Filtra nomes inválidos
$testemunhas = array_values(array_filter($testemunhas, function ($t) {
    $n = strtoupper(trim($t['nome'] ?? ''));
    return $n !== '' && $n !== 'NÃO INFORMADO' && strpos($n, 'TESTEMUNHA') === false;
}));

// Fallback: testemunha1..testemunha3
if (empty($testemunhas)) {
    for ($i = 1; $i <= 3; $i++) {
        if (
            isset($dadosArray["testemunha$i"]) &&
            !empty($dadosArray["testemunha$i"]['nome']) &&
            strtoupper($dadosArray["testemunha$i"]['nome']) !== 'NÃO INFORMADO' &&
            strpos(strtoupper($dadosArray["testemunha$i"]['nome']), 'TESTEMUNHA') === false
        ) {
            $testemunhas[] = $dadosArray["testemunha$i"];
        }
    }
}

if (empty($testemunhas)) {
    $testemunhas[] = $dadosArray['testemunha1'] ?? ['nome' => 'NÃO INFORMADO'];
}

// Formatar lista de nomes de testemunhas
$testemunhasNomes = array_map(function ($t) {
    return $t['nome'] ?? 'NÃO INFORMADO';
}, $testemunhas);
if (count($testemunhasNomes) === 1) {
    $listaTestemunhas = $testemunhasNomes[0];
} else {
    $ultimo = array_pop($testemunhasNomes);
    $listaTestemunhas = implode(', ', $testemunhasNomes) . ' e ' . $ultimo;
}
    ?>

    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor de DEPOIMENTO | CONDUTOR APFD
            </h1>
        </div>

        <!-- CABEÇALHO DO DOCUMENTO -->
        <div class="document-container">
            <div class="document-header">
                <div class="header-content">
                    <img src="{{ asset('images/b_PE.jpg') }}" alt="Brasão de Pernambuco">
                    <div class="header-text">
                        <div class="orgao-principal">POLÍCIA CIVIL DE PERNAMBUCO - PCPE</div>
                        <div class="orgao-secundario">Diretoria Integrada do Interior - 2 da Policia Civil – DINTER - 2
                        </div>
                        <div class="orgao-secundario">Gerência de Controle Operacional do Interior - 2 – GCOI - 2</div>
                        <div class="orgao-secundario">20ª Delegacia Seccional de Polícia – Afogados da Ingazeira – 20ª
                            DESEC</div>
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

                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 16pt;">AUTO DE PRISÃO EM FLAGRANTE DELITO</strong><br>
                    <span style="font-size: 14px; font-weight: normal;">(ART. 304 DO CPP)</span>
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <!-- CONDUTOR -->
                <div style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: left;">
                    <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                        Aos
                        <strong>{{ !empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO' }}</strong>,
                        nesta Cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }},
                        Estado de Pernambuco, no Cartório desta Delegacia de Polícia, onde presente se encontrava
                        o (a) Bel. (a)
                        <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>,
                        respectivo (a) Delegado (a), comigo, Escrivão(ã) de seu cargo, ao final assinado, aí compareceu
                        na qualidade
                        de <strong>CONDUTOR</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'nome'); ?>,
                        <strong>ALCUNHA</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'alcunha'); ?>,
                        <strong>NASCIMENTO</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'nascimento'); ?>,
                        <strong>IDADE</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'idade'); ?>,
                        <strong>ESTADO CIVIL</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'estcivil'); ?>,
                        <strong>NATURALIDADE</strong>:
                        <?php echo exibirDado($dadosArray, 'condutor', 'naturalidade'); ?>,
                        <strong>RG</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'rg'); ?>,
                        <strong>CPF</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'cpf'); ?>,
                        <strong>PROFISSÃO</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'profissao'); ?>,
                        <strong>INSTRUÇÃO</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'instrucao'); ?>,
                        <strong>TELEFONE</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'telefone'); ?>,
                        <strong>MÃE</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'mae'); ?>,
                        <strong>PAI</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'pai'); ?>,
                        <strong>ENDEREÇO</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'endereco'); ?>. Aos
                        costumes,
                        disse ________. Compromissado(a) na forma da lei, advertido(a) das penas cominadas ao falso
                        testemunho, prometeu dizer a verdade do que
                        soubesse e lhe fosse perguntado. Inquirido(a), respondeu: QUE apresenta preso em flagrante
                        delito: <strong><?php echo $listaAutores; ?></strong>, na presença
                        da(s) VÍTIMA(S) <strong><?php echo $listaVitimas; ?></strong> e das TESTEMUNHAS:
                        <strong><?php echo $listaTestemunhas; ?></strong>, pelos motivos que, a seguir, passa a
                        expor: QUE
                    </p>

                    <p class="ql-align-justify preservar-espacamento">
                        <br>
                    </p>

                    Nada mais disse nem lhe foi perguntado, determinou a Autoridade Policial encerrar este Termo,
                    que, após ser lido e achado conforme, o
                    qual assina juntamente com o Condutor e comigo Escrivão que digitei.
                    </p>

                    <p class="ql-align-justify preservar-espacamento">
                        <br><br>
                    </p>

                    <!-- Área de assinaturas -->
                    <div class="assinatura-area">
                        <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">AUTORIDADE POLICIAL:</p>
                        <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                        <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">CONDUTOR:</p>
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
                ip: <?php echo json_encode(exibirDado($dadosArray, 'ip')); ?>,

                // ✅ DADOS DAS PESSOAS - CORRIGIDO
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

                // ✅ LISTAS DINÂMICAS DE CHIPS
                autores: <?php echo json_encode($autores); ?>,
                vitimas: <?php echo json_encode($vitimas); ?>,
                testemunhas: <?php echo json_encode($testemunhas); ?>
            };
        </script>

        <script>
            console.log('🟢 BLADE APFD CONDUTOR CARREGADO');
            console.log('🟢 Rota:', '{{ request()->path() }}');
            console.log('🟢 Deveria carregar JS:', '{{ asset('js/pages/apfd/apfd_condutor.js') }}');
        </script>


        <!-- ✅ JS ESPECÍFICO PARA APFD CONDUTOR -->
        <script src="{{ asset('js/pages/apfd/apfd_condutor.js') }}?v=<?php echo time(); ?>"></script>

</body>

</html>