<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OFÍCIOS APFD - DINÂMICO - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>

<body class="body-declaracao">
    <?php
// ✅ DECODIFICAR DADOS DA URL
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
                    'endereco' => 'NÃO INFORMADO',
                    'tipopenal' => 'NÃO INFORMADO'
                ], $dadosArray[$pessoa]);
            }
        }
    } catch (Exception $e) {
        $dadosArray = [];
    }
}

// ✅ FUNÇÃO AUXILIAR PARA EXIBIR DADOS COM SEGURANÇA
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

// ✅ LÓGICA DINÂMICA PARA AUTORES (ROBUSTA)
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

// Se não encontrou autores válidos, usa autor1 como padrão mesmo se vazio
if (empty($autores)) {
    $autores[] = $dadosArray['autor1'] ?? ['nome' => 'NÃO INFORMADO', 'tipopenal' => 'NÃO INFORMADO'];
}

$qtdAutores = count($autores);
$isPlural = $qtdAutores > 1;

// Formatar lista de nomes
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
    if (empty($tipos)) {
        $listaTiposPenais = 'NÃO INFORMADO';
    } elseif (count(array_unique($tipos)) === 1) {
        $listaTiposPenais = $tipos[0];
    } else {
        $tiposList = array_values(array_unique($tipos));
        $ultimoTipo = array_pop($tiposList);
        $listaTiposPenais = implode(', ', $tiposList) . ' e ' . $ultimoTipo;
    }
}

// Data de competência
$dataComp = !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : (!empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'NÃO INFORMADO');

// ✅ LÓGICA DE DESFECHO REFINADA (3 GRUPOS)
$grupoPagou = [];           // Fiança > 0 E Pagou = SIM
$grupoNaoPagouArbitrada = []; // Fiança > 0 E Pagou = NÃO
$grupoSemFianca = [];       // Fiança = 0 ou Vazio

foreach ($autores as $autor) {
    $nome = $autor['nome'] ?? 'NÃO INFORMADO';
    $fianca = isset($autor['fianca']) ? $autor['fianca'] : null;

    // Normaliza fiancaPago
    $fiancaPagoRaw = $autor['fianca_pago'] ?? $autor['FiancaPago'] ?? false;
    $fiancaPago = filter_var($fiancaPagoRaw, FILTER_VALIDATE_BOOLEAN) || $fiancaPagoRaw === 1 || $fiancaPagoRaw === '1';

    // Existe valor de fiança?
    $temValor = ($fianca && floatval(str_replace(['.', ','], ['', '.'], $fianca)) > 0);

    if ($temValor && $fiancaPago) {
        $grupoPagou[] = [
            'nome' => $nome,
            'valor' => $fianca,
            'extenso' => $autor['fianca_ext'] ?? ''
        ];
    } elseif ($temValor && !$fiancaPago) {
        $grupoNaoPagouArbitrada[] = [
            'nome' => $nome,
            'valor' => $fianca,
            'extenso' => $autor['fianca_ext'] ?? ''
        ];
    } else {
        $grupoSemFianca[] = $nome;
    }
}

$partesTexto = [];

// 1. GRUPO PAGOU (Liberdade)
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

// 2. GRUPO NÃO PAGOU ARBITRADA (Preso - Deixou de recolher)
if (count($grupoNaoPagouArbitrada) > 0) {
    $nomes = array_map(function ($a) {
        return $a['nome'];
    }, $grupoNaoPagouArbitrada);
    $nomesStr = implode(', ', $nomes);
    $plural = count($grupoNaoPagouArbitrada) > 1;

    if ($plural) {
        $partesTexto[] = "Já quanto aos autuados <strong>{$nomesStr}</strong>, foi arbitrada fiança criminal, porém os mesmos deixaram de pagar o valor estipulado, sendo recolhidos ao xadrez desta unidade policial, a fim de serem apresentados na Audiência de Custódia.";
    } else {
        $val = $grupoNaoPagouArbitrada[0];
        $partesTexto[] = "Já quanto ao autuado(a) <strong>{$val['nome']}</strong>, foi arbitrada fiança no valor de R$ {$val['valor']} ({$val['extenso']}), porém o(a) mesmo(a) deixou de pagar o valor estipulado, sendo recolhido(a) ao xadrez desta unidade policial, a fim de ser apresentado(a) na Audiência de Custódia.";
    }
}

