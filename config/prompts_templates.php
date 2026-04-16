<?php

/**
 * Templates de Prompts para Geração de Depoimentos
 * 
 * Convertidos dos arquivos .docx da pasta sisdp_prompts/
 * Cada template aceita variáveis no formato {{VARIAVEL}} que serão
 * substituídas pelo PromptGeneratorController.
 * 
 * Variáveis disponíveis:
 *   {{NOME}}              - Nome do envolvido
 *   {{CPF}}               - CPF formatado
 *   {{RG}}                - RG
 *   {{MAE}}               - Nome da mãe
 *   {{PAI}}               - Nome do pai
 *   {{NASCIMENTO}}        - Data de nascimento
 *   {{NATURALIDADE}}      - Naturalidade
 *   {{PROFISSAO}}         - Profissão
 *   {{ENDERECO}}          - Endereço do envolvido
 *   {{DATA_FATO}}         - Data do fato
 *   {{HORA_FATO}}         - Hora do fato
 *   {{LOCAL_FATO}}        - Endereço do fato
 *   {{INCIDENCIA_PENAL}}  - Incidência penal / Natureza
 *   {{BOE_NUMERO}}        - Número do BOE
 *   {{DELEGACIA}}         - Delegacia
 *   {{DELEGADO}}          - Nome do delegado
 *   {{ESCRIVAO}}          - Nome do escrivão
 *   {{HISTORICO_BOE}}     - Texto bruto completo do BOE
 *   {{LISTA_ENVOLVIDOS}}  - Lista de todos os envolvidos com papéis
 *   {{POLICIAL_1}}        - Nome do policial 1
 *   {{POLICIAL_2}}        - Nome do policial 2
 */

