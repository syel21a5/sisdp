<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AAFAI Condutor - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    
</head>

<body class="body-declaracao">
    <?php
// ✅ DECODIFICAR DADOS DA URL PARA AAFAI CONDUTOR - VERSÃO CORRIGIDA
$dadosBase64 = request()->segment(2); // Pega o segundo segmento da URL
 if (!isset($dadosArray)) { $dadosArray = []; }

if ($dadosBase64 && empty($dadosArray)) {
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
    } catch (\Exception $e) {
        $dadosArray = [];
    }
}
// ✅ DADOS INJETADOS PELA ROTA (routes/documentos_aafai_apfd.php)
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
$autores = array_values(array_filter($autores, function ($a) {
    $n = strtoupper(trim($a['nome'] ?? ''));
    return $n !== '' && $n !== 'NÃO INFORMADO' && strpos($n, 'AUTOR') === false;
}));
if (empty($autores)) {
    for ($i = 1; $i <= 5; $i++) {
        if (isset($dadosArray["autor$i"]) && !empty($dadosArray["autor$i"]['nome']) && strtoupper($dadosArray["autor$i"]['nome']) !== 'NÃO INFORMADO' && strpos(strtoupper($dadosArray["autor$i"]['nome']), 'AUTOR') === false) {
            $autores[] = $dadosArray["autor$i"];
        }
    }
}
if (empty($autores)) { $autores[] = $dadosArray['autor1'] ?? ['nome' => 'NÃO INFORMADO']; }
$autoresNomes = array_map(function ($a) { return $a['nome'] ?? 'NÃO INFORMADO'; }, $autores);
if (count($autoresNomes) === 1) { $listaAutores = $autoresNomes[0]; } else { $ultimo = array_pop($autoresNomes); $listaAutores = implode(', ', $autoresNomes) . ' e ' . $ultimo; }

// ✅ LÓGICA DINÂMICA PARA VÍTIMAS (CHIPS)
$vitimas = [];
if (isset($dadosArray['vitimas'])) {
    $rawVitimas = $dadosArray['vitimas'];
    if (is_string($rawVitimas)) { $decoded = json_decode($rawVitimas, true); if (is_array($decoded)) { $vitimas = $decoded; } } elseif (is_array($rawVitimas)) { $vitimas = $rawVitimas; }
}
$vitimas = array_values(array_filter($vitimas, function ($v) {
    $n = strtoupper(trim($v['nome'] ?? '')); return $n !== '' && $n !== 'NÃO INFORMADO' && strpos($n, 'VITIMA') === false;
}));
if (empty($vitimas)) {
    for ($i = 1; $i <= 3; $i++) {
        if (isset($dadosArray["vitima$i"]) && !empty($dadosArray["vitima$i"]['nome']) && strtoupper($dadosArray["vitima$i"]['nome']) !== 'NÃO INFORMADO' && strpos(strtoupper($dadosArray["vitima$i"]['nome']), 'VITIMA') === false) {
            $vitimas[] = $dadosArray["vitima$i"];
        }
    }
}
if (empty($vitimas)) { $vitimas[] = $dadosArray['vitima1'] ?? ['nome' => 'NÃO INFORMADO']; }
$totalVitimas = count($vitimas);
$vitimasNomes = array_map(function ($v) { return $v['nome'] ?? 'NÃO INFORMADO'; }, $vitimas);
if ($totalVitimas === 1) { $listaVitimas = $vitimasNomes[0]; $textoVitima = 'da VÍTIMA'; } elseif ($totalVitimas === 2) { $listaVitimas = $vitimasNomes[0] . ' e ' . $vitimasNomes[1]; $textoVitima = 'das VÍTIMAS'; } elseif ($totalVitimas === 3) { $listaVitimas = $vitimasNomes[0] . ', ' . $vitimasNomes[1] . ' e ' . $vitimasNomes[2]; $textoVitima = 'das VÍTIMAS'; } else { $listaVitimas = $vitimasNomes[0] . ', ' . $vitimasNomes[1] . ' e outras'; $textoVitima = 'das VÍTIMAS'; }