// 3. GRUPO SEM FIANÇA (Preso - Inafiançável ou Outros)
if (count($grupoSemFianca) > 0) {
    $nomesStr = implode(', ', $grupoSemFianca);
    $plural = count($grupoSemFianca) > 1;

    if ($plural) {
        $partesTexto[] = "Em relação aos autuados <strong>{$nomesStr}</strong>, foram recolhidos ao xadrez desta unidade policial, a fim de serem apresentados na Audiência de Custódia.";
    } else {
        $partesTexto[] = "Em relação ao autuado(a) <strong>{$nomesStr}</strong>, foi recolhido(a) ao xadrez desta unidade policial, a fim de ser apresentado(a) na Audiência de Custódia.";
    }
}

// Junta tudo
// O prefixo "Após as formalidades legais, " foi removido daqui para ser incluído diretamente no HTML ou como parte do primeiro item se necessário.
// Mas para manter a lógica original onde isso é o início:
$textoDesfecho = "Após as formalidades legais, " . implode("</p><p style=\"text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;\">&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;", $partesTexto);
    ?>

    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor de OFÍCIOS | APFD - COMUNICAÇÃO JUIZ, PROMOTOR, DEFENSOR
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
            <div id="editor" class="preservar-espacamento">{{-- PRIMEIRO OFÍCIO (Juiz) --}}<p
                    class="preservar-espacamento ql-align-right">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dataComp }}
                </p>

                <p class="preservar-espacamento"><strong>Ofício nº {{ $numeroOficioJuiz ?? 'NÃO GERADO' }}</strong></p>

                <p class="preservar-espacamento"><br></p>

                <p class="preservar-espacamento"><strong>A SUA EXCELÊNCIA O(A) SENHOR(A)</strong></p>
                <p class="preservar-espacamento"><strong>JUIZ(A) DE DIREITO DO ESTADO DE PERNAMBUCO</strong></p>

                <p class="preservar-espacamento">&nbsp;</p>

                <p class="preservar-espacamento">Senhor(a) Juiz(a),</p>

                <p class="preservar-espacamento"><br></p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Levo ao conhecimento de V.Ex.ª que
                    {{ $isPlural ? 'foram presos' : 'foi preso' }} em flagrante delito
                    {{ $isPlural ? 'as pessoas de' : 'a pessoa de' }}
                    <strong>{{ $listaNomes }}</strong>, {{ $isPlural ? 'por infrigirem' : 'por infrigir' }} as penas
                    do(a)
                    <strong>{{ $listaTiposPenais }}</strong>,
                    fato ocorrido no dia {{ $dataComp }}, na(o) cidade de
                    <strong>{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}/PE</strong>,
                    de acordo com o Boletim de Ocorrência de nº
                    <strong>{{ !empty($dadosArray['boe']) ? $dadosArray['boe'] : 'NÃO INFORMADO' }}</strong>, tendo como
                    vítima: <strong><?php echo exibirDado($dadosArray, 'vitima1', 'nome'); ?></strong>.
                </p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;<?php echo $textoDesfecho; ?>
                </p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Em anexo, seguem cópias do
                    procedimento lavrado.
                </p>

                <p class="preservar-espacamento"><br></p>

                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Atenciosamente,
                    <br><br><br>
                    <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong><br>
                    Delegado(a) de Polícia
                </p>

                <!-- ✅✅✅ QUEBRA DE PÁGINA COMPATÍVEL COM QUILL -->
                <p class="page-break-marker"
                    style="page-break-before: always; break-before: page; height: 0; margin: 0; padding: 0; visibility: hidden;">
                    --- QUEBRA DE PÁGINA ---</p>

                <!-- SEGUNDO OFÍCIO (Promotor) - COMEÇA NA PÁGINA 2 -->
                <p class="preservar-espacamento ql-align-right">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dataComp }}
                </p>

                <p class="preservar-espacamento"><strong>Ofício nº {{ $numeroOficioPromotor ?? 'NÃO GERADO' }}</strong>
                </p>

                <p class="preservar-espacamento"><br></p>

                <p class="preservar-espacamento"><strong>A SUA EXCELÊNCIA O(A) SENHOR(A)</strong></p>
                <p class="preservar-espacamento"><strong>PROMOTOR(A) DE JUSTIÇA DO ESTADO DE PERNAMBUCO</strong></p>

                <p class="preservar-espacamento">&nbsp;</p>

                <p class="preservar-espacamento">Senhor(a) Promotor(a),</p>

                <p class="preservar-espacamento"><br></p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Levo ao conhecimento de V.Ex.ª que
                    {{ $isPlural ? 'foram presos' : 'foi preso' }} em flagrante delito
                    {{ $isPlural ? 'as pessoas de' : 'a pessoa de' }}
                    <strong>{{ $listaNomes }}</strong>, {{ $isPlural ? 'por infrigirem' : 'por infrigir' }} as penas
                    do(a)
                    <strong>{{ $listaTiposPenais }}</strong>,
                    fato ocorrido no dia {{ $dataComp }}, na(o) cidade de
                    <strong>{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}/PE</strong>,
                    de acordo com o Boletim de Ocorrência de nº
                    <strong>{{ !empty($dadosArray['boe']) ? $dadosArray['boe'] : 'NÃO INFORMADO' }}</strong>, tendo como
                    vítima: <strong><?php echo exibirDado($dadosArray, 'vitima1', 'nome'); ?></strong>.
                </p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;<?php echo $textoDesfecho; ?>
                </p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Em anexo, seguem cópias do
                    procedimento lavrado.
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

                <!-- ✅✅✅ QUEBRA DE PÁGINA COMPATÍVEL COM QUILL -->
                <p class="page-break-marker"
                    style="page-break-before: always; break-before: page; height: 0; margin: 0; padding: 0; visibility: hidden;">
                    --- QUEBRA DE PÁGINA ---</p>

                <!-- TERCEIRO OFÍCIO (Defensor) - COMEÇA NA PÁGINA 3 -->
                <p class="preservar-espacamento ql-align-right">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dataComp }}
                </p>

                <p class="preservar-espacamento"><strong>Ofício nº {{ $numeroOficioDefensor ?? 'NÃO GERADO' }}</strong>
                </p>

                <p class="preservar-espacamento"><br></p>

                <p class="preservar-espacamento"><strong>A SUA EXCELÊNCIA O(A) SENHOR(A)</strong></p>
                <p class="preservar-espacamento"><strong>DEFENSOR(A) PÚBLICO DO ESTADO DE PERNAMBUCO</strong></p>

                <p class="preservar-espacamento">&nbsp;</p>

                <p class="preservar-espacamento">Senhor(a) Defensor(a),</p>

                <p class="preservar-espacamento"><br></p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Levo ao conhecimento de V.Ex.ª que
                    {{ $isPlural ? 'foram presos' : 'foi preso' }} em flagrante delito
                    {{ $isPlural ? 'as pessoas de' : 'a pessoa de' }}
                    <strong>{{ $listaNomes }}</strong>, {{ $isPlural ? 'por infrigirem' : 'por infrigir' }} as penas
                    do(a)
                    <strong>{{ $listaTiposPenais }}</strong>,
                    fato ocorrido no dia {{ $dataComp }}, na(o) cidade de
                    <strong>{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}/PE</strong>,
                    de acordo com o Boletim de Ocorrência de nº
                    <strong>{{ !empty($dadosArray['boe']) ? $dadosArray['boe'] : 'NÃO INFORMADO' }}</strong>, tendo como
                    vítima: <strong><?php echo exibirDado($dadosArray, 'vitima1', 'nome'); ?></strong>.
                </p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;<?php echo $textoDesfecho; ?>
                </p>

                <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;Em anexo, seguem cópias do
                    procedimento lavrado.
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
            data_comp: <?php echo json_encode($dataComp); ?>,
            ip: <?php echo json_encode(exibirDado($dadosArray, 'ip')); ?>,
            incidencia_penal: <?php echo json_encode(isset($dadosArray['incidencia_penal']) ? $dadosArray['incidencia_penal'] : (isset($dadosArray['natureza']) ? $dadosArray['natureza'] : '')); ?>,

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

            // ✅ LISTA COMPLETA DE AUTORES (NOVO)
            autores: <?php echo json_encode($autores); ?>
        };
    </script>

    <script>
        console.log('🟢 BLADE OFÍCIOS APFD DINÂMICO CARREGADO');
        console.log('🟢 Rota:', '{{ request()->path() }}');
    </script>

    <!-- ✅ JS DINÂMICO -->
    <script src="{{ asset('js/pages/apfd/oficios/oficios_apfd_dinamico.js') }}?v=<?php echo time(); ?>"></script>

</body>

</html>