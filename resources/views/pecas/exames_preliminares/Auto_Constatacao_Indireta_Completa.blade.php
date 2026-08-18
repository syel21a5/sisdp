<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DANOS INDIRETA - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor da PORTARIA de CONSTATAÇÃO INDIRETA
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
                            {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADO' }} –
                            {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}
                        </div>
                    </div>
                    <img src="{{ asset('images/b_PCPE.png') }}" alt="Brasão da Polícia Civil">
                </div>
            </div>
        </div>



        <!-- ÁREA DO EDITOR -->
        <div class="editor-area">
            <div id="editor" class="preservar-espacamento">

                <p style="text-align: center; font-weight: bold; font-size: 14pt;">P O R T A R I A</p>

                <p style="text-align: justify;">
                    O(A) Bel(a). <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, usando de suas atribuições legais, e, havendo necessidade de proceder ao EXAME DE CONSTATAÇÃO DE DANOS E AVALIAÇÃO INDIRETA DOS OBJETOS, nomeia como Peritos: <strong>{{ !empty($dadosArray['policial_1']) ? $dadosArray['policial_1'] : 'NÃO INFORMADO' }}</strong> e <strong>{{ !empty($dadosArray['policial_2']) ? $dadosArray['policial_2'] : 'NÃO INFORMADO' }}</strong>, ambos policiais civis, os quais deverão prestar o compromisso legal de estilo.
                </p>

                <p style="text-align: center; font-weight: bold; margin: 5px 0 0 0;">C u m p r a – s e</p>

                <p style="text-align: center; margin: 5px 0 0 0;">
                    Afogados da Ingazeira, <strong>{{ !empty($dadosArray['data_comp']) ? $dadosArray['data_comp'] : 'DATA DO FATO' }}</strong>.
                </p>
                <p><br></p>
                <div class="assinatura-area" style="margin-top: 10px; line-height: 1.3;">
                    <p style="text-align: center;">
                        <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                        Delegado(a) de Polícia
                    </p>
                </div>
                <div style="height: 15px;"></div>
                <p style="text-align: center; font-weight: bold; font-size: 14pt;">TERMO DE COMPROMISSO</p>

                <p style="text-align: justify;">
                    Ao(s) <strong>{{ !empty($dadosArray['data_ext']) ? $dadosArray['data_ext'] : 'DATA POR EXTENSO' }}</strong>, nesta cidade de Afogados da Ingazeira, e no Cartório desta Delegacia de Polícia, onde presente se encontrava o(a) Bel(a). <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>, respectivo Delegado(a), comigo escrivão no final assinado, aí compareceram os PERITOS nomeados <strong>{{ !empty($dadosArray['policial_1']) ? $dadosArray['policial_1'] : 'NÃO INFORMADO' }}</strong> e <strong>{{ !empty($dadosArray['policial_2']) ? $dadosArray['policial_2'] : 'NÃO INFORMADO' }}</strong>, a quem a Autoridade deferiu o compromisso legal de bem e fielmente desempenharem o encargo, descrevendo com verdade, sem dolo ou malícia, o que encontrarem e os encarregou de procederem ao EXAME DE CONSTATAÇÃO DE DANOS E AVALIAÇÃO INDIRETA DOS OBJETOS, referentes ao procedimento policial em questão. E como aceitassem o encargo, mandou a Autoridade encerrar o presente Auto que assina com os Peritos e comigo Escrivão que digitei.
                </p>
                <p>&nbsp;</p>

                 <!-- Assinaturas do Termo (Layout Vertical) -->
                 <div class="assinatura-area" style="margin-top: 10px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 35px;">
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                AUTORIDADE POLICIAL
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 35px;">
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                ESCRIVÃO(Ã)
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; border: none;">
                                <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                            <td style="width: 50%; text-align: center; border: none;">
                                <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                        </tr>
                    </table>
                </div>
            



<p class="page-break-marker" style="page-break-before: always; break-before: page; height: 0; margin: 0; padding: 0; visibility: hidden;">--- QUEBRA DE PÁGINA ---</p>




                <p style="text-align: center; line-height: 1.6; margin: 20px 0 20px 0; padding: 0;">
                    <strong style="font-size: 14pt;">AUTO DE CONSTATAÇÃO DE DANOS E AVALIAÇÃO INDIRETA DOS OBJETOS</strong>
                </p>

                

                <p style="text-align: justify;">
                    Ao(s) <strong>{{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}</strong>, nesta cidade de {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, e no Cartório da Delegacia de Polícia, onde presente achava-se o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, comigo escrivão ao final assinado, tendo a autoridade policial nomeado como Peritos as pessoas de <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, ambos policiais civis, deferindo-lhes o compromisso legal, de bem e fielmente, sem dolo nem malícia, desempenharem a missão, determinado os exames necessários a fim de que procedam ao <strong>EXAME DE CONSTATAÇÃO DE DANOS E AVALIAÇÃO INDIRETA DOS OBJETOS</strong>, referidos nos autos, conforme boletim de ocorrência nº <strong>{{ $dadosArray['boe'] ?? 'NÃO INFORMADO' }}</strong>.
                </p>

                

                <div style="height: 12px;"></div>
                <p style="text-align: justify; line-height: 1.1; {{ empty($dadosArray['apreensao']) ? 'background-color: #ffff00;' : '' }}">
                    <strong>{!! !empty($dadosArray['apreensao']) ? preg_replace("/\r?\n/", "<br><span style=\"display:block; height: 8px;\"></span>", e($dadosArray['apreensao'])) : 'DESCREVER LOCAL E OBJETOS A SER AVALIADO INDIRETAMENTE;' !!}</strong>
                </p>

                <div style="height: 12px;"></div>
                <p style="text-align: justify;">
                    Os peritos se dirigiram até o local indicado, onde constataram que <span style="background-color: #00ffff;">EXEMPLO: a mala do referido veículo, constataram que se encontrava danificado: as portas do guarda roupas do quarto do casal se encontrava quebradas e caídas no quarto e o aparelho celular apresentava a tela e a capa com grandes arranhões, estando, porém, o aparelho funcionando normalmente. QUE, o dano foi avaliado no valor aproximado de R$ 150,00 (cento e cinquenta reais).</span>
                </p>

                <div style="height: 12px;"></div>
                <p style="text-align: justify;">
                    Nada mais havendo a acrescentar, mandou a Autoridade, encerrar o presente auto, o qual depois de lido e achado conforme, segue devidamente assinado, pela Autoridade, pelos peritos e por mim Escrivão, que o digitei.
                </p>

                <p>&nbsp;</p>
                <div class="assinatura-area" style="margin-top: 10px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 35px;">
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                AUTORIDADE POLICIAL
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 35px;">
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                ESCRIVÃO(Ã)
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; border: none;">
                                <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                            <td style="width: 50%; text-align: center; border: none;">
                                <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong><br>
                                PERITO
                            </td>
                        </tr>
                    </table>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}?v={{ time() }}"></script>
    
    

    <!-- Dados para JavaScript -->
    <script>
        // ✅ DEFINIR ROTA E TIPO PARA O JAVASCRIPT
        // Essa variável sobrescreve a lógica padrão do ExameDanos.js
        window.rotaPdf = "{{ route('constatacao.indireta.portaria.pdf') }}";
        window.tipoDocumentoGlobal = 'portaria';

        var dadosDelegacia = '{!! isset($dadosArray["delegacia"]) ? addslashes($dadosArray["delegacia"]) : "NÃO INFORMADO" !!}';
        var dadosCidade = '{!! isset($dadosArray["cidade"]) ? addslashes($dadosArray["cidade"]) : "NÃO INFORMADO" !!}';
        var dadosDelegado = '{!! isset($dadosArray["delegado"]) ? addslashes($dadosArray["delegado"]) : "NÃO INFORMADO" !!}';
        var dadosEscrivao = '{!! isset($dadosArray["escrivao"]) ? addslashes($dadosArray["escrivao"]) : "NÃO INFORMADO" !!}';
        var dadosPolicial1 = '{!! isset($dadosArray["policial_1"]) ? addslashes($dadosArray["policial_1"]) : "NÃO INFORMADO" !!}';
        var dadosPolicial2 = '{!! isset($dadosArray["policial_2"]) ? addslashes($dadosArray["policial_2"]) : "NÃO INFORMADO" !!}';
        var dadosDataComp = '{!! isset($dadosArray["data_comp"]) ? addslashes($dadosArray["data_comp"]) : "NÃO INFORMADO" !!}';
        var dadosNome = '{!! isset($dadosArray["nome"]) ? addslashes($dadosArray["nome"]) : "" !!}';
        var dadosBoe = '{!! isset($dadosArray["boe"]) ? addslashes($dadosArray["boe"]) : "" !!}';
        var dadosApreensao = '{!! isset($dadosArray["apreensao"]) ? addslashes($dadosArray["apreensao"]) : "" !!}';
        var dadosDataExt = '{!! isset($dadosArray["data_ext"]) ? addslashes($dadosArray["data_ext"]) : "NÃO INFORMADO" !!}';

        window.dadosParaImpressao = {
            delegacia: dadosDelegacia,
            cidade: dadosCidade,
            delegado: dadosDelegado,
            escrivao: dadosEscrivao,
            policial_1: dadosPolicial1,
            policial_2: dadosPolicial2,
            data_comp: dadosDataComp,
            nome: dadosNome,
            boe: dadosBoe,
            apreensao: dadosApreensao,
            data_ext: dadosDataExt
        };

        console.log('Dados carregados para Constatação Indireta (Portaria):', window.dadosParaImpressao);
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pecas/ExameDanos.js') }}?v={{ time() }}"></script>
</body>
</html>








