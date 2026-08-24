<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ofício - Perícia de Informática - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-laptop-code"></i>
                Editor: OFÍCIO - PERÍCIA DE INFORMÁTICA
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
                
                <p style="text-align: right; line-height: 1.5; margin-bottom: 20px;">
                    {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'NÃO INFORMADO') }}.
                </p>

                <p style="text-align: left; line-height: 1.5; margin: 10px 0;">
                    <strong>OFÍCIO Nº {{ $dadosArray['num_oficio'] ?? '_____' }}</strong>
                </p>

                <div style="margin-bottom: 30px; line-height: 1.5;">
                    <p style="margin: 0;"><strong>De:</strong> {{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</p>
                    <p style="margin: 0;">Delegado(a) de Polícia Civil</p>
                    <p style="margin: 0;"><strong>{{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'DELEGACIA' }}</strong></p>
                    
                    <p style="margin: 20px 0 0 0;"><strong>Ao:</strong> Ilmo(a). Sr(a). Diretor(a) do Instituto de Criminalística</p>
                </div>

                <p style="text-align: left; line-height: 1.5; margin-bottom: 5px;">
                    <strong>ASSUNTO:</strong> Solicitação de Perícia de Informática (Extração de Dados).
                </p>
                
                <p style="text-align: left; line-height: 1.5; margin-bottom: 30px;">
                    <strong>REF.:</strong> 
                    @if(!empty($dadosArray['boe']) && !empty($dadosArray['ip']))
                        Boletim de Ocorrência nº {{ $dadosArray['boe'] }} / Inquérito Policial nº {{ $dadosArray['ip'] }}
                    @elseif(!empty($dadosArray['boe']))
                        Boletim de Ocorrência nº {{ $dadosArray['boe'] }}
                    @elseif(!empty($dadosArray['ip']))
                        Inquérito Policial nº {{ $dadosArray['ip'] }}
                    @else
                        Boletim de Ocorrência nº ____________________
                    @endif
                </p>

                <p style="text-align: left; line-height: 1.5; margin-bottom: 15px;">
                    Senhor(a) Diretor(a),
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    Cumprimentando-o cordialmente, solicito de V.Sª. determinar que seja realizada a <strong>PERÍCIA DE INFORMÁTICA (EXTRAÇÃO DE DADOS)</strong> no(s) seguinte(s) aparelho(s)/dispositivo(s): <strong>{{ $dadosArray['aparelhos_apreendidos'] ?? '________________________________' }}</strong>, o(s) qual(is) segue(m) anexo(s) a este ofício conforme respectivo Auto de Apresentação e Apreensão.
                </p>

                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 15px;">
                    É importante salientar que seja(m) extraída(s) do(s) referido(s) equipamento(s): <strong>{{ $dadosArray['objetivo_extracao'] ?? '________________________________________________' }}</strong> para fiel esclarecimento do procedimento apuratório em epígrafe.
                </p>
                
                <p style="text-align: justify; line-height: 1.5; text-indent: 40px; margin-bottom: 30px;">
                    Finalmente, tão logo seja concluído o respectivo Laudo Pericial, requeiro que o mesmo seja encaminhado para esta Unidade Policial para a devida juntada aos autos processuais. Apresento-lhe protestos de estima e consideração.
                </p>

                <div class="assinatura-area" style="margin-top: 60px; line-height: 1.3;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 100%; text-align: center; border: none;">
                                ________________________________________<br>
                                <strong>{{ $dadosArray['delegado'] ?? 'NÃO INFORMADO' }}</strong><br>
                                Delegado(a) de Polícia
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="{{ asset('js/DocumentoService.js') }}?v={{ time() }}"></script>

    <!-- Dados para JavaScript -->
    <script>
        window.dadosParaImpressao = @json($dadosArray);
    </script>

    <script src="{{ asset('js/pages/pericias/PericiaInformatica.js') }}?v={{ time() }}"></script>
</body>
</html>
