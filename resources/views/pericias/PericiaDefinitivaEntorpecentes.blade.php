<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perícia Definitiva em Entorpecentes - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor: PERÍCIA DEFINITIVA EM ENTORPECENTES
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
                
                <p style="text-align: right;">
                    {{ $dadosArray['cidade'] ?? 'NÃO INFORMADO' }}, 
                    {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'NÃO INFORMADO') }}
                </p>

                <p><br /></p>

                <p style="line-height: 1.5;">
                    <strong>Ofício nº {{ $numeroOficio ?? '____' }}</strong><br>
                    <strong>BOE:</strong> {{ $dadosArray['boe'] ?? '____' }}<br>
                    <strong>SEI nº:</strong>
                </p>

                <p><br /></p>

                <p style="line-height: 1.5;">
                    <strong>ILMO. SR. GESTOR</strong><br>
                    <strong>UNIDADE REGIONAL DE POLÍCIA CIENTÍFICA DO SERTÃO DO PAJEÚ – URPOC</strong><br>
                    <strong>AFOGADOS DA INGAZEIRA (SDS - GGPOC - GURPOCSP)</strong>
                </p>

                <p><br /></p>

                <p style="text-align: justify; line-height: 1.5;">
                    Senhor(a) Gestor(a),
                
                @php
                    $drogasArr = !empty($dadosArray['lista_drogas']) ? $dadosArray['lista_drogas'] : [];
                    
                    $descricoes = [];
                    $quesitosHtml = '';

                    foreach ($drogasArr as $droga) {
                        $drogaNome = is_array($droga) ? ($droga['nome'] ?? '') : $droga;
                        $drogaQtd = is_array($droga) ? ($droga['qtd'] ?? '______') : '______';
                        $drogaUpper = strtoupper($drogaNome);
                        $drogaQtd = trim($drogaQtd);

                        if ($drogaUpper === 'MACONHA') {
                            $descricoes[] = 'aproximadamente <strong>' . $drogaQtd . '</strong> de material vegetal, apresentando características semelhantes às da erva de coloração castanha esverdeada, denominada <em>Cannabis sativa Linneu</em>, vulgarmente conhecida como <strong>MACONHA</strong>';
                            
                            $quesitosHtml .= '<p style="margin-bottom: 5px;"><strong>QUANTO À SUBSTÂNCIA (MACONHA):</strong></p>';
                            $quesitosHtml .= '<div style="margin-left: 20px; margin-bottom: 15px;">';
                            $quesitosHtml .= '1º) A substância apresentada para perícia, é maconha?<br>';
                            $quesitosHtml .= '2º) É o material proscrito no Brasil?<br>';
                            $quesitosHtml .= '3º) Demais esclarecimentos necessários.</div>';
                        }
                        elseif ($drogaUpper === 'COCAÍNA' || $drogaUpper === 'COCAINA') {
                            $descricoes[] = 'aproximadamente <strong>' . $drogaQtd . '</strong> de material em pó de cor branca, que se presume ser a droga conhecida como <strong>COCAÍNA</strong>';
                            
                            $quesitosHtml .= '<p style="margin-bottom: 5px;"><strong>QUANTO À SUBSTÂNCIA (COCAÍNA):</strong></p>';
                            $quesitosHtml .= '<div style="margin-left: 20px; margin-bottom: 15px;">';
                            $quesitosHtml .= '1º) A substância apresentada para perícia, é cocaína?<br>';
                            $quesitosHtml .= '2º) É o material proscrito no Brasil?<br>';
                            $quesitosHtml .= '3º) Qual a sua história, seu conceito, seu nome científico e sua fórmula química?<br>';
                            $quesitosHtml .= '4º) Especialmente, é manifesto o seu princípio ativo, capacitando-a a causar dependência física e/ou psíquica?<br>';
                            $quesitosHtml .= '5º) Demais esclarecimentos necessários.</div>';
                        }
                        elseif ($drogaUpper === 'CRACK') {
                            $descricoes[] = $drogaQtd . ' de substância sólida de cor amarelada, apresentando características e odor da droga popularmente conhecida como <strong>CRACK</strong>';
                            
                            $quesitosHtml .= '<p style="margin-bottom: 5px;"><strong>QUANTO À SUBSTÂNCIA (CRACK):</strong></p>';
                            $quesitosHtml .= '<div style="margin-left: 20px; margin-bottom: 15px;">';
                            $quesitosHtml .= '1º) A substância apresentada para perícia, é crack?<br>';
                            $quesitosHtml .= '2º) Qual sua história, seu conceito, seu nome científico e sua fórmula química?<br>';
                            $quesitosHtml .= '3º) É o material proscrito no Brasil?<br>';
                            $quesitosHtml .= '4º) Especialmente é manifesto o seu princípio ativo, capacitando-a a causar dependência física e/ou psíquica?<br>';
                            $quesitosHtml .= '5º) O "Crack" acha-se classificado como substância entorpecente, de acordo com o Decreto Lei Federal de nº. 891/38 de 25 de novembro de 1938?<br>';
                            $quesitosHtml .= '6º) Demais esclarecimentos necessários.</div>';
                        }
                        else {
                            $descricoes[] = 'aproximadamente <strong>' . $drogaQtd . '</strong> de substância análoga a <strong>' . $drogaUpper . '</strong>';
                            
                            $quesitosHtml .= '<p style="margin-bottom: 5px;"><strong>QUANTO À SUBSTÂNCIA ('. $drogaUpper .'):</strong></p>';
                            $quesitosHtml .= '<div style="margin-left: 20px; margin-bottom: 15px;">';
                            $quesitosHtml .= '1º) A substância apresentada para perícia é de fato '. $drogaUpper .'?<br>';
                            $quesitosHtml .= '2º) É o material proscrito no Brasil?<br>';
                            $quesitosHtml .= '3º) Qual a sua história, seu conceito, seu nome científico e sua fórmula química?<br>';
                            $quesitosHtml .= '4º) Especialmente, é manifesto o seu princípio ativo, capacitando-a a causar dependência física e/ou psíquica?<br>';
                            $quesitosHtml .= '5º) Demais esclarecimentos necessários.</div>';
                        }
                    }
                    
                    // Pegar os autores caso seja necessário (normalmente apreendido em poder de alguém)
                    if (!empty($dadosArray['autuados_manuais'])) {
                        $textoAutores = $dadosArray['autuados_manuais'];
                    } else {
                        $autores = !empty($dadosArray['lista_autores']) ? array_column($dadosArray['lista_autores'], 'nome') : [];
                        $textoAutores = count($autores) > 0 ? implode(' e ', $autores) : '[AUTUADOS]';
                    }
                @endphp

                <div style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 10px;">
                    Encaminho a V. Sa., com a finalidade de ser realizado o <strong>EXAME DEFINITIVO DE DROGA</strong>, de conformidade com o que preceitua o § 2º do Art. 50 da Lei Federal nº. 11.343/2006, um envelope CONTENDO:
                    @if(count($descricoes) >= 1)
                        <br>
                        <ul style="list-style-type: none; padding-left: 50px; margin-top: 10px; margin-bottom: 15px; text-indent: 0;">
                        @foreach($descricoes as $index => $desc)
                            <li style="margin-bottom: 8px; text-align: justify; line-height: 1.5; position: relative;">
                                <span style="position: absolute; left: -15px;">&#8226;</span>{!! $desc !!}{{ $index === count($descricoes) - 1 ? '.' : ';' }}
                            </li>
                        @endforeach
                        </ul>
                    @else
                        _______ .
                    @endif
                </div>
                <div style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Substância(s) apreendida(s) em poder de: <strong>{{ $textoAutores }}</strong>, fato que ensejou o respectivo procedimento policial, devendo os senhores Peritos responder aos seguintes quesitos:
                </div>
                
                <div style="text-align: justify; line-height: 1.5; margin-bottom: 15px;">
                    {!! $quesitosHtml !!}
                </div>
                
                <div style="text-align: justify; line-height: 1.5; text-indent: 40px;">
                    Esclareço, ainda, que o competente Laudo deverá ser encaminhado a esta Delegacia de Polícia ({{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : '____' }}).
                </div>

                <p><br /></p>

                <p style="text-align: center; line-height: 1.5;">
                    Atenciosamente,
                </p>
                
                <p><br /></p>
                <p><br /></p>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.4;">
                    <div style="width: 50%; margin: 0 auto; text-align: center; border-top: 1px solid #000; padding-top: 5px;">
                        <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                        Delegado(a) de Polícia
                    </div>
                </div>

            </div>

            <div class="editor-stats">
                <div class="stat-item">
                    <i class="fas fa-keyboard"></i>
                    <span id="char-count">0 caracteres</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-file-word"></i>
                    <span id="word-count">0 palavras</span>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}?v={{ time() }}"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <script src="{{ asset('js/pages/pericias/PericiaDefinitivaEntorpecentes.js') }}?v={{ time() }}"></script>
</body>
</html>