return [

    // =========================================================================
    // DEPOIMENTO DO CONDUTOR PM (PROMPT 0001 - Parte 1)
    // =========================================================================
    'pm_condutor' => [
        'titulo' => 'Depoimento do Condutor PM',
        'descricao' => 'Depoimento do policial militar que apresentou a ocorrência',
        'template' => <<<'TPL'
PROMPT PARA OITIVA DO POLICIAL MILITAR - CONDUTOR DA OCORRÊNCIA

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Hora do Fato: {{HORA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}
- Delegacia: {{DELEGACIA}}

Dados do Condutor:
- Nome: {{NOME}}

Envolvidos na Ocorrência:
{{LISTA_ENVOLVIDOS}}

1. Redação do Depoimento do Policial Responsável pela Apresentação da Ocorrência:
Redija o depoimento do apresentador da ocorrência, iniciando com a palavra "QUE", e organize o texto em parágrafos claros e concisos. Use o cabeçalho: ### **TERMO DE DEPOIMENTO DE {{NOME}}**.

Você é um assistente especializado em revisão, correção e formatação de textos oficiais. Sua tarefa é revisar e organizar o texto fornecido, seguindo estas diretrizes detalhadas:
- **RESPOSTA CURTA E DIRETA:** Sua resposta deve conter UNICAMENTE o corpo do texto do depoimento/termo. NÃO repita os dados de qualificação (CPF, RG, Filiação, etc.) no início da resposta.
- mantenha a precisão e a clareza em todos os depoimentos;
- corrija erros de português, incluindo ortografia, gramática, concordância e regência verbal e nominal;
- Ajuste a pontuação, como vírgulas, pontos, travessões e outros sinais, para garantir clareza e coesão;
- Insira "QUE" (em caixa alta e negrito) no início de cada parágrafo;
- Destaque os nomes de pessoas e codinomes em caixa alta e negrito (EXEMPLO);
- Caso haja números, como datas ou códigos, mantenha-os claros e destacados;
- Reorganize trechos do texto, se necessário, para melhorar a compreensão e o fluxo das informações;
- Não altere o sentido das informações prestadas. Ajustes devem ser feitos apenas para garantir organização e clareza, respeitando fielmente o relato original;
- Substitua palavras repetidas por sinônimos adequados, mantendo o mesmo significado, para evitar redundâncias.

APLIQUE ESSAS REGRAS AO BOLETIM DE OCORRÊNCIA A SEGUIR:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // DEPOIMENTO DA TESTEMUNHA PM (PROMPT 0001 - Parte 2)
    // =========================================================================
    'pm_testemunha' => [
        'titulo' => 'Depoimento da Testemunha PM',
        'descricao' => 'Depoimento do segundo policial da guarnição',
        'template' => <<<'TPL'
PROMPT PARA OITIVA DO POLICIAL MILITAR - TESTEMUNHA POLICIAL

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Hora do Fato: {{HORA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}
- Delegacia: {{DELEGACIA}}

Dados da Testemunha Policial:
- Nome: {{NOME}}

Envolvidos na Ocorrência:
{{LISTA_ENVOLVIDOS}}

2. Redação do Depoimento da Primeira Testemunha:
Redija o depoimento da testemunha policial, iniciando com a palavra "QUE", e organize o texto de forma similar ao do condutor. Use o cabeçalho: ### **TERMO DE DEPOIMENTO DE {{NOME}}**.

Você é um assistente especializado em revisão, correção e formatação de textos oficiais. Sua tarefa é revisar e organizar o texto fornecido, seguindo estas diretrizes detalhadas:
- **RESPOSTA CURTA E DIRETA:** Sua resposta deve conter UNICAMENTE o corpo do texto do depoimento/termo. NÃO repita os dados de qualificação (CPF, RG, Filiação, etc.) no início da resposta.
- mantenha a precisão e a clareza em todos os depoimentos;
- corrija erros de português, incluindo ortografia, gramática, concordância e regência verbal e nominal;
- Ajuste a pontuação, como vírgulas, pontos, travessões e outros sinais, para garantir clareza e coesão;
- Insira "QUE" (em caixa alta e negrito) no início de cada parágrafo;
- Destaque os nomes de pessoas e codinomes em caixa alta e negrito (EXEMPLO);
- Caso haja números, como datas ou códigos, mantenha-os claros e destacados;
- Reorganize trechos do texto, se necessário, para melhorar a compreensão e o fluxo das informações;
- Não altere o sentido das informações prestadas;
- Substitua palavras repetidas por sinônimos adequados, mantendo o mesmo significado, para evitar redundâncias.

APLIQUE ESSAS REGRAS AO BOLETIM DE OCORRÊNCIA A SEGUIR:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // EQUIPE PM COMPLETA (CONDUTOR + TESTEMUNHA PM de uma vez)
    // =========================================================================
    'pm_equipe' => [
        'titulo' => 'Depoimentos da Equipe PM (Condutor + Testemunha)',
        'descricao' => 'Gera depoimento do condutor e da testemunha policial de uma vez',
        'template' => <<<'TPL'
PROMPT PARA ELABORAÇÃO DE DEPOIMENTOS POLICIAIS

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Hora do Fato: {{HORA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}
- Delegacia: {{DELEGACIA}}

Envolvidos na Ocorrência:
{{LISTA_ENVOLVIDOS}}

Você é um assistente especializado na elaboração, revisão e formatação de documentos policiais formais utilizados em procedimentos de polícia judiciária.
Sua tarefa é analisar o BOLETIM DE OCORRÊNCIA fornecido e produzir:
1. DEPOIMENTO DO CONDUTOR (policial que apresentou a ocorrência)
2. DEPOIMENTO DA TESTEMUNHA POLICIAL (segundo policial da guarnição)

REGRAS GERAIS DE REDAÇÃO:
• utilize linguagem formal e jurídica;
• organize os fatos em ordem cronológica;
• corrija erros de português (ortografia, concordância e pontuação);
• substitua palavras repetidas por sinônimos adequados;
• não invente informações que não estejam no BO.

FORMATAÇÃO OBRIGATÓRIA:
• inicie cada parágrafo com **QUE,**
• destaque nomes de pessoas em **CAIXA ALTA E NEGRITO**
• destaque codinomes da mesma forma
• mantenha datas, horários, placas e números claros
• mantenha a narrativa objetiva e técnica

1) DEPOIMENTO DO CONDUTOR
Redija o depoimento do policial que apresentou a ocorrência.
Estrutura:
• patrulhamento ou serviço da guarnição
• local e horário da ocorrência
• visualização do autor ou situação suspeita
• descrição da infração ou crime
• abordagem policial
• busca pessoal (se houve)
• objeto apreendido relacionado ao crime (se houver)
• verificação de documentos (CNH, veículo etc.)
• condução à delegacia
• providências adotadas
O texto deve ser organizado em parágrafos iniciados com **QUE**.

2) DEPOIMENTO DA TESTEMUNHA POLICIAL
Redija o depoimento do segundo policial da guarnição.
• mantenha a mesma linha narrativa do condutor
• confirme os fatos presenciados
• destaque a conduta do autor
• mencione o objeto apreendido relacionado ao crime
• mencione a condução à delegacia
Se houver ameaças ou injúrias, inclua a fala entre aspas.

APLIQUE AS REGRAS AO BOLETIM DE OCORRÊNCIA ABAIXO:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // DEPOIMENTO DA VÍTIMA (PROMPT 0002)
    // =========================================================================
    'vitima' => [
        'titulo' => 'Declaração da Vítima',
        'descricao' => 'Termo de declaração da vítima com base no histórico do BOE',
        'template' => <<<'TPL'
PROMPT PARA DECLARAÇÃO DA VÍTIMA

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Hora do Fato: {{HORA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}
- Delegacia: {{DELEGACIA}}

Dados da Vítima:
- Nome: {{NOME}}
- CPF: {{CPF}}
- RG: {{RG}}
- Mãe: {{MAE}}
- Nascimento: {{NASCIMENTO}}
- Naturalidade: {{NATURALIDADE}}
- Profissão: {{PROFISSAO}}

Envolvidos na Ocorrência:
{{LISTA_ENVOLVIDOS}}

Crie um depoimento formal e estruturado para a vítima de uma ocorrência policial, assegurando que o relato siga os padrões jurídicos e seja redigido com clareza e precisão. (mencione TERMO DE DECLARAÇÃO DA VÍTIMA):

Instruções:
Descreva o ocorrido de forma cronológica e detalhada, iniciando com: ### **TERMO DE DEPOIMENTO DE {{NOME}}**.

Diretrizes de Resposta:
- **NÃO REPITA A QUALIFICAÇÃO:** Sua resposta deve conter APENAS o texto do depoimento formatado. Omitir cabeçalhos com CPF, RG, Endereço e Filiação na resposta.
- Use linguagem formal, objetiva e impessoal.
- Insira "QUE" (em caixa alta e negrito) no INÍCIO DE CADA PARÁGRAFO.
- Destaque os nomes de pessoas e codinomes em **CAIXA ALTA E NEGRITO**.
- Mantenha a precisão e a clareza; corrija erros de português.
- Não altere o sentido das informações prestadas.

APLIQUE ESSAS REGRAS AO TEXTO A SEGUIR:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // DEPOIMENTO DA TESTEMUNHA CIVIL (PROMPT 0004)
    // =========================================================================
    'testemunha_civil' => [
        'titulo' => 'Depoimento da Testemunha',
        'descricao' => 'Depoimento de testemunha civil da ocorrência',
        'template' => <<<'TPL'
PROMPT PARA DEPOIMENTO DA TESTEMUNHA

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Hora do Fato: {{HORA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}
- Delegacia: {{DELEGACIA}}

Dados da Testemunha:
- Nome: {{NOME}}
- CPF: {{CPF}}
- RG: {{RG}}
- Mãe: {{MAE}}
- Nascimento: {{NASCIMENTO}}

Envolvidos na Ocorrência:
{{LISTA_ENVOLVIDOS}}

Crie um depoimento formal e estruturado para a testemunha de uma ocorrência policial, assegurando que o relato siga os padrões jurídicos e seja redigido com clareza e precisão. (mencione DEPOIMENTO DA TESTEMUNHA):

Instruções:
Descreva o ocorrido de forma cronológica e detalhada, iniciando com: ### **TERMO DE DEPOIMENTO DE {{NOME}}**.

Diretrizes de Resposta:
- **NÃO REPITA A QUALIFICAÇÃO:** Sua resposta deve conter APENAS o texto do depoimento formatado. Omitir cabeçalhos com CPF, RG e Filiação na resposta.
- Mantenha uma linguagem formal, objetiva e impessoal.
- Insira "QUE" (em caixa alta e negrito) SOMENTE no início de cada parágrafo.
- Destaque os nomes de pessoas e codinomes em **CAIXA ALTA E NEGRITO**.
- Não altere o sentido das informações prestadas.

APLIQUE ESSAS REGRAS AO TEXTO A SEGUIR:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // OITIVA PARAFRASEADA (PROMPT 0003)
    // =========================================================================
    'parafraseada' => [
        'titulo' => 'Oitiva Parafraseada',
        'descricao' => 'Parafraseia um depoimento já existente mantendo o conteúdo',
        'template' => <<<'TPL'
PROMPT PARA OITIVA PARAFRASEADA

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Local do Fato: {{LOCAL_FATO}}

Dados da Pessoa:
- Nome: {{NOME}}

QUE, extraia a narrativa completa do depoimento apresentado, com foco nos detalhes específicos do crime e nos objetos mencionados.

1. Redação do Depoimento (mencione DEPOIMENTO PARAFRASEADO):
Redija o depoimento, também iniciando com a palavra "QUE", e organize o texto de forma similar ao do depoimento apresentado. Parafraseie o conteúdo, mantendo a linguagem formal e jurídica. Inclua citações diretas apenas se houver menção de ameaças ou injúrias.

Você é um assistente especializado em revisão, correção e formatação de textos oficiais:
- mantenha a precisão e a clareza;
- corrija erros de português;
- Insira "QUE" (em caixa alta e negrito) no início de cada parágrafo;
- Destaque os nomes de pessoas e codinomes em caixa alta e negrito;
- Não altere o sentido das informações prestadas;
- Substitua palavras repetidas por sinônimos adequados.

APLIQUE ESSAS REGRAS AO TEXTO A SEGUIR:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // INTERROGATÓRIO DO AUTOR - PERGUNTAS (Genérico)
    // =========================================================================
    'interrogatorio_autor' => [
        'titulo' => 'Perguntas para Interrogatório do Autor',
        'descricao' => 'Elabora perguntas para interrogatório com base no BOE',
        'template' => <<<'TPL'
PROMPT PARA ELABORAÇÃO DE PERGUNTAS DE INTERROGATÓRIO

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Hora do Fato: {{HORA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}
- Delegacia: {{DELEGACIA}}

Dados do Autor/Investigado:
- Nome: {{NOME}}

Envolvidos na Ocorrência:
{{LISTA_ENVOLVIDOS}}

3) PERGUNTAS ESTRATÉGICAS PARA O INTERROGATÓRIO
Você é um Delegado de Polícia experiente. Analise o Boletim de Ocorrência e elabore entre 10 e 15 perguntas **estratégicas de mérito e técnica investigativa** para o interrogatório de **{{NOME}}**.

Use o cabeçalho: ### **PERGUNTAS PARA INTERROGATÓRIO DE {{NOME}}**

Diretrizes Críticas:
- **NÃO ESCREVA UM DEPOIMENTO:** É proibido escrever uma narrativa em primeira ou terceira pessoa. O acusado será interrogado, portanto, forneça APENAS as perguntas.
- **FOCO TÉCNICO:** Busque esclarecer contradições, detalhes específicos da dinâmica do crime, prova de dolo (intenção), origem de objetos ilícitos e pontos que o autor possa tentar omitir.
- **EVITE O ÓBVIO:** NÃO faça perguntas básicas de qualificação (nome, endereço, idade) ou perguntas "bobas".
- **CONTEXTO:** Use os fatos narrados no BO para confrontar o interrogado.

Baseie-se nos fatos narrados no BOLETIM DE OCORRÊNCIA abaixo:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // TRÂNSITO - DEPOIMENTOS PM (Condutor + Testemunha)
    // =========================================================================
    'transito_pm' => [
        'titulo' => 'Depoimentos PM - Crime de Trânsito',
        'descricao' => 'Depoimentos policiais específicos para ocorrências de trânsito',
        'template' => <<<'TPL'
PROMPT – DEPOIMENTOS DE POLICIAIS MILITARES (OCORRÊNCIAS DE TRÂNSITO)

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Hora do Fato: {{HORA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}
- Delegacia: {{DELEGACIA}}

Envolvidos na Ocorrência:
{{LISTA_ENVOLVIDOS}}

Você é um assistente especializado na redação de documentos policiais formais.
Sua tarefa é analisar o BOLETIM DE OCORRÊNCIA fornecido e elaborar:
1) DEPOIMENTO DO CONDUTOR (policial que apresentou a ocorrência)
2) DEPOIMENTO DA TESTEMUNHA POLICIAL (segundo policial da guarnição)

REGRAS DE REDAÇÃO:
• Utilize linguagem formal e jurídica adequada a procedimentos policiais;
• Inicie cada parágrafo com **QUE,** em caixa alta;
• Destaque os nomes de pessoas em **CAIXA ALTA E NEGRITO**;
• Organize os fatos em ordem cronológica;
• Corrija erros gramaticais presentes no BO;
• Evite repetições, utilizando sinônimos adequados;
• Destaque sempre:
  - tipo de manobra perigosa
  - risco a pedestres
  - alteração de escapamento (se houver)
  - ausência de CNH (se houver)
  - recolhimento de veículo (se houver)

ESTRUTURA DO DEPOIMENTO:
1. patrulhamento da guarnição
2. visualização da infração
3. descrição das manobras perigosas
4. abordagem policial
5. verificação documental
6. condução à delegacia
7. destino do veículo

Não invente informações. Utilize apenas o que consta no BO.

APLIQUE AO BOLETIM DE OCORRÊNCIA A SEGUIR:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // TRÂNSITO - INTERROGATÓRIO
    // =========================================================================
    'transito_interrogatorio' => [
        'titulo' => 'Interrogatório - Crime de Trânsito',
        'descricao' => 'Perguntas para interrogatório em crimes de trânsito',
        'template' => <<<'TPL'
PROMPT – PERGUNTAS PARA INTERROGATÓRIO (CRIMES DE TRÂNSITO)

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}

Dados do Autor/Investigado:
- Nome: {{NOME}}

Você é um Delegado de Polícia especializado em crimes de trânsito (CTB). Elabore entre 10 e 15 perguntas estratégicas para o interrogatório de **{{NOME}}**.

Use o cabeçalho: ### **PERGUNTAS PARA INTERROGATÓRIO DE {{NOME}}**

REGRAS:
- **NÃO ESCREVA DEPOIMENTO NARRATIVO:** Forneça apenas as perguntas técnicas.
- **FOCO NO CTB:** Priorize perguntas que comprovem dolo/culpa, risco a terceiros, manobras específicas (grau, direção perigosa) e consciência da irregularidade.
- **EXAMES:** Pergunte sobre a recusa ao teste do etilômetro ou sinais de embriaguez narrados pelos policiais.
- **OBJETIVIDADE:** Evite perguntas genéricas de qualificação.

Baseie-se nos fatos narrados no BO abaixo:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // TRÂNSITO - COMPLETO (PM + Interrogatório)
    // =========================================================================
    'transito_completo' => [
        'titulo' => 'Completo - Crime de Trânsito (PM + Interrogatório)',
        'descricao' => 'Gera depoimentos PM e perguntas de interrogatório para trânsito',
        'template' => <<<'TPL'
PROMPT PARA ELABORAÇÃO DE DEPOIMENTOS POLICIAIS E PERGUNTAS DE INTERROGATÓRIO

Dados do Procedimento:
- BOE: {{BOE_NUMERO}}
- Data do Fato: {{DATA_FATO}}
- Hora do Fato: {{HORA_FATO}}
- Local do Fato: {{LOCAL_FATO}}
- Natureza/Incidência: {{INCIDENCIA_PENAL}}
- Delegacia: {{DELEGACIA}}

Envolvidos na Ocorrência:
{{LISTA_ENVOLVIDOS}}

Você é um assistente especializado na elaboração, revisão e formatação de documentos policiais formais utilizados em procedimentos de polícia judiciária.
Sua tarefa é analisar o BOLETIM DE OCORRÊNCIA fornecido e produzir:
1. DEPOIMENTO DO CONDUTOR (policial que apresentou a ocorrência)
2. DEPOIMENTO DA TESTEMUNHA POLICIAL (segundo policial da guarnição)
3. PERGUNTAS PARA O INTERROGATÓRIO DO AUTOR

REGRAS GERAIS DE REDAÇÃO:
• utilize linguagem formal e jurídica;
• organize os fatos em ordem cronológica;
• corrija erros de português (ortografia, concordância e pontuação);
• substitua palavras repetidas por sinônimos adequados;
• não invente informações que não estejam no BO.

FORMATAÇÃO OBRIGATÓRIA:
• inicie cada parágrafo com **QUE,**
• destaque nomes de pessoas em **CAIXA ALTA E NEGRITO**
• destaque codinomes da mesma forma
• mantenha datas, horários, placas e números claros
• mantenha a narrativa objetiva e técnica

1) DEPOIMENTO DO CONDUTOR
Redija o depoimento do policial que apresentou a ocorrência.
Estrutura:
• patrulhamento ou serviço da guarnição
• local e horário da ocorrência
• visualização do autor ou situação suspeita
• descrição da infração ou crime
• abordagem policial
• busca pessoal (se houve)
• objeto apreendido relacionado ao crime (se houver)
• verificação de documentos (CNH, veículo etc.)
• condução à delegacia
• providências adotadas

2) DEPOIMENTO DA TESTEMUNHA POLICIAL
Redija o depoimento do segundo policial da guarnição.
• mantenha a mesma linha narrativa do condutor
• confirme os fatos presenciados
• destaque a conduta do autor
• mencione o objeto apreendido relacionado ao crime
• mencione a condução à delegacia

3) PERGUNTAS PARA O INTERROGATÓRIO DO AUTOR
Elabore entre 10 e 15 perguntas objetivas.
As perguntas devem buscar esclarecer:
• autoria
• circunstâncias do fato
• motivação
• consciência da irregularidade ou crime
• eventual propriedade do objeto apreendido
• presença de outras pessoas

APLIQUE AS REGRAS AO BOLETIM DE OCORRÊNCIA ABAIXO:

{{HISTORICO_BOE}}
TPL
    ],

    // =========================================================================
    // CORREÇÃO / REVISÃO (PROMPT 0000)
    // =========================================================================
    'correcao' => [
        'titulo' => 'Revisão de Depoimento',
        'descricao' => 'Revisa e corrige um depoimento já escrito',
        'template' => <<<'TPL'
AGORA VOCÊ É UM ASSISTENTE ESPECIALIZADO EM REVISÃO, correção e formatação de textos oficiais. Sua tarefa é revisar e organizar o texto fornecido, seguindo estas diretrizes detalhadas:

- mantenha a precisão e a clareza em todos os depoimentos;
- corrija erros de português, incluindo ortografia, gramática, concordância e regência verbal e nominal;
- Ajuste a pontuação, como vírgulas, pontos, travessões e outros sinais, para garantir clareza e coesão;
- ATENÇÃO: Insira "QUE" (em caixa alta e negrito) no INÍCIO DE CADA PARÁGRAFO;
- Destaque os nomes de pessoas e codinomes em caixa alta e negrito (EXEMPLO);
- Caso haja números, como datas ou códigos, mantenha-os claros e destacados;
- Reorganize trechos do texto, se necessário, para melhorar a compreensão e o fluxo das informações;
- Não altere o sentido das informações prestadas. Ajustes devem ser feitos apenas para garantir organização e clareza, respeitando fielmente o relato original;
- Substitua palavras repetidas por sinônimos adequados, mantendo o mesmo significado, para evitar redundâncias.

APLIQUE ESSAS REGRAS AO TEXTO A SEGUIR:

{{HISTORICO_BOE}}
TPL
    ],

];
