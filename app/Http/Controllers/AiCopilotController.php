<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCopilotController extends Controller
{
    public function chat(Request $request)
    {
        // Verifica permissão de acesso ao Assistente de IA (Copilot)
        $user = auth()->user();
        if ($user && isset($user->permissions['copilot_ia']) && !$user->permissions['copilot_ia']) {
            Log::warning("Usuário sem permissão de Copilot IA tentou acessar: {$user->username}");
            return response()->json([
                'success' => false,
                'error' => "Acesso Negado: Apenas usuários habilitados podem usar o Assistente de IA."
            ], 403);
        }

        $userMessage = $request->input('message');
        $context = $request->input('context');
        $history = $request->input('history', []);
        
        Log::info("Copilot Context Received: ", $context ?? []);

        $contextString = "CONTEXTO DO SISTEMA:\n";
        $boeNumero = $context['boe_numero'] ?? '';
        $boeTexto = $context['boe'] ?? '';

        // Fallback: se o front-end não enviou o texto (ex: recarregou a página), tenta buscar no cache pelo número do BOE
        if (empty($boeTexto) && !empty($boeNumero)) {
            $boeLimpo = preg_replace('/[^A-Za-z0-9]/', '', $boeNumero);
            $cachePaths = [
                storage_path("app/boe_cache/boe_pcpe_{$boeLimpo}_apfd.json"),
                storage_path("app/boe_cache/boe_pm_{$boeLimpo}_apfd.json"),
                storage_path("app/boe_cache/boe_{$boeLimpo}_apfd.json"), // antigo
            ];
            foreach ($cachePaths as $path) {
                if (file_exists($path)) {
                    $cachedData = json_decode(file_get_contents($path), true);
                    if (!empty($cachedData['texto_raw'])) {
                        $boeTexto = $cachedData['texto_raw'];
                        break;
                    }
                }
            }
        }

        $contextString .= "- BOE Número: " . ($boeNumero ?: 'Não informado') . "\n";
        $contextString .= "- Texto extraído do BOE:\n" . ($boeTexto ?: 'Vazio') . "\n\n";
        
        $contextString .= "- Pessoas Envolvidas (Chips):\n";
        if (!empty($context['envolvidos'])) {
            foreach ($context['envolvidos'] as $papel => $pessoas) {
                foreach ($pessoas as $pessoa) {
                    $contextString .= "  * Papel: {$papel} | ID: {$pessoa['id']} | Nome: {$pessoa['nome']}\n";
                }
            }
        } else {
            $contextString .= "  * Nenhuma pessoa envolvida encontrada.\n";
        }$systemPrompt = <<<PROMPT
Você é o Sisdepol Assistente IA, o cérebro integrado diretamente no sistema da Polícia Civil (Sisdepol).
RESPONDA SEMPRE EM PORTUGUÊS DO BRASIL. NUNCA FALE EM INGLÊS.
VOCÊ TEM CONTROLE TOTAL sobre a interface através das suas ferramentas. Se o usuário perguntar se você tem controle ou se pode fazer coisas no site, DIGA QUE SIM, com orgulho, e explique que você pode abrir os editores de peças processuais automaticamente para ele.

Sua principal função é ajudar o Escrivão/Delegado a analisar o Boletim de Ocorrência (BOE), encontrar envolvidos e agilizar a criação de documentos processuais.

Você possui acesso a ferramentas (Function Calling). Em particular, você tem a ferramenta `abrir_editor`.
Se o usuário pedir para gerar um depoimento, declaração, interrogatório, laudo, ou qualquer outra peça listada, VOCÊ NÃO DEVE ESCREVER O TEXTO NA SUA RESPOSTA. 
Em vez disso, você DEVE acionar a ferramenta `abrir_editor`, passando o ID da pessoa e o tipo do termo exato.

TOLERÂNCIA A ERROS DE DIGITAÇÃO E AMBIGUIDADE (MUITO IMPORTANTE):
- O usuário humano escreve rápido e pode cometer erros de digitação. Você deve compreender a intenção, corrigir mentalmente e mapear para o NOME DO DOCUMENTO exato da lista abaixo.
- SE o pedido for AMBÍGUO (ex: pediu "Ofício da família" e existem "APFD" e "Mandado de Prisão"), VOCÊ NÃO DEVE ACIONAR A FERRAMENTA. Em vez disso, responda no chat listando as opções exatas ENUMERADAS (ex: "1 - APFD - OFICIO FAMILIA\n2 - MANDADO DE PRISAO - OFICIO FAMILIA") e pergunte qual ele deseja abrir.
- Se na mensagem seguinte o usuário responder apenas com o NÚMERO (ex: "1" ou "2"), você deve entender o contexto da conversa, saber qual era a opção correspondente ao número e acionar a ferramenta corretamente.
- Se for um documento que exige pessoa (ex: TERMO DE RESTITUIÇÃO, REPRESENTAÇÃO) e o usuário pedir SEM ESPECIFICAR QUEM É, VOCÊ NÃO DEVE ACIONAR A FERRAMENTA. Em vez disso, pergunte: "Para qual envolvido você deseja abrir esse documento?"

LISTA DE DOCUMENTOS DISPONÍVEIS NO SISTEMA (Use APENAS estes nomes):
- TERMO DE INTERROGATORIO, TERMO DE DECLARACAO, TERMO DE DEPOIMENTO
- AAFAI - AUTOR, APFD - AUTOR, NOTA DE CULPA, NOTA DE CIENCIA - GARANTIAS CONSTITUCIONAIS
- AAFAI - TESTEMUNHA, APFD - TESTEMUNHA
- AAFAI - VITIMA, APFD - VITIMA
- CERTIDAO DE ASSINATURA INDIVIDUAL, CERTIDAO DE ASSINATURA APFD, AAFAI CONDUTOR, APFD CONDUTOR
- AUTO CIRCUNSTACIADO - AUTOR, COMUNICACAO DE APFD, COMUNICACAO DE APFD - UNICO OFICIO
- MANDADO DE PRISAO - OFICIOS, MANDADO DE PRISAO - OFICIO FAMILIA, APFD - OFICIO FAMILIA, MANDADO DE PRISAO - RECOLHIMENTO
- AUTO DE APRESENTACAO E APREENSAO, TERMO DE RESTITUICAO
- TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO, TERMO DE REPRESENTACAO, TERMO DE COMPROMISSO, TERMO DE LIBERACAO DE MENOR - INFRATOR
- LAUDO TRAUMATOLOGICO, LAUDO TRAUMATOLOGICO IML, PERICIA EM VEICULO, PERICIA EM LOCAL DE CRIME
- AVALIACAO DE OBJETOS, AVALIACAO INDIRETA DE OBJETOS
- EXAME DE CONSTATACAO DE DANOS E AVALIACAO, EXAME DE CONSTATACAO DE DANOS INDIRETA, EXAME DE EFICIENCIA DE ARMA DE FOGO
- DESPACHO DE CONCLUSAO, ROL DE TESTEMUNHAS

Se o documento for PROCEDIMENTO GERAL/GENÉRICO (ex: DESPACHO DE CONCLUSAO, ROL DE TESTEMUNHAS, AVALIACAO DE OBJETOS, PERICIAS), passe SEMPRE o `pessoa_id` como `0`.
Se o documento for PESSOAL (qualquer outro da lista, incluindo OFÍCIOS, MANDADOS, APFD, AAFAI), você DEVE OBRIGATORIAMENTE passar o ID da pessoa correta. Se o usuário não disse quem é, olhe para as pessoas disponíveis no contexto da tela: se houver APENAS UMA pessoa naquele papel (ex: apenas 1 autor), ou se for óbvio de quem se trata, NÃO PERGUNTE nada, apenas abra o documento para ela. Só pergunte se houver DUAS OU MAIS opções válidas e estiver ambíguo. NUNCA passe 0 para documentos que exigem pessoa.

REGRAS DE DOCUMENTOS POR PAPEL:
As regras abaixo são apenas para as "oitivas" principais. Para os demais documentos (Laudos, Representações, Certidões, Autos, Ofícios), você tem liberdade para abrir conforme o pedido, adaptando para o papel correto (ex: se for AAFAI para autor, use "AAFAI - AUTOR").
- AUTOR só possui "TERMO DE INTERROGATORIO". NUNCA possui depoimento ou declaração.
- VITIMA só possui "TERMO DE DECLARACAO". NUNCA possui depoimento ou interrogatório.
- TESTEMUNHA e CONDUTOR só possuem "TERMO DE DEPOIMENTO". NUNCA possuem interrogatório ou declaração.

SE o usuário pedir uma OITIVA que não bate com o papel (ex: "Gerar depoimento do autor"), VOCÊ É ESTRITAMENTE PROIBIDO de usar a ferramenta. Responda avisando qual é o documento correto.

Seja sempre conciso. Se você usou uma ferramenta, diga apenas na sua resposta de texto: "Estou abrindo o editor do [Nome do Documento] para [Nome da Pessoa]." (Ou apenas o nome do documento se for genérico).
MUITO IMPORTANTE: O ID numérico, `pessoa_id 0`, ou parâmetros técnicos são APENAS para uso interno das suas ferramentas. NUNCA exiba, mencione ou explique esses parâmetros (como "ID 0") para o usuário. O usuário final não deve ver nenhuma linguagem técnica.

Aqui estão os dados da ocorrência atual em que o usuário está trabalhando:
{$contextString}
PROMPT;

        $providerName = config('services.ai.default', 'deepseek');
        $providerConfig = config("services.ai.providers.{$providerName}");
        $rawApiKey = $providerConfig['api_key'] ?? null;
        $baseUrl = $providerConfig['base_url'] ?? 'https://api.deepseek.com/chat/completions';
        $model = $providerConfig['model'] ?? 'deepseek-chat';

        // Lista de modelos de fallback: tenta na ordem até um responder (evita 503/429 de modelo sobrecarregado).
        $modelosFallback = config("services.ai.providers.{$providerName}.fallback_models", []);
        if (empty($modelosFallback) || !is_array($modelosFallback)) {
            $modelosFallback = [$model];
            if ($providerName === 'gemini') {
                $modelosFallback = array_values(array_unique(array_filter([
                    env('GEMINI_MODEL', 'gemini-3.5-flash'),
                    'gemini-3.6-flash',
                    'gemini-3-flash-preview',
                    'gemini-3.5-flash',
                ])));
            }
        }

        if (!$rawApiKey) {
            Log::warning("API Key do provedor {$providerName} não configurada no .env");
            return response()->json([
                'success' => false,
                'error' => "A chave de API da Inteligência Artificial não está configurada. Contate o administrador."
            ]);
        }

        // Suporte para múltiplas chaves API (Load Balancing e Fallback)
        $keys = [$rawApiKey];
        if (strpos($rawApiKey, ',') !== false) {
            $keys = array_filter(array_map('trim', explode(',', $rawApiKey)));
            shuffle($keys);
        }

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
            ],
            'tools' => [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'abrir_editor',
                        'description' => 'Abre o editor de documentos/termos para uma pessoa. Para saber os nomes exatos permitidos, CONSULTE A LISTA no System Prompt. Você deve passar exatamente um dos nomes daquela lista.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'pessoa_id' => [
                                    'type' => 'integer',
                                    'description' => 'O ID da pessoa. Se o documento for GENÉRICO e não pertencer a ninguém (ex: Despacho, Rol), passe o valor 0.'
                                ],
                                'tipo_termo' => [
                                    'type' => 'string',
                                    'description' => 'Nome do documento a abrir. Ex: TERMO DE DEPOIMENTO, TERMO DE DECLARACAO, AAFAI CONDUTOR, APFD CONDUTOR, etc.'
                                ]
                            ],
                            'required' => ['pessoa_id', 'tipo_termo']
                        ]
                    ]
                ]
            ],
            'tool_choice' => 'auto'
        ];

        // Injetar o histórico de mensagens (contexto da conversa)
        if (is_array($history)) {
            foreach ($history as $msg) {
                if (!empty($msg['role']) && isset($msg['content'])) {
                    $payload['messages'][] = [
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            }
        }

        // Adicionar a mensagem atual do usuário
        $payload['messages'][] = ['role' => 'user', 'content' => $userMessage];

        // Mapa de normalização: converte variações do nome do documento para o nome exato
        $mapaDocumentos = [
            'DECLARACAO' => 'TERMO DE DECLARACAO',
            'DECLARAÇÃO' => 'TERMO DE DECLARACAO',
            'TERMO_DE_DECLARACAO' => 'TERMO DE DECLARACAO',
            'TERMO DE DECLARAÇÃO' => 'TERMO DE DECLARACAO',
            'DEPOIMENTO' => 'TERMO DE DEPOIMENTO',
            'TERMO_DE_DEPOIMENTO' => 'TERMO DE DEPOIMENTO',
            'INTERROGATORIO' => 'TERMO DE INTERROGATORIO',
            'INTERROGATÓRIO' => 'TERMO DE INTERROGATORIO',
            'TERMO_DE_INTERROGATORIO' => 'TERMO DE INTERROGATORIO',
            'TERMO DE INTERROGATÓRIO' => 'TERMO DE INTERROGATORIO',
            'AUTO DE APREENSAO' => 'AUTO DE APRESENTACAO E APREENSAO',
            'AUTO APREENSAO' => 'AUTO DE APRESENTACAO E APREENSAO',
            'RESTITUICAO' => 'TERMO DE RESTITUICAO',
            'RESTITUIÇÃO' => 'TERMO DE RESTITUICAO',
            'RENUNCIA' => 'TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO',
            'REPRESENTACAO' => 'TERMO DE REPRESENTACAO',
            'REPRESENTAÇÃO' => 'TERMO DE REPRESENTACAO',
            'COMPROMISSO' => 'TERMO DE COMPROMISSO',
            'LAUDO' => 'LAUDO TRAUMATOLOGICO',
            'CERTIDAO' => 'CERTIDAO DE ASSINATURA INDIVIDUAL',
            'CERTIDAO APFD' => 'CERTIDAO DE ASSINATURA APFD',
            'AAFAI' => 'AAFAI CONDUTOR',
            'APFD' => 'APFD CONDUTOR'
        ];

        $lastError = null;

        foreach ($modelosFallback as $currentModel) {
            $payload['model'] = $currentModel;
            foreach ($keys as $apiKey) {
                try {
                    $response = Http::withToken($apiKey)
                        ->timeout(60)
                        ->post($baseUrl, $payload);

                if ($response->successful()) {
                    $data = $response->json();
                    $message = $data['choices'][0]['message'];

                    // Verifica se a IA decidiu usar uma ferramenta
                    if (isset($message['tool_calls']) && count($message['tool_calls']) > 0) {
                        $toolCall = $message['tool_calls'][0]['function'];
                        $args = json_decode($toolCall['arguments'], true) ?? [];

                        // Abre o editor do documento pré-preenchido com os dados da pessoa
                        // (a redação da oitiva fica por conta do escrivão — sem geração de texto pela IA)
                        if ($toolCall['name'] === 'abrir_editor') {
                            $pessoaId = $args['pessoa_id'] ?? null;
                            $termoIA = isset($args['tipo_termo']) ? strtoupper(trim($args['tipo_termo'])) : '';
                            
                            // Lista de termos que são realmente genéricos (pessoa_id 0 é válido)
                            $termosGenericos = ['DESPACHO DE CONCLUSAO', 'ROL DE TESTEMUNHAS', 'AVALIACAO DE OBJETOS', 'AVALIACAO INDIRETA DE OBJETOS', 'EXAME DE CONSTATACAO DE DANOS E AVALIACAO', 'EXAME DE CONSTATACAO DE DANOS INDIRETA', 'EXAME DE EFICIENCIA DE ARMA DE FOGO', 'PERICIA EM VEICULO', 'PERICIA EM LOCAL DE CRIME', 'OFICIOS MANDADO DE PRISAO'];
                            
                            $isGenerico = in_array($termoIA, $termosGenericos) || in_array($mapaDocumentos[$termoIA] ?? '', $termosGenericos);

                            // Se a IA mandou pessoa_id 0 ou vazio para um documento que NÃO é genérico,
                            // forçamos o ID do primeiro autor (ou vitima/condutor) do contexto para evitar que quebre.
                            if ((empty($pessoaId) || $pessoaId == 0) && !$isGenerico) {
                                if (!empty($context['envolvidos']['autores'])) {
                                    $pessoaId = reset($context['envolvidos']['autores'])['id'];
                                } elseif (!empty($context['envolvidos']['condutores'])) {
                                    $pessoaId = reset($context['envolvidos']['condutores'])['id'];
                                } elseif (!empty($context['envolvidos']['vitimas'])) {
                                    $pessoaId = reset($context['envolvidos']['vitimas'])['id'];
                                }
                            }
                            
                            // Normaliza o tipo_termo da IA para o nome exato do documento
                            if ($termoIA) {
                                // Tenta match exato primeiro, depois pelo mapa de variações
                                $args['tipo_termo'] = $mapaDocumentos[$termoIA] ?? $termoIA;
                            }
                            // Busca o nome e papel na lista de envolvidos do contexto
                            $nomeEncontrado = '';
                            $preenchido_em = '';
                            $mapaPapeis = [
                                'vitimas' => 'VITIMA',
                                'autores' => 'AUTOR',
                                'testemunhas' => 'TESTEMUNHA',
                                'condutores' => 'CONDUTOR',
                                'outros' => 'OUTRO'
                            ];

                            if (!empty($context['envolvidos'])) {
                                foreach ($context['envolvidos'] as $papel => $pessoas) {
                                    foreach ($pessoas as $pessoa) {
                                        if ($pessoa['id'] == $pessoaId) {
                                            $nomeEncontrado = $pessoa['nome'];
                                            $papelEncontrado = $mapaPapeis[strtolower($papel)] ?? 'OUTRO';
                                            break 2;
                                        }
                                    }
                                }
                            }

                            // NOTA INTENCIONAL: O Copilot APENAS abre o documento pré-preenchido com os
                            // dados da pessoa. A redação da oitiva/depoimento/declaração fica por conta
                            // do usuário (escrivão), evitando a chamada de IA que gerava o texto e demorava.
                        }
                        
                        return response()->json([
                            'success' => true,
                            'tool_call' => [
                                'name' => $toolCall['name'],
                                'arguments' => json_encode($args)
                            ],
                            'reply' => $message['content'] ?? 'Estou gerando o documento usando as regras avançadas do sistema...'
                        ]);
                    }

                    // Se não usou ferramenta, apenas retorna o texto
                    return response()->json([
                        'success' => true,
                        'reply' => $message['content']
                    ]);
                    
                }

                // Se falhou com essa chave, tenta a próxima
                $lastError = "Status " . $response->status() . " - " . $response->body();
                Log::warning("Chave Copilot falhou para {$providerName} (Modelo: {$currentModel}). Tentando a próxima... Erro: {$lastError}");
                continue;

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Exceção com uma chave Copilot do {$providerName} (Modelo: {$currentModel}). Tentando a próxima... Erro: {$lastError}");
                continue;
            }
        }
        }

        Log::error("Erro {$providerName} API Copilot: Todas as chaves e modelos falharam. Último erro: {$lastError}");
        return response()->json([
            'success' => false,
            'error' => "Erro na comunicação com a Inteligência Artificial. Verifique as configurações ou tente novamente em instantes."
        ]);
    }
}
