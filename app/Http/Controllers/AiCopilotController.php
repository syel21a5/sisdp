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
        }

        $systemPrompt = <<<PROMPT
Você é o Sisdepol Assistente IA, o cérebro integrado diretamente no sistema da Polícia Civil (Sisdepol).
VOCÊ TEM CONTROLE TOTAL sobre a interface através das suas ferramentas. Se o usuário perguntar se você tem controle ou se pode fazer coisas no site, DIGA QUE SIM, com orgulho, e explique que você pode abrir os editores de peças processuais automaticamente para ele.

Sua principal função é ajudar o Escrivão/Delegado a analisar o Boletim de Ocorrência (BOE), encontrar envolvidos e agilizar a criação de documentos processuais.

Você possui acesso a ferramentas (Function Calling). Em particular, você tem a ferramenta `abrir_editor`.
Se o usuário pedir para gerar um depoimento, declaração, interrogatório, ou qualquer outra peça listada, VOCÊ NÃO DEVE ESCREVER O TEXTO NA SUA RESPOSTA. 
Em vez disso, você DEVE acionar a ferramenta `abrir_editor`, passando o ID da pessoa e o tipo do termo exato.
Se o usuário pedir um documento citando apenas o papel (ex: "Gerar APFD Condutor" ou "Depoimento do Autor") sem dizer o nome, deduza a pessoa correspondente olhando a lista de envolvidos no contexto. Se houver apenas um condutor, use o ID dele.
Para documentos que REALMENTE não são atrelados a vítimas ou autores específicos (ex: apenas o AUTO DE APREENSAO), associe SEMPRE ao CONDUTOR (pegue o ID do condutor no contexto). NUNCA diga que precisa do nome de alguém para um auto de apreensão genérico. Apenas execute a ferramenta.
ATENÇÃO: LAUDOS e PERÍCIAS (como LAUDO TRAUMATOLOGICO) SEMPRE pertencem a uma pessoa específica. Se o usuário pedir um laudo, você DEVE associar à pessoa correta ou pedir para ele especificar de quem é o laudo.

O sistema backend se encarregará de redigir o texto perfeitamente usando as regras de negócio.

Seja sempre conciso. Se você usou uma ferramenta, diga apenas na sua resposta de texto: "Estou gerando o documento usando as regras avançadas do sistema e abrindo o editor."

