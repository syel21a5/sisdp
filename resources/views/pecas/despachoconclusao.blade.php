<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DESPACHO DE CONCLUSÃO - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor do DESPACHO DE CONCLUSÃO
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
                <p style="text-align: center; background-color: #d0d0d0; padding: 6px 10px; font-weight: bold; border: 1px solid #000;">DESPACHO</p>
                <p><br></p>
                <p style="text-align: justify;">Senhor Escrivão(ã), junte aos autos a(s) peça(s) abaixo relacionada(s):</p>
                <ol>
                    <li>Solicitação dos antecedentes criminais ao IITB;</li>
                    <li>Ofício encaminhando boletim individual ao IITB;</li>
                    <li>Boletim individual do(a) envolvido(a);</li>
                    <li>Rol de testemunhas;</li>
                    <li>Relatório conclusivo de procedimento policial;</li>
                    <li>Termo de remessa.</li>
                </ol>
                <p style="text-align: justify;">Em seguida, remeta-se os Autos à Justiça para as demais providências legais pertinentes.</p>
                <p style="text-align: center;"><strong>CUMPRA-SE</strong></p>
                <p style="text-align: center;">{{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, {{ $dadosArray['data_ext'] ?? 'DATA' }}</p>
                <p style="text-align: center;"><strong>{{ $dadosArray['delegado'] ?? 'DELEGADO' }}</strong></p>
                <p style="text-align: center;">Delegado(a) de Polícia</p>

                <div style="page-break-before: always;"></div>

                <p style="text-align: center; background-color: #d0d0d0; padding: 6px 10px; font-weight: bold; border: 1px solid #000;">DATA</p>
                <p style="text-align: center;">Ao(s) {{ $dadosArray['data_ext'] ?? 'DATA' }}, recebo estes autos do(a) Delegado(a)</p>
                <p style="text-align: center;">que preside o presente Inquérito. Do que, para constar, lavro este termo.</p>
                <p style="text-align: center;">Eu, _________________, Escrivão(ã) que o digitei.</p>

                <p style="text-align: center; background-color: #d0d0d0; padding: 6px 10px; font-weight: bold; border: 1px solid #000;">JUNTADA</p>
                <p style="text-align: center;">Ao(s) {{ $dadosArray['data_ext'] ?? 'DATA' }}, faço juntada dos documentos</p>
                <p style="text-align: center;">que adiante se seguem. Do que, para constar, lavro este termo.</p>
                <p style="text-align: center;">Eu, _________________, Escrivão(ã) que o digitei.</p>

                <p style="text-align: center; background-color: #d0d0d0; padding: 6px 10px; font-weight: bold; border: 1px solid #000;">CERTIDÃO</p>
                <p style="text-align: center;">Certifico, que nesta data, dei fiel cumprimento ao</p>
                <p style="text-align: center;">despacho retro do Delegado(a) que preside o presente Inquérito, aonde me reporto e DOU FÉ.</p>
                <p style="text-align: center;">Eu, _________________, Escrivão(ã) que o digitei.</p>

                <div style="page-break-before: always;"></div>

                <p style="text-align: center; background-color: #d0d0d0; padding: 6px 10px; font-weight: bold; border: 1px solid #000;">CONCLUSÃO</p>
                <p style="text-align: center;">Ao(s) {{ $dadosArray['data_ext'] ?? 'DATA' }}, faço estes autos conclusos</p>
                <p style="text-align: center;">ao(a) Delegado(a) desta {{ $dadosArray['delegacia'] ?? 'DELEGACIA' }}, do que para constar, lavro este termo.</p>
                <p style="text-align: center;">Eu, __________________ Escrivão(ã) que o digitei.</p>
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
    <script src="{{ asset('js/pages/pecas/DespachoConclusao.js') }}"></script>
</body>
</html>