// ✅ LÓGICA DINÂMICA PARA TESTEMUNHAS (CHIPS)
$testemunhas = [];
if (isset($dadosArray['testemunhas'])) {
    $rawTestemunhas = $dadosArray['testemunhas'];
    if (is_string($rawTestemunhas)) { $decoded = json_decode($rawTestemunhas, true); if (is_array($decoded)) { $testemunhas = $decoded; } } elseif (is_array($rawTestemunhas)) { $testemunhas = $rawTestemunhas; }
}
$testemunhas = array_values(array_filter($testemunhas, function ($t) {
    $n = strtoupper(trim($t['nome'] ?? '')); return $n !== '' && $n !== 'NÃO INFORMADO' && strpos($n, 'TESTEMUNHA') === false;
}));
if (empty($testemunhas)) {
    for ($i = 1; $i <= 3; $i++) {
        if (isset($dadosArray["testemunha$i"]) && !empty($dadosArray["testemunha$i"]['nome']) && strtoupper($dadosArray["testemunha$i"]['nome']) !== 'NÃO INFORMADO' && strpos(strtoupper($dadosArray["testemunha$i"]['nome']), 'TESTEMUNHA') === false) {
            $testemunhas[] = $dadosArray["testemunha$i"];
        }
    }
}
if (empty($testemunhas)) { $testemunhas[] = $dadosArray['testemunha1'] ?? ['nome' => 'NÃO INFORMADO']; }
$testemunhas = array_slice($testemunhas, 0, 2);
$testemunhasNomes = array_map(function ($t) { return $t['nome'] ?? 'NÃO INFORMADO'; }, $testemunhas);
if (count($testemunhasNomes) === 1) { $listaTestemunhas = $testemunhasNomes[0]; $textoTestemunha = 'da TESTEMUNHA'; } else { $ultimo = array_pop($testemunhasNomes); $listaTestemunhas = implode(', ', $testemunhasNomes) . ' e ' . $ultimo; $textoTestemunha = 'das TESTEMUNHAS'; }
?>

    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor de DEPOIMENTO | CONDUTOR AAFAI
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

        <!-- ÁREA DO EDITOR -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">

                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 16pt;">AUTO DE APREENSAO EM FLAGRANTE DE ATO INFRACIONAL</strong><br>
                    <span style="font-size: 14px; font-weight: normal;">(ART. 173, I DA LEI Nº 8.069, DE
                        13/07/1990)</span>
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <!-- CONDUTOR -->
                <div style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Aos <strong>{{ !empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO' }}</strong>,
                    nesta Cidade de {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }},
                    Estado de Pernambuco, no Cartório desta Delegacia de Polícia, onde presente se encontrava
                    o (a) Bel. (a) <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>,
                    respectivo (a) Delegado (a), comigo, Escrivão(ã) de seu cargo, ao final assinado, aí compareceu
                    na qualidade de <strong>CONDUTOR</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'nome'); ?>,
                    <strong>ALCUNHA</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'alcunha'); ?>,
                    <strong>NASCIMENTO</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'nascimento'); ?>,
                    <strong>IDADE</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'idade'); ?>,
                    <strong>ESTADO CIVIL</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'estcivil'); ?>,
                    <strong>NATURALIDADE</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'naturalidade'); ?>,
                    <strong>RG</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'rg'); ?>,
                    <strong>CPF</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'cpf'); ?>,
                    <strong>PROFISSÃO</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'profissao'); ?>,
                    <strong>INSTRUÇÃO</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'instrucao'); ?>,
                    <strong>TELEFONE</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'telefone'); ?>,
                    <strong>MÃE</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'mae'); ?>,
                    <strong>PAI</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'pai'); ?>,
                    <strong>ENDEREÇO</strong>: <?php echo exibirDado($dadosArray, 'condutor', 'endereco'); ?>.
                    Compromissado(a) na forma da lei, advertido(a) das penas cominadas ao falso testemunho, prometeu dizer a verdade do que
                    soubesse e lhe fosse perguntado. Inquirido(a), respondeu: QUE apresenta em flagrante por ato
                    infracional: <strong><?php echo $listaAutores; ?></strong>, na presença
                    <?php echo $textoVitima; ?>: <strong><?php echo $listaVitimas; ?></strong> e <?php echo $textoTestemunha; ?>:
                    <strong><?php echo $listaTestemunhas; ?></strong>, pelos motivos que, a seguir, passa a
                    expor:
                </p>
                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <span id="conteudo-depoimento">
                    @if(!empty($dadosArray['_conteudo_salvo']))
                    {!! $dadosArray['_conteudo_salvo'] !!}
                    @else
                    {...ESCREVER AQUI O DEPOIMENTO...}
                    @endif
                    </span>
                </p>
                <p style="line-height: 1.6; margin: 0.2em 0; padding: 0;">&nbsp;</p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Nada mais disse nem lhe foi perguntado, determinou a Autoridade Policial encerrar este Termo,
                    que, após ser lido e achado conforme, o qual assina juntamente com o Condutor e comigo Escrivão que digitei.
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

    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    
    <script src="{{ asset('js/DocumentoService.js') }}"></script>

    <!-- Dados para JavaScript - CORRIGIDO -->
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
            testemunha1: <?php echo json_encode(isset($dadosArray['testemunha1']) ? $dadosArray['testemunha1'] : []); ?>,
            autor1: <?php echo json_encode(isset($dadosArray['autor1']) ? $dadosArray['autor1'] : []); ?>,

            // ✅ LISTAS DINÂMICAS DE CHIPS
            autores: <?php echo json_encode(isset($autores) ? $autores : []); ?>,
            vitimas: <?php echo json_encode(isset($vitimas) ? $vitimas : []); ?>,
            testemunhas: <?php echo json_encode(isset($testemunhas) ? $testemunhas : []); ?>
        };
    </script>

    <!-- ✅ JS ESPECÍFICO PARA AAFAI CONDUTOR -->
    <script src="{{ asset('js/pages/aafai/aafai_condutor.js') }}?v=<?php echo time(); ?>"></script>
</body>
</html>
