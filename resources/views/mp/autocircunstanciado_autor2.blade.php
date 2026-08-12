<!-- resources/views/mp/autocircunstanciado_autor2.blade.php -->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AUTO CIRCUNSTANCIADO - AUTOR 2 - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    
</head>
<body class="body-declaracao">
<?php
$dadosBase64 = request()->segment(2);
$dadosArray = [];
if ($dadosBase64) {
    try { $dadosJson = base64_decode($dadosBase64); $dadosArray = json_decode($dadosJson, true) ?? []; } catch (Exception $e) { $dadosArray = []; }
}
function exibirDado($array, $chave, $subchave = null, $padrao = 'NÃO INFORMADO') {
    if (!$array || !is_array($array)) return $padrao;
    if ($subchave) return (isset($array[$chave][$subchave]) && !empty($array[$chave][$subchave])) ? $array[$chave][$subchave] : $padrao;
    return (isset($array[$chave]) && !empty($array[$chave])) ? $array[$chave] : $padrao;
}
?>
<div class="editor-wrapper">
    <div class="editor-header">
        <h1 class="editor-title"><i class="fas fa-file-contract"></i> Editor do AUTO CIRCUNSTACIADO - AUTOR 2</h1>
    </div>
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
                        <?php echo exibirDado($dadosArray, 'delegacia'); ?> – <?php echo exibirDado($dadosArray, 'cidade'); ?>
                    </div>
                </div>
                <img src="{{ asset('images/b_PCPE.png') }}" alt="Brasão da Polícia Civil">
            </div>
        </div>
    </div>



    <div class="editor-area">
        <div id="editor" class="preservar-espacamento">
            <p style="text-align:center;line-height:1.6;margin:0.2em 0;"><strong style="font-size:16pt;">AUTO CIRCUNSTANCIADO DE BUSCA E APREENSÃO</strong><br><span style="font-size:14px;">(Art. 245, § 7º do CPP)</span></p>
            <p class="ql-align-justify preservar-espacamento"><br></p>
            <div style="line-height:1.6;margin:0.2em 0;padding:0;text-align:left;">
                <p style="line-height:1.6;margin:0.2em 0;padding:0;text-align:justify;">
                    Aos [DATA], nesta Cidade de [CIDADE], Estado de Pernambuco, em cumprimento ao Mandado de Busca e Apreensão, [NUMERO MANDADO],
                    expedido pelo MM. Juiz de Direito da Comarca de [CIDADE], às [HORA]  horas, fui em diligência à ENDEREÇO DO MANDADO e aí, depois
                    de exibir o referido mandado judicial, INTIMEI o cidadão [INTIMADO], proprietário do imóvel, no sentido de que, imediatamente, me
                    franqueasse o acesso ao interior do ambiente, a fim de efetuar a diligência ordenada, no que fui atendido pelo mesmo, sendo
                    convidadas, neste momento, para assistirem a esse procedimento, as Testemunhas [TESTEMUNHA_1] e [TESTEMUNHA_2], abaixo
                    assinadas. Em companhia destas, adentrando ao imóvel supracitado, foi realizada uma minuciosa busca, com o exame de todos os
                    cômodos, fazendo abrir as portas que estavam fechadas (descrever eventuais detalhes) aí encontrando:
                </p>
                <p class="ql-align-justify preservar-espacamento"><br></p>
                <span style="background-color: cyan;">ESCREVER AQUI O MATERIAL</span>
                <p class="ql-align-justify preservar-espacamento"><br></p>
                <p style="line-height:1.6;margin:0.2em 0;padding:0;text-align:justify;">
                    Apreendido e depositado neste Órgão Policial, para os fins devidos, com base no Art. 245 § 7º do CPP, para constar, determinei
                    a lavratura deste Auto, que, depois de lido e achado conforme, o subscrevo na condição de executor do Mandado, com as Testemunhas
                    supracitadas e o(a) Escrivão(ã) de meu cargo, que o digitou.
                </p>
                <p class="ql-align-justify preservar-espacamento"><br><br></p>
                <div class="assinatura-area">
                    <p>AUTORIDADE POLICIAL:</p><p>&nbsp;</p><p>TESTEMUNHA:</p><p>&nbsp;</p><p>TESTEMUNHA:</p><p>&nbsp;</p><p>ESCRIVÃO(Ã):</p>
                </div>
            </div>
        </div>
    </div>

    <div class="editor-stats">
        <div class="stat-item"><i class="fas fa-keyboard"></i><span id="char-count">0 caracteres</span></div>
        <div class="stat-item"><i class="fas fa-paragraph"></i><span id="paragraph-count">0 parágrafos</span></div>
        <div class="stat-item"><i class="fas fa-clock"></i><span>Última alteração: <span id="last-modified">Agora</span></span></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/DocumentoService.js') }}"></script>



<script>
window.dadosParaImpressao = {
    delegacia: <?php echo json_encode(exibirDado($dadosArray, 'delegacia')); ?>,
    cidade: <?php echo json_encode(exibirDado($dadosArray, 'cidade')); ?>,
    delegado: <?php echo json_encode(exibirDado($dadosArray, 'delegado')); ?>,
    escrivao: <?php echo json_encode(exibirDado($dadosArray, 'escrivao')); ?>,
    boe: <?php echo json_encode(exibirDado($dadosArray, 'boe')); ?>,
    data_ext: <?php echo json_encode(exibirDado($dadosArray, 'data_ext')); ?>,
    ip: <?php echo json_encode(exibirDado($dadosArray, 'ip')); ?>,
    hora_fato: <?php echo json_encode(exibirDado($dadosArray, 'hora_fato')); ?>,
    end_fato: <?php echo json_encode(exibirDado($dadosArray, 'end_fato')); ?>,
    nmandado: <?php echo json_encode(exibirDado($dadosArray, 'nmandado')); ?>,
    datamandado: <?php echo json_encode(exibirDado($dadosArray, 'datamandado')); ?>,
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
// Sinaliza ao JS que o INTIMADO deve ser o Autor 2
window.autorAlvo = 2;
</script>

<script src="{{ asset('js/pages/mp/autocircunstanciado.js') }}?v=<?php echo time(); ?>"></script>
</body>
</html>

