<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ROL DE TESTEMUNHAS - Editor Profissional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/formularios.css') }}" rel="stylesheet">
</head>
<body class="body-declaracao">
    <div class="editor-wrapper">
        <div class="editor-header">
            <h1 class="editor-title">
                <i class="fas fa-file-contract"></i>
                Editor de ROL DE TESTEMUNHAS
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
                <p style="text-align: center; background-color: #e0e0e0; padding: 5px; font-weight: bold; border: 1px solid #000; font-family: 'Arial Black', Gadget, sans-serif; font-size: 16pt;">ROL DE TESTEMUNHAS / INFORMANTES</p>
                <p><strong>Tombamento: nº {{ $dadosArray['ip'] ?? 'NÃO INFORMADO' }}</strong></p>
                <p><br></p>
                @php
                    $contador = 0;
                    $chavesPessoas = [];
                    $dadosArray = $dadosArray ?? [];
                    foreach (array_keys($dadosArray) as $key) {
                        if (preg_match('/^testemunha\d+$/', $key)) {
                            $chavesPessoas[] = $key;
                        }
                    }
                    natsort($chavesPessoas);
                @endphp

                @foreach ($chavesPessoas as $chave)
                    @php
                        $pessoa = $dadosArray[$chave];
                        $nome = $pessoa['nome'] ?? '';
                    @endphp
                    @if ($nome && $nome !== 'NÃO INFORMADO')
                        @php $contador++; @endphp
                        <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0;">
                            <strong>NOME:</strong> {{ mb_strtoupper($pessoa['nome'] ?? 'NÃO INFORMADO', 'UTF-8') }}, 
                            <strong>ALCUNHA:</strong> {{ mb_strtoupper($pessoa['alcunha'] ?? 'NÃO INFORMADO', 'UTF-8') }}, 
                            <strong>NASCIMENTO:</strong> {{ $pessoa['nascimento'] ?? 'NÃO INFORMADO' }}, 
                            <strong>IDADE:</strong> {{ $pessoa['idade'] ?? 'NÃO INFORMADO' }}, 
                            <strong>ESTADO CIVIL:</strong> {{ mb_strtoupper($pessoa['estcivil'] ?? 'NÃO INFORMADO', 'UTF-8') }}, 
                            <strong>NATURALIDADE:</strong> {{ mb_strtoupper($pessoa['naturalidade'] ?? 'NÃO INFORMADO', 'UTF-8') }}, 
                            <strong>RG:</strong> {{ $pessoa['rg'] ?? 'NÃO INFORMADO' }}, 
                            <strong>CPF:</strong> {{ $pessoa['cpf'] ?? 'NÃO INFORMADO' }}, 
                            <strong>PROFISSÃO:</strong> {{ mb_strtoupper($pessoa['profissao'] ?? 'NÃO INFORMADO', 'UTF-8') }}, 
                            <strong>INSTRUÇÃO:</strong> {{ mb_strtoupper($pessoa['instrucao'] ?? 'NÃO INFORMADO', 'UTF-8') }}, 
                            <strong>TELEFONE:</strong> {{ $pessoa['telefone'] ?? 'NÃO INFORMADO' }}, 
                            <strong>MÃE:</strong> {{ mb_strtoupper($pessoa['mae'] ?? 'NÃO INFORMADO', 'UTF-8') }}, 
                            <strong>PAI:</strong> {{ mb_strtoupper($pessoa['pai'] ?? 'NÃO INFORMADO', 'UTF-8') }}, 
                            <strong>ENDEREÇO:</strong> {{ mb_strtoupper($pessoa['endereco'] ?? 'NÃO INFORMADO', 'UTF-8') }};
                        </p>
                        <p><br></p>
                    @endif
                @endforeach

                @if ($contador === 0)
                    <p><em>Nenhuma testemunha selecionada para este procedimento.</em></p>
                @endif

                <p><br></p>
                <p style="text-align: center;"><strong>{{ $dadosArray['cidade'] ?? 'Afogados da Ingazeira' }}, {{ $dadosArray['data_comp'] ?? ($dadosArray['data_ext'] ?? 'DATA') }}.</strong></p>
                <p><br></p>
                <p style="text-align: center;"><strong>{{ $dadosArray['escrivao'] ?? 'ESCRIVÃO' }}</strong></p>
                <p style="text-align: center;">Escrivão(ã) de Polícia</p>
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
    <script src="{{ asset('js/pages/pecas/RoldeTestemunhas.js') }}"></script>
</body>
</html>
