<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Envia um prompt para a API do DeepSeek (Externo - Rápido)
     */
    public function gerarTextoDeepSeek(string $userPrompt, string $systemPrompt = 'Você é um assistente útil.', ?string $providerOverride = null): ?string
    {
        $providerName = $providerOverride ?? config('services.ai.default', 'deepseek');
        $providerConfig = config("services.ai.providers.{$providerName}");

        $rawApiKey = $providerConfig['api_key'] ?? null;
        $baseUrl = $providerConfig['base_url'] ?? 'https://api.deepseek.com/chat/completions';
        $model = $providerConfig['model'] ?? 'deepseek-chat';

        if (!$rawApiKey) {
            Log::warning("{$providerName} API Key não encontrada no .env");
            return null;
        }

        // Suporte para múltiplas chaves API (Load Balancing e Fallback)
        $keys = [$rawApiKey];
        if (strpos($rawApiKey, ',') !== false) {
            $keys = array_filter(array_map('trim', explode(',', $rawApiKey)));
            shuffle($keys); // Randomiza a ordem para balanceamento de carga
        }

        $lastError = null;

        foreach ($keys as $apiKey) {
            try {
                Log::info("Enviando prompt para {$providerName} API (Tentativa com uma das chaves)...");
                
                $response = Http::timeout(60)->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ])->post($baseUrl, [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt]
                    ],
                    'stream' => false
                ]);

                if ($response->successful()) {
                    return $response->json('choices.0.message.content');
                }

                // Se falhar (ex: Rate Limit 429), salva o erro e tenta a próxima chave do loop
                $lastError = "Status " . $response->status() . " - " . $response->body();
                Log::warning("Chave falhou para {$providerName}. Tentando a próxima... Erro: {$lastError}");
                continue;

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("Exceção com uma chave do {$providerName}. Tentando a próxima... Erro: {$lastError}");
                continue;
            }
        }

        Log::error("Todas as chaves configuradas para {$providerName} falharam. Último erro: {$lastError}");
        return null;
    }

    /**
     * Extrai dados estruturados (JSON) de um texto de BOE.
     * 
     * @param string $texto Texto bruto do BOE
     * @return array|null Dados extraídos ou null em caso de falha
     */
    public function extrairDados(string $texto): ?array
    {
        $systemMessage = "Você é um excelente extrator de dados de Boletim de Ocorrência Policial. Leia o texto e extraia os dados estritamente no seguinte formato JSON. Não adicione textos, explicações ou markdown, apenas o JSON.

IMPORTANTE SOBRE OBJETOS APREENDIDOS:
1. Localize a seção \"Objetos\" e extraia TODOS os itens listados.
2. NÃO resuma nem agrupe itens diferentes.
3. Para cada objeto, inclua: [Tipo/Nome] - Marca: [Marca], Mod: [Modelo], Cor: [Cor], Qtd: [Quantidade], Desc: [Descrição completa].
4. Se for um celular, inclua também o IMEI ou Número de Série na descrição.
5. Separe cada objeto por uma quebra de linha (\\n) dentro da string \"objetos_apreendidos\".
6. ATENÇÃO: Em BOs da Polícia Militar, os itens aparecem como \"OBJETO Nº 1\", \"OBJETO Nº 2\" e usam \"Caracteristicas Adicionais\". Trate-os da mesma forma e extraia todos os detalhes.

Formato esperado:
{
  \"boe\": \"12345\",
  \"data_fato\": \"DD/MM/AAAA\",
  \"hora_fato\": \"HH:MM\",
  \"end_fato\": \"endereco do fato\",
  \"delegado\": \"nome\",
  \"escrivao\": \"nome\",
  \"delegacia\": \"nome\",
  \"natureza\": \"crime\",
  \"objetos_apreendidos\": \"Item 1\\nItem 2\\nItem 3...\",
  \"vitimas\": [\"NOME1\", \"NOME2\"],
  \"autores\": [\"NOME1\"],
  \"testemunhas\": [\"NOME1\"],
  \"condutor\": [\"NOME1\"],
  \"outros\": [],
  \"veiculos\": [{\"marca_modelo\": \"...\", \"placa\": \"...\", \"chassi\": \"...\", \"cor\": \"...\", \"proprietario\": \"...\"}],
  \"celulares\": [{\"marca_modelo\": \"...\", \"imei1\": \"...\", \"imei2\": \"...\", \"proprietario\": \"...\"}],
  \"envolvidos_detalhes\": {
    \"NOME COMPLETO\": {
      \"nome\": \"NOME COMPLETO\",
      \"cpf\": \"XXX.XXX.XXX-XX\",
      \"rg\": \"...\",
      \"nascimento\": \"DD/MM/AAAA\",
      \"mae\": \"NOME DA MAE\",
      \"pai\": \"NOME DO PAI\",
      \"naturalidade\": \"CIDADE-UF\",
      \"profissao\": \"...\",
      \"endereco\": \"RUA, NUMERO, BAIRRO, CIDADE-UF\",
      \"estado_civil\": \"...\",
      \"escolaridade\": \"...\",
      \"telefone\": \"(XX) XXXXX-XXXX\",
      \"alcunha\": \"...\"
    }
  }
}

