<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OFÍCIO ÚNICO APFD - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    <style>
        @media print {

            /* Compacting the header area */
            .print-header {
                margin-bottom: 5px !important;
                padding-bottom: 5px !important;
                border-bottom: 1px solid #000 !important;
            }

            /* Reducing margins between paragraphs to fit more content */
            #editor p.preservar-espacamento,
            #editor p {
                margin: 2px 0 !important;
                /* Drastically reduced from 0.8em */
                line-height: 1.3 !important;
                /* Reduced from 1.6 */
            }

            /* Specifically for the date line to pull it up */
            .ql-align-right.preservar-espacamento {
                margin-top: -10px !important;
                margin-bottom: 5px !important;
            }

            /* Ensure the recipient header stays compact */
            .preservar-espacamento strong {
                line-height: 1.2 !important;
            }

            /* Reduce page margins if needed, though usually handled by @page */
            @page {
                margin: 1cm 1.5cm !important;
                /* Tighter page margins */
            }

            /* Hide empty paragraphs that might consume space */
            p:empty {
                display: none !important;
            }

            /* But keep the specific spacer if needed, or reduce it */
            p.preservar-espacamento:has(br) {
                line-height: 0.5 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>

<body class="body-declaracao">
    <?php
// ✅ DECODIFICAR DADOS DA URL APENAS SE NÃO FOI PASSADO PELO CONTROLLER
if (!isset($dadosArray) || empty($dadosArray)) {
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
                    'endereco' => 'NÃO INFORMADO',
                    'tipopenal' => 'NÃO INFORMADO'
                ], $dadosArray[$pessoa]);
            }
        }
    } catch (Exception $e) {
        $dadosArray = [];
    }
}
}

function exibirDado($array, $chave, $subchave = null, $padrao = 'NÃO INFORMADO')
{
    if (!$array || !is_array($array))
        return $padrao;
    if ($subchave)
        return isset($array[$chave][$subchave]) && !empty($array[$chave][$subchave]) ? $array[$chave][$subchave] : $padrao;
    return isset($array[$chave]) && !empty($array[$chave]) ? $array[$chave] : $padrao;
}

// ✅ LÓGICA DINÂMICA PARA AUTORES
$autores = [];
if (isset($dadosArray['autores'])) {
    $rawAutores = $dadosArray['autores'];
    if (is_string($rawAutores)) {
        $decoded = json_decode($rawAutores, true);
        if (is_array($decoded))
            $autores = $decoded;
    } elseif (is_array($rawAutores)) {
        $autores = $rawAutores;
    }
}

// Filtra e normaliza autores
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

if (empty($autores))
    $autores[] = $dadosArray['autor1'] ?? ['nome' => 'NÃO INFORMADO', 'tipopenal' => 'NÃO INFORMADO'];

$qtdAutores = count($autores);
$isPlural = $qtdAutores > 1;

$nomes = array_map(function ($a) {
    return $a['nome'] ?? 'NÃO INFORMADO';
}, $autores);
$listaNomes = '';
if ($qtdAutores === 1) {
    $listaNomes = $nomes[0];
} else {
    $ultimo = array_pop($nomes);
    $listaNomes = implode(', ', $nomes) . ' e ' . $ultimo;
}

$incPenalGeral = '';
if (!empty($dadosArray['incidencia_penal']) && strtoupper(trim($dadosArray['incidencia_penal'])) !== 'NÃO INFORMADO') {
    $incPenalGeral = $dadosArray['incidencia_penal'];
} elseif (!empty($dadosArray['natureza']) && strtoupper(trim($dadosArray['natureza'])) !== 'NÃO INFORMADO') {
    $incPenalGeral = $dadosArray['natureza'];
}

$listaTiposPenais = '';
if ($incPenalGeral !== '') {
    $listaTiposPenais = $incPenalGeral;
} else {
    $tipos = array_map(function ($a) {
        return $a['tipopenal'] ?? 'NÃO INFORMADO';
    }, $autores);
    $tipos = array_values(array_filter($tipos, function ($t) {
        $n = strtoupper(trim($t));
        return $n !== '' && $n !== 'NÃO INFORMADO';
    }));
    if (empty($tipos))
        $listaTiposPenais = 'NÃO INFORMADO';
    elseif (count(array_unique($tipos)) === 1)
        $listaTiposPenais = $tipos[0];
    else {
        $tiposList = array_values(array_unique($tipos));
        $ultimoTipo = array_pop($tiposList);
        $listaTiposPenais = implode(', ', $tiposList) . ' e ' . $ultimoTipo;
    }
}