Aqui estão os dados da ocorrência atual em que o usuário está trabalhando:
{$contextString}
PROMPT;

        $providerName = config('services.ai.default', 'deepseek');
        $providerConfig = config("services.ai.providers.{$providerName}");
        $rawApiKey = $providerConfig['api_key'] ?? null;
        $baseUrl = $providerConfig['base_url'] ?? 'https://api.deepseek.com/chat/completions';
        $model = $providerConfig['model'] ?? 'deepseek-chat';

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
                ['role' => 'user', 'content' => $userMessage],
            ],
            'tools' => [
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'abrir_editor',
                        'description' => 'Abre o editor de documentos/termos para uma pessoa. Documentos disponíveis: TERMO DE DECLARACAO, TERMO DE DEPOIMENTO, TERMO DE INTERROGATORIO, AUTO DE APRESENTACAO E APREENSAO, TERMO DE RESTITUICAO, TERMO DE RENUNCIA E DESISTENCIA DE REPRESENTACAO, TERMO DE REPRESENTACAO, TERMO DE COMPROMISSO, LAUDO TRAUMATOLOGICO, LAUDO TRAUMATOLOGICO IML, CERTIDAO DE ASSINATURA INDIVIDUAL, CERTIDAO DE ASSINATURA APFD, AAFAI CONDUTOR, APFD CONDUTOR.',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'pessoa_id' => [
                                    'type' => 'integer',
                                    'description' => 'O ID da pessoa (disponível no contexto do sistema).'
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

                        // Intercepta a chamada para injetar o texto_gerado usando o PromptGeneratorController!
                        if ($toolCall['name'] === 'abrir_editor') {
                            // Se a IA não mandou pessoa_id (ex: documento genérico), pegamos o ID do primeiro condutor do contexto
                            $pessoaId = $args['pessoa_id'] ?? null;
                            if (!$pessoaId && !empty($context['envolvidos']['condutores'])) {
                                $pessoaId = reset($context['envolvidos']['condutores'])['id'];
                            }
                            
                            // Normaliza o tipo_termo da IA para o nome exato do documento
                            if (isset($args['tipo_termo'])) {
                                $termoIA = strtoupper(trim($args['tipo_termo']));
                                // Tenta match exato primeiro, depois pelo mapa de variações
                                $args['tipo_termo'] = $mapaDocumentos[$termoIA] ?? $termoIA;
                            }
                            // Busca o nome e papel na lista de envolvidos do contexto
                            $nomeEncontrado = '';
                            $papelEncontrado = '';
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

                            if ($nomeEncontrado && $boeNumero) {
                                $promptReq = new Request([
                                    'boe' => $boeNumero,
                                    'pessoa_id' => $pessoaId,
                                    'nome' => $nomeEncontrado,
                                    'papel' => $papelEncontrado
                                ]);
                                
                                $promptGen = app(\App\Http\Controllers\PromptGeneratorController::class);
                                $promptResp = $promptGen->gerarPrompt($promptReq);
                                
                                if ($promptResp->status() == 200) {
                                    $promptData = $promptResp->getData();
                                    if (!empty($promptData->prompt)) {
                                        $aiService = app(\App\Services\AiService::class);
                                        $textoFinal = $aiService->gerarTextoDeepSeek($promptData->prompt);
                                        if ($textoFinal) {
                                            // 1. Converte negritos de Markdown (**) para HTML (<b>)
                                            $textoFinal = preg_replace('/\*\*(.*?)\*\*/s', '<b>$1</b>', $textoFinal);
                                            
                                            // 2. Remove cabeçalhos de título comuns (ex: ### <b>TERMO DE DEPOIMENTO...</b>)
                                            $textoFinal = preg_replace('/^\s*(?:#+|###)?\s*<b>(?:TERMO DE DEPOIMENTO|TERMO DE DECLARACAO|TERMO DE INTERROGATORIO|PERGUNTAS PARA INTERROGATÓRIO|DECLARAÇÃO|DEPOIMENTO|INTERROGATÓRIO|AUTO DE APRESENTAÇÃO).*?<\/b>\s*/mi', '', $textoFinal);
                                            // Fallback se o título vier sem negrito
                                            $textoFinal = preg_replace('/^\s*(?:#+|###)\s*(?:TERMO DE DEPOIMENTO|TERMO DE DECLARACAO|TERMO DE INTERROGATORIO|PERGUNTAS PARA INTERROGATÓRIO|DECLARAÇÃO|DEPOIMENTO|INTERROGATÓRIO|AUTO DE APRESENTAÇÃO).*?$/mi', '', $textoFinal);
                                            
                                            // 3. Remove blocos de assinatura comuns ao final
                                            $textoFinal = preg_replace('/Assinatura:\s*<b>.*?<\/b>\s*$/i', '', $textoFinal);
                                            $textoFinal = preg_replace('/Assinatura:\s*.*?$/i', '', $textoFinal);
                                            $textoFinal = preg_replace('/(?:DEPOENTE|AUTORIDADE POLICIAL|ESCRIV[AÃ]O|TESTEMUNHA):\s*$/mi', '', $textoFinal);
                                            
                                            // Limpa espaços extras
                                            $textoFinal = trim($textoFinal);
                                            
                                            $args['texto_gerado'] = $textoFinal;
                                        }
                                    }
                                }
                            }
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
                Log::warning("Chave Copilot falhou para {$providerName}. Tentando a próxima... Erro: {$lastError}");
                continue;

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Exceção com uma chave Copilot do {$providerName}. Tentando a próxima... Erro: {$lastError}");
                continue;
            }
        }

        Log::error("Erro {$providerName} API Copilot: Todas as chaves falharam. Último erro: {$lastError}");
        return response()->json([
            'success' => false,
            'error' => "Erro na comunicação com a Inteligência Artificial. Verifique as configurações ou tente novamente em instantes."
        ]);
    }
}
