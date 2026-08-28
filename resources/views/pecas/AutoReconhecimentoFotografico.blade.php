<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Auto de Reconhecimento Fotográfico - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
    <style>
        .foto-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .foto-grid td {
            width: 50%;
            border: 2px dashed #ccc;
            padding: 10px;
            text-align: center;
            vertical-align: top;
            height: 250px;
        }
        .foto-placeholder {
            color: #999;
            font-size: 14px;
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-camera"></i>
                Editor: AUTO DE RECONHECIMENTO FOTOGRÁFICO
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

                <p style="text-align: center; font-size: 18px; line-height: 1.5; margin: 30px 0;">
                    <strong>AUTO DE RECONHECIMENTO FOTOGRÁFICO</strong><br>
                    <strong>{{ mb_strtoupper($dadosArray['nome'] ?? '[NOME]') }} (RECONHECEDOR(A))</strong>
                </p>
                
                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    Ao(s) {{ $dadosArray['data_ext'] ?? 'NÃO INFORMADO' }}, nesta cidade de {{ !empty($dadosArray['cidade']) ? mb_strtoupper($dadosArray['cidade']) : 'NÃO INFORMADO' }}, onde presente se encontrava o(a) Bel(a). {{ $dadosArray['delegado'] ?? '[DELEGADO]' }}, respectivo(a) Delegado(a) de Polícia, comigo, escrivão de seu cargo e ao final assinado, presente as testemunhas: 1ª – {{ !empty($dadosArray['policial_1']) ? mb_strtoupper($dadosArray['policial_1']) : '[NOME DA 1ª TESTEMUNHA]' }} e a 2ª – {{ !empty($dadosArray['policial_2']) ? mb_strtoupper($dadosArray['policial_2']) : '[NOME DA 2ª TESTEMUNHA]' }}, ambos lotados nesta DEPOL da 167ª Circunscrição – Afogados da Ingazeira – PE, aí compareceu:
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    “<strong>{{ mb_strtoupper($dadosArray['nome'] ?? '[NOME]') }}</strong>”, 
                    ALCUNHA: “{{ mb_strtoupper($dadosArray['alcunha'] ?? 'NÃO INFORMADO') }}”, 
                    NASCIMENTO: “{{ $dadosArray['nascimento'] ?? 'NÃO INFORMADO' }}”, 
                    IDADE: “{{ $dadosArray['idade'] ?? 'NÃO INFORMADO' }}”, 
                    ESTADO CIVIL: “{{ mb_strtoupper($dadosArray['estado_civil'] ?? 'NÃO INFORMADO') }}”, 
                    NATURALIDADE: “{{ mb_strtoupper($dadosArray['naturalidade'] ?? 'NÃO INFORMADO') }}”, 
                    RG: “{{ $dadosArray['rg'] ?? 'NÃO INFORMADO' }}”, 
                    CPF: “{{ $dadosArray['cpf'] ?? 'NÃO INFORMADO' }}”, 
                    PROFISSÃO: “{{ mb_strtoupper($dadosArray['profissao'] ?? 'NÃO INFORMADO') }}”, 
                    INSTRUÇÃO: “{{ mb_strtoupper($dadosArray['instrucao'] ?? 'NÃO INFORMADO') }}”, 
                    TELEFONE: “{{ $dadosArray['telefone'] ?? 'NÃO INFORMADO' }}”, 
                    MÃE: “{{ mb_strtoupper($dadosArray['mae'] ?? 'NÃO INFORMADO') }}”, 
                    PAI: “{{ mb_strtoupper($dadosArray['pai'] ?? 'NÃO INFORMADO') }}”, 
                    ENDEREÇO: “{{ mb_strtoupper($dadosArray['endereco'] ?? 'NÃO INFORMADO') }}”.
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px;">
                    O(A) qual, sob o compromisso de honra de dizer a verdade, foi convidado(a) a realizar o reconhecimento fotográfico das imagens abaixo listadas:
                </p>

                <table class="foto-grid">
                    <tr>
                        <td>
                            @if(isset($dadosArray['imagens_selecionadas'][0]))
                                <img src="{{ $dadosArray['imagens_selecionadas'][0] }}" style="max-width: 100%; max-height: 220px; object-fit: contain;">
                            @else
                                <span class="foto-placeholder">IMAGEM 1<br>(Cole ou insira a foto 1 aqui)</span>
                            @endif
                        </td>
                        <td>
                            @if(isset($dadosArray['imagens_selecionadas'][1]))
                                <img src="{{ $dadosArray['imagens_selecionadas'][1] }}" style="max-width: 100%; max-height: 220px; object-fit: contain;">
                            @else
                                <span class="foto-placeholder">IMAGEM 2<br>(Cole ou insira a foto 2 aqui)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>
                            @if(isset($dadosArray['imagens_selecionadas'][2]))
                                <img src="{{ $dadosArray['imagens_selecionadas'][2] }}" style="max-width: 100%; max-height: 220px; object-fit: contain;">
                            @else
                                <span class="foto-placeholder">IMAGEM 3<br>(Cole ou insira a foto 3 aqui)</span>
                            @endif
                        </td>
                        <td>
                            @if(isset($dadosArray['imagens_selecionadas'][3]))
                                <img src="{{ $dadosArray['imagens_selecionadas'][3] }}" style="max-width: 100%; max-height: 220px; object-fit: contain;">
                            @else
                                <span class="foto-placeholder">IMAGEM 4<br>(Cole ou insira a foto 4 aqui)</span>
                            @endif
                        </td>
                    </tr>
                </table>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 20px; background-color: #ffffcc;">
                    Depois de realizar minucioso exame visual, afirma sem sombras de dúvidas que <strong>[DIGITE AQUI QUAL IMAGEM FOI RECONHECIDA E OS DETALHES DO FATO]</strong>.
                </p>

                <p style="text-align: justify; line-height: 1.8; text-indent: 40px; margin-bottom: 50px;">
                    Nada mais havendo, mandou a autoridade encerrar este Auto, que, depois de lido e achado conforme o assina com o(a) Reconhecedor(a), com as testemunhas e comigo Escrivão que o digitei.
                </p>

                <div class="assinatura-area" style="margin-top: 50px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 30px;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Autoridade Policial
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 30px;">
                                ________________________________________<br>
                                <strong>{{ mb_strtoupper($dadosArray['nome'] ?? '[NOME]') }}</strong><br>
                                Reconhecedor(a)
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 30px;">
                                ________________________________________<br>
                                <strong>{{ !empty($dadosArray['policial_1']) ? mb_strtoupper($dadosArray['policial_1']) : '[NOME DA 1ª TESTEMUNHA]' }}</strong><br>
                                1ª Testemunha
                            </td>
                            <td style="width: 50%; text-align: center; border: none; padding-bottom: 30px;">
                                ________________________________________<br>
                                <strong>{{ !empty($dadosArray['policial_2']) ? mb_strtoupper($dadosArray['policial_2']) : '[NOME DA 2ª TESTEMUNHA]' }}</strong><br>
                                2ª Testemunha
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="width: 100%; text-align: center; border: none;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['escrivao'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Escrivão(ã) de Polícia
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}?v={{ time() }}"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <script src="{{ asset('js/pages/pecas/AutoReconhecimentoFotografico.js') }}?v={{ time() }}"></script>
</body>
</html>
