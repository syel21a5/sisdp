<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Termo de Avaliação - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor da PORTARIA do TERMO DE AVALIAÇÃO
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
                <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                    <strong style="font-size: 20pt;">PORTARIA</strong>
                </p>

                <p><br></p>

                <p style="text-align: justify;">
                    <span>O(A) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, Delegado(a) de Polícia, usando de suas atribuições legais, e, havendo necessidade de proceder a <strong>AVALIAÇÃO DE OBJETOS</strong>, nomeia como Peritos: <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, ambos policiais civis, os quais deverão prestar o compromisso legal de estilo:</span>
                </p>

                <p style="text-align: center;"><strong>C u m p r a - s e</strong></p>

                <p style="text-align: center;">
                    {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'DATA') }}.
                </p>

                <p><br></p>

                <p style="text-align: center;">
                    <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                    Delegado(a) de Polícia
                </p>

                <p><br></p>

                <p style="text-align: center;"><strong><u>TERMO DE COMPROMISSO</u></strong></p>

                <p style="text-align: justify;">
                    <span>Ao(s) <strong>{{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}</strong>, nesta cidade de {{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, e no Cartório desta Delegacia de Polícia, onde presente se encontrava o(a) Bel(a). <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong>, respectivo Delegado(a), comigo escrivão no final assinado, aí compareceram os PERITOS nomeados <strong>{{ $dadosArray['policial_1'] ?? 'NÃO INFORMADO' }}</strong> e <strong>{{ $dadosArray['policial_2'] ?? 'NÃO INFORMADO' }}</strong>, <strong>a quem a Autoridade deferiu o compromisso legal de bem e fielmente desempenharem o encargo, descrevendo com verdade, sem dolo ou malícia,</strong> o que encontrarem e os encarregou de procederem a <strong>AVALIAÇÃO DE OBJETOS</strong> do procedimento policial em questão. E como aceitassem o encargo, mandou a Autoridade encerrar o presente Auto que assina com os Peritos e comigo Escrivão que digitei.</span>
                </p>

                <p><br></p>

                <div class="assinatura-area">
                    <p style="border-top: 1px solid #000; padding-top: 5px;"><strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong></p>
                    <p>AUTORIDADE POLICIAL</p>
                    <p><br></p>
                    <p>PERITO</p>
                    <p><br></p>
                    <p>PERITO</p>
                    <p><br></p>
                    <p>ESCRIVÃO(Ã)</p>
                    <p style="border-top: 1px solid #000; padding-top: 5px;"><strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong></p>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pecas/Avaliacao.js') }}"></script>
</body>
</html>

    <!-- JavaScript principal -->
    <script src="{{ asset('js/pages/pecas/Avaliacao.js') }}"></script>
</body>
</html>