REGRAS IMPORTANTES DE FORMATAÇÃO:
- O \"condutor\" deve ser exclusivamente o Policial Militar ou autoridade policial que efetuou a prisão/condução da ocorrência até a delegacia (encontrado sob \"Condutor da ocorrência:\" no final do BOE).
- JAMAIS coloque o Infrator/Autor/Imputado (como VICENTE FRANCISCO DE ALMEIDA) como \"condutor\" da ocorrência, mesmo que no histórico ele seja descrito como o \"condutor do veículo/moto\". O Infrator/Autor deve ir apenas para \"autores\".
- Naturalidade: retorne APENAS no formato \"CIDADE-UF\" (exemplo: \"SAO JOSE DO EGITO-PE\"). NUNCA inclua o país (BRASIL). 
  - IMPORTANTE: Se o dado for desconhecido ou não informado, retorne APENAS \"NAO INFORMADO\". NUNCA acrescente o estado nesses casos.
- Endereco: retorne o logradouro completo (rua, número, bairro) e ao final coloque apenas CIDADE-UF. 
  - IMPORTANTE: Remova REDUNDÂNCIAS. Se no início do endereço já houver \"MUNICIPIO DE [NOME]\", NÃO repita [NOME] no final da string. NÃO inclua CEP nem BRASIL.
  - Converta o nome do estado para sigla (PERNAMBUCO → PE, PARAIBA → PB, etc).
- Nomes de pessoas devem ser em MAIÚSCULAS e sem acentos.
- Se qualquer campo não constar no texto, use string vazia \"\".
- JAMAIS extraia o policial que registrou o BO como envolvido.
- Se tiver dúvidas do papel de uma pessoa, coloque-a em \"outros\".";

        $userMessage = "Texto do BOE para extração: \n{$texto}";

        // API do Groq - Processamento em Segundos para Extração
        Log::info("Iniciando extração ultrarrápida via Groq API (com cache)...");
        $resposta = $this->gerarTextoDeepSeek($userMessage, $systemMessage, 'groq');

        if (!$resposta) {
            Log::error("Falha ao comunicar com DeepSeek. Falha na extração.");
            return null;
        }

        // Tenta limpar a resposta (remover ```json e textos extras)
        $jsonLimpo = preg_replace('/^```json\s*|```$/m', '', $resposta);
        $jsonLimpo = trim($jsonLimpo);

        // Tenta encontrar o bloco JSON caso haja lixo em volta
        if (preg_match('/\{.*\}/s', $jsonLimpo, $matches)) {
            $dados = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                // TRAVA DE SEGURANÇA: Limitar endereços a 200 caracteres
                if (isset($dados['end_fato']) && strlen($dados['end_fato']) > 200) {
                    $dados['end_fato'] = substr($dados['end_fato'], 0, 197) . "...";
                }
                if (isset($dados['envolvidos_detalhes']) && is_array($dados['envolvidos_detalhes'])) {
                    foreach ($dados['envolvidos_detalhes'] as $nome => $detalhe) {
                        if (isset($detalhe['endereco']) && strlen($detalhe['endereco']) > 200) {
                            $dados['envolvidos_detalhes'][$nome]['endereco'] = substr($detalhe['endereco'], 0, 197) . "...";
                        }
                    }
                }
                return $dados;
            }
            Log::error("Falha ao decodificar JSON da IA: " . json_last_error_msg());
            Log::debug("JSON Bruto: " . $matches[0]);
        } else {
            Log::error("IA não retornou um formato JSON válido: " . $resposta);
        }

        return null;
    }
}
