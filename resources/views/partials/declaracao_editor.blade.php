<div class="print-header" style="text-align: center; padding: 10px 0; margin-bottom: 15px; border-bottom: 1px solid #888;">
    <!-- Linha principal com brasões e texto -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0;">
        <!-- Brasão de PE à esquerda -->
        <img src="{{ asset('images/b_PE.jpg') }}" style="width: 100px; height: 100px; object-fit: contain; margin: -5px 10px 0 10px;">
        <!-- Texto central -->
        <div style="flex: 1; text-align: center;">
            <div style="font-weight: bold; font-size: 12.5pt; margin-bottom: 3px;">
                POLÍCIA CIVIL DE PERNAMBUCO - PCPE
            </div>
            <div style="font-size: 10.5pt; margin-bottom: 2px;">
                Diretoria Integrada do Interior - 2 da Policia Civil – DINTER - 2
            </div>
            <div style="font-size: 10.5pt; margin-bottom: 2px;">
                Gerência de Controle Operacional do Interior - 2 – GCOI - 2
            </div>
            <div style="font-size: 10.5pt; margin-top: 2px;">
                20ª Delegacia Seccional de Polícia – Afogados da Ingazeira – 20ª DESEC
            </div>
        </div>
        <!-- Brasão da Polícia Civil à direita -->
        <img src="{{ asset('images/b_PCPE.png') }}" style="width: 100px; height: 100px; object-fit: contain; margin: -5px 10px 0 10px;">
    </div>
    <!-- Linha adicional com delegacia/cidade -->
    <div style="font-size: 11.5pt; margin-top: 5px; font-weight: bold; text-align: center;">
        {{ !empty($dadosArray['delegacia']) ? $dadosArray['delegacia'] : 'NÃO INFORMADO' }} –
        {{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}
    </div>
</div>

<div id="editor" class="preservar-espacamento">
    <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">
        <strong style="font-size: 20pt;">TERMO DE DECLARAÇÃO</strong>
    </p>
    <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">
        <strong style="font-size: 14pt;">{{ !empty($dadosArray['nome']) ? $dadosArray['nome'] : 'NÃO INFORMADO' }}</strong>
    </p>
    <p style="line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>
    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">
        Aos <strong>{{ !empty($dadosArray['data']) ? $dadosArray['data'] : 'NÃO INFORMADO' }}</strong>, nesta Cidade de
        <strong>{{ !empty($dadosArray['cidade']) ? $dadosArray['cidade'] : 'NÃO INFORMADO' }}</strong>, Estado de Pernambuco,
        no Cartório desta Delegacia de Polícia, onde presente se encontrava o (a) Bel. (a)
        <strong>{{ !empty($dadosArray['delegado']) ? $dadosArray['delegado'] : 'NÃO INFORMADO' }}</strong>, respectivo (a) Delegado (a),
        comigo, Escrivão(ã) <strong>{{ !empty($dadosArray['escrivao']) ? $dadosArray['escrivao'] : 'NÃO INFORMADO' }}</strong> de seu cargo,
        ao final assinado, aí compareceu:
    </p>
    <p style="line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>
    <div style="line-height: 1.6; margin: 0.2em 0; padding: 0; text-align: left;">
        <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">
            <strong>NOME</strong>: {{ !empty($dadosArray['nome']) ? $dadosArray['nome'] : 'NÃO INFORMADO' }},
            <strong>ALCUNHA</strong>: {{ !empty($dadosArray['alcunha']) ? $dadosArray['alcunha'] : 'NÃO INFORMADO' }},
            <strong>NASCIMENTO</strong>: {{ !empty($dadosArray['nascimento']) ? $dadosArray['nascimento'] : 'NÃO INFORMADO' }},
            <strong>IDADE</strong>: {{ !empty($dadosArray['idade']) ? $dadosArray['idade'] : 'NÃO INFORMADO' }},
            <strong>ESTADO CIVIL</strong>: {{ !empty($dadosArray['estcivil']) ? $dadosArray['estcivil'] : 'NÃO INFORMADO' }},
            <strong>NATURALIDADE</strong>: {{ !empty($dadosArray['naturalidade']) ? $dadosArray['naturalidade'] : 'NÃO INFORMADO' }},
            <strong>RG</strong>: {{ !empty($dadosArray['rg']) ? $dadosArray['rg'] : 'NÃO INFORMADO' }},
            <strong>CPF</strong>: {{ !empty($dadosArray['cpf']) ? $dadosArray['cpf'] : 'NÃO INFORMADO' }},
            <strong>PROFISSÃO</strong>: {{ !empty($dadosArray['profissao']) ? $dadosArray['profissao'] : 'NÃO INFORMADO' }},
            <strong>INSTRUÇÃO</strong>: {{ !empty($dadosArray['instrucao']) ? $dadosArray['instrucao'] : 'NÃO INFORMADO' }},
            <strong>TELEFONE</strong>: {{ !empty($dadosArray['telefone']) ? $dadosArray['telefone'] : 'NÃO INFORMADO' }},
            <strong>MÃE</strong>: {{ !empty($dadosArray['mae']) ? $dadosArray['mae'] : 'NÃO INFORMADO' }},
            <strong>PAI</strong>: {{ !empty($dadosArray['pai']) ? $dadosArray['pai'] : 'NÃO INFORMADO' }},
            <strong>ENDEREÇO</strong>: {{ !empty($dadosArray['endereco']) ? $dadosArray['endereco'] : 'NÃO INFORMADO' }}.
        </p>
    </div>
    <p style="line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>
    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">
        Inquirida pela Autoridade Policial acerca dos fatos, disse o que se segue: QUE,
    </p>
    <p style="line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>
    <p style="text-align: center; line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">
        <span style="background-color: cyan;">ESCREVER AQUI AS DECLARAÇÕES</span>
    </p>
    <p style="line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>
    <p style="text-align: justify; line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">
        . Nada mais havendo a acrescentar, lido e achado conforme, o presente termo é lavrado em referência ao Boletim de Ocorrência nº
        <strong>{{ !empty($dadosArray['boe']) ? $dadosArray['boe'] : 'NÃO INFORMADO' }}</strong>,
        e vai devidamente assinado pela Autoridade Policial, pela vítima, e por moi escrivão que o digitei.
    </p>
    <p style="line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>
    <p style="line-height: 1.6; margin: 0.2em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>

    <!-- Área de assinaturas -->
    <div class="assinatura-area">
        <p style="line-height: 1.4; margin: 0.1em 0; padding: 0; font-family: Arial, sans-serif;">AUTORIDADE POLICIAL:</p>
        <p style="line-height: 1.4; margin: 0.1em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>
        <p style="line-height: 1.4; margin: 0.1em 0; padding: 0; font-family: Arial, sans-serif;">DECLARANTE:</p>
        <p style="line-height: 1.4; margin: 0.1em 0; padding: 0; font-family: Arial, sans-serif;">&nbsp;</p>
        <p style="line-height: 1.4; margin: 0.1em 0; padding: 0; font-family: Arial, sans-serif;">ESCRIVÃO(Ã):</p>
    </div>
</div>


