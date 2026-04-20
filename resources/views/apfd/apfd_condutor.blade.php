<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CONDUTOR APFD - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>

<body class="body-declaracao">
    <?php
// ✅ DADOS INJETADOS PELA ROTA (routes/documentos_aafai_apfd.php)
// Não é mais necessário decodificar Base64 aqui dentro do Blade.
if (!isset($dadosArray)) {
    $dadosArray = [];
}
    ?>
<?php
// ✅ CORREÇÃO: FUNÇÃO AUXILIAR PROTEGIDA PARA EVITAR CRASH
if (!function_exists('exibirDado')) {
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

// Formatar lista de nomes de autores (TODOS listados)
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

// ✅ Formatar lista de vítimas (INTELIGENTE: máx 2 nomes + "e outra/outras")
$totalVitimas = count($vitimas);
$vitimasNomes = array_map(function ($v) {
    return $v['nome'] ?? 'NÃO INFORMADO';
}, $vitimas);

if ($totalVitimas === 1) {
    $listaVitimas = $vitimasNomes[0];
    $textoVitima = 'da VÍTIMA';
} elseif ($totalVitimas === 2) {
    $listaVitimas = $vitimasNomes[0] . ' e ' . $vitimasNomes[1];
    $textoVitima = 'das VÍTIMAS';
} elseif ($totalVitimas === 3) {
    $listaVitimas = $vitimasNomes[0] . ', ' . $vitimasNomes[1] . ' e ' . $vitimasNomes[2];
    $textoVitima = 'das VÍTIMAS';
} else {
    // 4 ou mais: duas primeiras + "e outras"
    $listaVitimas = $vitimasNomes[0] . ', ' . $vitimasNomes[1] . ' e outras';
    $textoVitima = 'das VÍTIMAS';
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

// ✅ APFD: Limitar a no máximo 2 testemunhas (as duas primeiras)
$testemunhas = array_slice($testemunhas, 0, 2);

// Formatar lista de nomes de testemunhas
$testemunhasNomes = array_map(function ($t) {
    return $t['nome'] ?? 'NÃO INFORMADO';
}, $testemunhas);
if (count($testemunhasNomes) === 1) {
    $listaTestemunhas = $testemunhasNomes[0];
    $textoTestemunha = 'da TESTEMUNHA';
} else {
    $ultimo = array_pop($testemunhasNomes);
    $listaTestemunhas = implode(', ', $testemunhasNomes) . ' e ' . $ultimo;
    $textoTestemunha = 'das TESTEMUNHAS';
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

        <!-- A barra do TinyMCE assume o lugar agora -->

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
                        <?php echo $textoVitima; ?>: <strong><?php echo $listaVitimas; ?></strong> e <?php echo $textoTestemunha; ?>:
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

        <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
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


        <!-- ✅ DEPENDÊNCIAS DO SISTEMA -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="{{ asset('js/DocumentoService.js') }}"></script>
        
        <!-- ✅ JS ESPECÍFICO PARA APFD CONDUTOR -->
        <script src="{{ asset('js/pages/apfd/apfd_condutor.js') }}?v=<?php echo time(); ?>"></script>

</body>

</html>