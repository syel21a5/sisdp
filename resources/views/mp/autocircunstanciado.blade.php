<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AUTO CIRCUNSTANCIADO - 1 AUTOR - Editor Profissional</title>
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

    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor do AUTO CIRCUNSTACIADO - 1 AUTOR
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

            <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
            <strong style="font-size: 16pt;">AUTO CIRCUNSTANCIADO DE BUSCA E APREENSÃO</strong><br>
            <span style="font-size: 14px; font-weight: normal;">(Art. 245, § 7º do CPP)</span>
            </p>

             <p class="ql-align-justify preservar-espacamento">
                <br>
            </p>

            <!-- CONDUTOR -->
            <div style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: left;">
                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Aos [DATA], nesta Cidade de [CIDADE], Estado de Pernambuco, em cumprimento ao Mandado de Busca e Apreensão, [NUMERO MANDADO],
                    expedido pelo MM. Juiz de Direito da Comarca de [CIDADE], às [HORA]  horas, fui em diligência à ENDEREÇO DO MANDADO e aí, depois
                    de exibir o referido mandado judicial, INTIMEI o cidadão [INTIMADO], proprietário do imóvel, no sentido de que, imediatamente, me
                    franqueasse o acesso ao interior do ambiente, a fim de efetuar a diligência ordenada, no que fui atendido pelo mesmo, sendo
                    convidadas, neste momento, para assistirem a esse procedimento, as Testemunhas [TESTEMUNHA_1] e [TESTEMUNHA_2], abaixo
                    assinadas. Em companhia destas, adentrando ao imóvel supracitado, foi realizada uma minuciosa busca, com o exame de todos os
                    cômodos, fazendo abrir as portas que estavam fechadas (descrever eventuais detalhes) aí encontrando:
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <span style="background-color: cyan;">ESCREVER AQUI O MATERIAL</span>
                </p>

                 <p class="ql-align-justify preservar-espacamento">
                    <br>
                </p>

                <p style="line-height: 1.6; margin: 0.2em 0px; padding: 0px; text-align: justify;">
                    Apreendido e depositado neste Órgão Policial, para os fins devidos, com base no Art. 245 § 7º do CPP, para constar, determinei
                    a lavratura deste Auto, que, depois de lido e achado conforme, o subscrevo na condição de executor do Mandado, com as Testemunhas
                    supracitadas e o(a) Escrivão(ã) de meu cargo, que o digitou.
                </p>

                <p class="ql-align-justify preservar-espacamento">
                    <br><br>
                </p>

                <!-- Área de assinaturas -->
                <div class="assinatura-area">
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">AUTORIDADE POLICIAL:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">TESTEMUNHA:</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">&nbsp;</p>
                    <p style="line-height: 1.4; margin: 0.1em 0; padding: 0;">TESTEMUNHA:</p>
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
        hora_fato: <?php echo json_encode(exibirDado($dadosArray, 'hora_fato')); ?>,
        end_fato: <?php echo json_encode(exibirDado($dadosArray, 'end_fato')); ?>,
        nmandado: <?php echo json_encode(exibirDado($dadosArray, 'nmandado')); ?>,
        datamandado: <?php echo json_encode(exibirDado($dadosArray, 'datamandado')); ?>,

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
    <script src="{{ asset('js/pages/mp/autocircunstanciado.js') }}?v=<?php echo time(); ?>"></script>

</body>
</html>