$dataComp = !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : (!empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO');

// ✅ LÓGICA DINÂMICA PARA VÍTIMAS
$vitimas = [];
if (isset($dadosArray['vitimas']) && is_array($dadosArray['vitimas'])) {
    $vitimas = array_filter($dadosArray['vitimas'], function($v) {
        $n = is_string($v) ? $v : ($v['nome'] ?? '');
        $n = strtoupper(trim($n));
        return $n !== '' && $n !== 'NÃO INFORMADO' && strpos($n, 'VITIMA') === false;
    });
    $vitimas = array_values($vitimas);
}

// Fallback para vitima1, vitima2, vitima3
if (empty($vitimas)) {
    for ($i = 1; $i <= 3; $i++) {
        if (isset($dadosArray["vitima$i"]) && !empty($dadosArray["vitima$i"]['nome'])) {
            $n = strtoupper(trim($dadosArray["vitima$i"]['nome']));
            if ($n !== 'NÃO INFORMADO' && strpos($n, 'VITIMA') === false) {
                $vitimas[] = $n;
            }
        }
    }
}

$qtdVitimas = count($vitimas);
$textoVitimas = 'tendo como vítima: <strong>NÃO INFORMADO</strong>';

if ($qtdVitimas == 1) {
    $nome = is_string($vitimas[0]) ? $vitimas[0] : ($vitimas[0]['nome'] ?? '');
    $textoVitimas = 'tendo como vítima: <strong>' . $nome . '</strong>';
} elseif ($qtdVitimas == 2) {
    $nome1 = is_string($vitimas[0]) ? $vitimas[0] : ($vitimas[0]['nome'] ?? '');
    $nome2 = is_string($vitimas[1]) ? $vitimas[1] : ($vitimas[1]['nome'] ?? '');
    $textoVitimas = 'tendo como vítimas: <strong>' . $nome1 . ' e ' . $nome2 . '</strong>';
} elseif ($qtdVitimas > 2) {
    $nome1 = is_string($vitimas[0]) ? $vitimas[0] : ($vitimas[0]['nome'] ?? '');
    $nome2 = is_string($vitimas[1]) ? $vitimas[1] : ($vitimas[1]['nome'] ?? '');
    $textoVitimas = 'tendo como vítimas: <strong>' . $nome1 . ', ' . $nome2 . ' e outros(as)</strong>';
}

// ✅ LÓGICA DE DESFECHO (3 GRUPOS)
$grupoPagou = [];
$grupoNaoPagouArbitrada = [];
$grupoSemFianca = [];

foreach ($autores as $autor) {
    $nome = $autor['nome'] ?? 'NÃO INFORMADO';
    $fianca = isset($autor['fianca']) ? $autor['fianca'] : null;
    $fiancaPagoRaw = $autor['fianca_pago'] ?? $autor['FiancaPago'] ?? false;
    $fiancaPago = filter_var($fiancaPagoRaw, FILTER_VALIDATE_BOOLEAN) || $fiancaPagoRaw === 1 || $fiancaPagoRaw === '1';
    $temValor = ($fianca && floatval(str_replace(['.', ','], ['', '.'], $fianca)) > 0);

    if ($temValor && $fiancaPago) {
        $grupoPagou[] = ['nome' => $nome, 'valor' => $fianca, 'extenso' => $autor['fianca_ext'] ?? ''];
    } elseif ($temValor && !$fiancaPago) {
        $grupoNaoPagouArbitrada[] = ['nome' => $nome, 'valor' => $fianca, 'extenso' => $autor['fianca_ext'] ?? ''];
    } else {
        $grupoSemFianca[] = $nome;
    }
}

$partesTexto = [];
if (count($grupoPagou) > 0) {
    $nomes = array_map(function ($a) {
        return $a['nome'];
    }, $grupoPagou);
    $nomesStr = implode(', ', $nomes);
    if (count($grupoPagou) == 1) {
        $val = $grupoPagou[0];
        $partesTexto[] = "em relação ao autuado(a) <strong>{$val['nome']}</strong>, foi arbitrada fiança no valor de R$ {$val['valor']} ({$val['extenso']}), a qual foi devidamente recolhida, sendo o(a) mesmo(a) posto(a) em liberdade.";
    } else {
        $partesTexto[] = "em relação aos autuados <strong>{$nomesStr}</strong>, foram arbitradas fianças criminais, as quais foram devidamente recolhidas, sendo os mesmos postos em liberdade.";
    }
}
if (count($grupoNaoPagouArbitrada) > 0) {
    $nomes = array_map(function ($a) {
        return $a['nome'];
    }, $grupoNaoPagouArbitrada);
    $nomesStr = implode(', ', $nomes);
    $plural = count($grupoNaoPagouArbitrada) > 1;
    if ($plural) {
        $partesTexto[] = "Já quanto aos autuados <strong>{$nomesStr}</strong>, foi arbitrada fiança criminal, porém os mesmos deixaram de recolher o valor estipulado, sendo recolhidos ao xadrez desta unidade policial, a fim de serem apresentados na Audiência de Custódia.";
    } else {
        $val = $grupoNaoPagouArbitrada[0];
        $partesTexto[] = "Já quanto ao autuado(a) <strong>{$val['nome']}</strong>, foi arbitrada fiança no valor de R$ {$val['valor']} ({$val['extenso']}), porém o(a) mesmo(a) deixou de recolher o valor estipulado, sendo recolhido(a) ao xadrez desta unidade policial, a fim de ser apresentado(a) na Audiência de Custódia.";
    }
}
if (count($grupoSemFianca) > 0) {
    $nomesStr = implode(', ', $grupoSemFianca);
    $plural = count($grupoSemFianca) > 1;
    if ($plural) {
        $partesTexto[] = "Em relação aos autuados <strong>{$nomesStr}</strong>, foram recolhidos ao xadrez desta unidade policial, a fim de serem apresentados na Audiência de Custódia.";
    } else {
        $partesTexto[] = "Em relação ao autuado(a) <strong>{$nomesStr}</strong>, foi recolhido(a) ao xadrez desta unidade policial, a fim de ser apresentado(a) na Audiência de Custódia.";
    }
}

// Junta tudo com formatação de parágrafo correta
// O prefixo "Após as formalidades legais, " é incluído aqui para iniciar a concatenação
$textoDesfecho = "Após as formalidades legais, " . implode("</p><p style=\"text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;\">&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;", $partesTexto);
    ?>

    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor de OFÍCIO ÚNICO | APFD
            </h1>
        </div>

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



        <!-- ÁREA DO EDITOR UNIFICADO -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">
                <p class="preservar-espacamento ql-align-right" style="margin-top: -25px !important;">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dataComp }}
                </p>

                <p class="preservar-espacamento"><strong>Ofício nº {{ $numeroOficio ?? 'NÃO GERADO' }}</strong></p>

                <p class="preservar-espacamento"><br></p>

                <!-- CABEÇALHO UNIFICADO REFORMATADO -->
                <p class="preservar-espacamento"><strong>A SUA EXCELÊNCIA O(A) SENHOR(A)</strong></p>
                <p class="preservar-espacamento" style="white-space: nowrap !important; font-size: 10pt !important;">
                    <strong>JUIZ(A) DE DIREITO | PROMOTOR(A) DE JUSTIÇA | DEFENSOR(A) PÚBLICO(A)</strong>
                <p class="preservar-espacamento"><strong>ESTADO DE PERNAMBUCO</strong></p>

                <p class="preservar-espacamento">&nbsp;</p>

                <!-- SAUDAÇÃO UNIFICADA -->
                <p class="preservar-espacamento">Senhor(a) Juiz(a), Promotor(a), Defensor(a)</p>

                <p class="preservar-espacamento"><br></p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Levo ao conhecimento de V.Ex.ªs que
                    {{ $isPlural ? 'foram presos' : 'foi preso' }} em flagrante delito
                    {{ $isPlural ? 'as pessoas de' : 'a pessoa de' }}
                    <strong>{{ $listaNomes }}</strong>, {{ $isPlural ? 'por infrigirem' : 'por infrigir' }} as penas
                    do(a)
                    <strong>{{ $listaTiposPenais }}</strong>,
                    fato ocorrido no dia {{ $dataComp }}, na(o) cidade de
                    <strong>{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}/PE</strong>,
                    de acordo com o Boletim de Ocorrência de nº
                    <strong>{{ !empty($dadosArray['boe']) ? $dadosArray['boe'] : 'NÃO INFORMADO' }}</strong>, <?php echo $textoVitimas; ?>.
                </p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;<?php echo $textoDesfecho; ?>
                </p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Em anexo, seguem cópias do
                    procedimento lavrado.
                </p>

                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Atenciosamente,
                    <br><br><br>
                    <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong><br>
                    Delegado(a) de Polícia
                </p>
            </div>
        </div>

        <div class="editor-stats">
            <div class="stat-item">
                <i class="fas fa-keyboard"></i> <span id="char-count">0 caracteres</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-paragraph"></i> <span id="paragraph-count">0 parágrafos</span>
            </div>
            <div class="stat-item">
                <i class="fas fa-clock"></i> <span>Última alteração: <span id="last-modified">Agora</span></span>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- TinyMCE 6 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}"></script>

    <script>
        window.dadosParaImpressao = {
            tipo: 'apfd_unico',
            delegacia: <?php echo json_encode(exibirDado($dadosArray, 'delegacia')); ?>,
            cidade: <?php echo json_encode(exibirDado($dadosArray, 'cidade')); ?>,
            delegado: <?php echo json_encode(exibirDado($dadosArray, 'delegado')); ?>,
            boe: <?php echo json_encode(exibirDado($dadosArray, 'boe')); ?>,
            data_ext: <?php echo json_encode(exibirDado($dadosArray, 'data_ext')); ?>,
            data_comp: <?php echo json_encode($dataComp); ?>,
            autores: <?php echo json_encode($autores); ?>
        };
    </script>
    <script src="{{ asset('js/pages/apfd/oficios/oficios_apfd_dinamico.js') }}?v=<?php echo time(); ?>"></script>
</body>

</html>

