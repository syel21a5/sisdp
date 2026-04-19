<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Envia um prompt para a API do DeepSeek (Externo - Rápido)
     */
    public function gerarTextoDeepSeek(string $prompt): ?string
    {
        $apiKey = env('DEEPSEEK_API_KEY');
        if (!$apiKey) {
            Log::warning("DeepSeek API Key não encontrada no .env");
            return null;
        }

        try {
            Log::info("Enviando prompt para DeepSeek API...");
            
            $response = Http::timeout(60)->withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->post("https://api.deepseek.com/chat/completions", [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um extrator de dados de BOE preciso que retorna apenas JSON.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'stream' => false
            ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error("Erro na API DeepSeek: Status " . $response->status() . " - " . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error("Exceção ao chamar DeepSeek: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extrai dados estruturados (JSON) de um texto de BOE.
     * 
     * @param string $texto Texto bruto do BOE
     * @return array|null Dados extraídos ou null em caso de falha
     */
    public function extrairDados(string $texto): ?array
    {
        $prompt = "Você é um excelente extrator de dados de Boletim de Ocorrência Policial. Leia o texto e extraia os dados estritamente no seguinte formato JSON. Não adicione textos, explicações ou markdown, apenas o JSON.
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
  \"objetos_apreendidos\": \"lista separada por barra\",
  \"vitimas\": [\"NOME1\", \"NOME2\"],
  \"autores\": [\"NOME1\"],
  \"testemunhas\": [\"NOME1\"],
  \"condutor\": [\"NOME1\"],
  \"outros\": [],
  \"veiculos\": [{\"marca_modelo\": \"...\", \"placa\": \"...\", \"chassi\": \"...\", \"cor\": \"...\"}],
  \"celulares\": [{\"marca_modelo\": \"...\", \"imei1\": \"...\", \"imei2\": \"...\"}],
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
- Naturalidade: retorne APENAS no formato \"CIDADE-UF\" (exemplo: \"SAO JOSE DO EGITO-PE\"). NUNCA inclua o país (BRASIL). 
  - IMPORTANTE: Se o dado for desconhecido ou não informado, retorne APENAS \"NAO INFORMADO\". NUNCA acrescente o estado nesses casos.
- Endereco: retorne o logradouro completo (rua, número, bairro) e ao final coloque apenas CIDADE-UF. 
  - IMPORTANTE: Remova REDUNDÂNCIAS. Se no início do endereço já houver \"MUNICIPIO DE [NOME]\", NÃO repita [NOME] no final da string. NÃO inclua CEP nem BRASIL.
  - Converta o nome do estado para sigla (PERNAMBUCO → PE, PARAIBA → PB, etc).
- Nomes de pessoas devem ser em MAIÚSCULAS e sem acentos.
- Se qualquer campo não constar no texto, use string vazia \"\".
- JAMAIS extraia o policial que registrou o BO como envolvido.

Texto do BOE: 
{$texto}";

        // API do DeepSeek - Processamento em Segundos com Alta Inteligência
        Log::info("Iniciando extração ultrarrápida via DeepSeek API...");
        $resposta = $this->gerarTextoDeepSeek($prompt);

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
