import sys
import json
import os
import time

# Forçar inclusão das pastas de site-packages do usuário (Windows) caso o PHP tenha as ocultado
try:
    import site
    user_site = site.getusersitepackages()
    if user_site and user_site not in sys.path:
        sys.path.append(user_site)
        
    appdata = os.environ.get('APPDATA')
    if not appdata:
        appdata = r"C:\Users\VGR\AppData\Roaming"
        
    py_ver = f"Python{sys.version_info.major}{sys.version_info.minor}"
    hard_path = os.path.join(appdata, "Python", py_ver, "site-packages")
    if os.path.exists(hard_path) and hard_path not in sys.path:
        sys.path.append(hard_path)
except:
    pass

# Tentativa de auto-instalação de dependências
import subprocess

def ensure_package(module_name: str, pip_name: str):
    try:
        __import__(module_name)
    except ImportError:
        try:
            subprocess.check_call([sys.executable, "-m", "pip", "install", pip_name])
            __import__(module_name)
        except Exception as e:
            print(json.dumps({"success": False, "error": f"A biblioteca '{pip_name}' não está instalada no Python local '{sys.executable}' e a instalação falhou."}))
            sys.exit(1)

ensure_package('google.genai', 'google-genai')
ensure_package('fitz', 'PyMuPDF')

from google import genai
import fitz  # PyMuPDF

def fallback_json(error_msg: str):
    """Fallback simples caso o processo falhe."""
    print(json.dumps({
        "success": False,
        "error": error_msg,
        "dados": {
            "vitimas": [], "autores": [], "testemunhas": [],
            "condutor": [], "outros": [],
            "natureza": "", "boe": "", "ip": "",
            "delegado": "", "escrivao": "", "delegacia": "",
            "data_fato": "", "hora_fato": "", "end_fato": "",
            "envolvidos_detalhes": {}
        }
    }))
    sys.exit(1)

def ler_arquivo(file_path: str) -> str:
    if not os.path.exists(file_path):
        fallback_json(f"Arquivo não encontrado no caminho: {file_path}")
    
    ext = os.path.splitext(file_path)[1].lower()
    
    try:
        if ext == '.pdf':
            doc = fitz.open(file_path)
            full_text = ""
            for page in doc:
                full_text += page.get_text()
            doc.close()
            return full_text
        else:
            with open(file_path, 'r', encoding='utf-8') as f:
                return f.read()
    except Exception as e:
        fallback_json(f"Erro ao tentar ler o arquivo {ext}: {str(e)}")
    return ""

def extract_with_gemini(texto: str, api_key: str):
    try:
        client = genai.Client(api_key=api_key)
    except Exception as e:
        return {"success": False, "error": f"Erro de credenciais na IA: {str(e)}"}

    prompt = rf"""Você é um sistema especializado em extração de dados estruturados a partir de textos de Boletins de Ocorrência Policial (BOE) brasileiros, especialmente do sistema INFOPOL da Polícia Civil de Pernambuco.

Sua missão é extrair as informações do texto fornecido e retornar **somente** um JSON válido. Não inclua blocos markdown (como ```json) ou qualquer outro texto explicativo, apenas o JSON bruto.

---

### REGRAS DE CLASSIFICAÇÃO DE PAPÉIS DOS ENVOLVIDOS:

Mapeie os papéis encontrados no BOE para os arrays corretos do JSON. Considere os seguintes sinônimos e equivalências:

| Papel no BOE | Array de destino |
|---|---|
| VÍTIMA, VITIMA, OFENDIDO, VÍTIMA/LESADA | `vitimas` |
| AUTOR, AUTOR\\AGENTE, AGENTE, SUSPEITO, INDICIADO, ACUSADO, INFRATOR, INVESTIGADO, RÉU | `autores` |
| TESTEMUNHA, TESTEMUNHA OCULAR | `testemunhas` |
| CONDUTOR (quando explicitamente nomeado como condutor do veículo ou condutor do flagrante) | `condutor` |
| NOTICIANTE, COMUNICANTE, SOLICITANTE, DECLARANTE | `outros` (se não houver informação extra de condutor) |
| OUTRO, OUTROS, DESCONHECIDO, DESCONHECIDA, MENOR, ACOMPANHANTE, REPRESENTANTE LEGAL | `outros` |

⚠️ IMPORTANTE: Um envolvido pode ter mais de um papel (ex: "AUTOR\AGENTE") — use o papel PRINCIPAL para classificá-lo.
⚠️ IMPORTANTE: O array `condutor` deve ser preenchido SOMENTE se o texto do BOE indicar claramente um CONDUTOR (de veículo ou do flagrante). Caso contrário, deixe `condutor` como array vazio `[]`.
⚠️ IMPORTANTE: Nomes "DESCONHECIDA" ou "DESCONHECIDO" são válidos — inclua-os em `outros`.
⚠️ IMPORTANTE: Campos de identidade de gênero, orientação sexual e cor de pele NÃO devem ser extraídos — ignore-os completamente.

---

### REGRAS GERAIS DE EXTRAÇÃO E FORMATAÇÃO:

1. Retorne as chaves conforme o schema exato abaixo.
2. Todas as strings devem estar em MAIÚSCULO (exceto chaves do JSON).
3. Datas devem ser obrigatoriamente formatadas DD/MM/YYYY (ex: 9/8/1998 → 09/08/1998). Se não encontrar, envie "".
4. Hora deve ser formatada HH:MM. Se não encontrar, envie "".
5. CPF: apenas os números puros ou com máscara (###.###.###-##). Remova rótulos como "(CPF)".
6. RG: inclua o número E o órgão emissor (ex: "55262/PM/PE", "10683541/SDS/PE"). Remova rótulos desnecessários.
7. Telefone: remova "Telefones Celulares:", traços e espaços extras. Só números ou separados por vírgula.
8. Naturalidade: extraia APENAS a CIDADE e o ESTADO no formato exato: "CIDADE-UF" (ex: "SAO JOSE DO EGITO / PERNAMBUCO / BRASIL" → "SAO JOSE DO EGITO-PE", "NÃO INFORMADO / PERNAMBUCO / BRASIL" → ""). Nunca inclua "BRASIL" ni barra. Se for "NÃO INFORMADO", envie "".
9. Endereço: limpe sufixos como "Características", "Documentos", pontos e vírgulas extras. Formato: "Rua X, N, Bairro, Cidade".
10. Profissão: remova sufixos "(A)" ou texto redundante.
11. Escolaridade: simplifique (ex: "1°. GRAU INCOMPLETO" → "FUNDAMENTAL INCOMPLETO"). Se "NAO INFORMADO", envie "".
12. Estado civil: se "NAO INFORMADO", envie "".
13. Para `boe`: use apenas o número limpo do Boletim de Ocorrência (ex: "25E0257000128"). Ignore "Complemento" ou BOEs relacionados.
14. Para `delegacia`: use o nome completo da Delegacia/Unidade policial conforme constar no cabeçalho.
15. Para `natureza`: se houver múltiplas naturezas, junte todas separadas por " / ".
16. Campos com "NAO INFORMADO", "Ñ INFORMADO", "NÃO INFORMADO" → substitua por "".
17. Caso não encontre certa informação, preencha com "". Não crie propriedades além das descritas.
18. Os nomes nos arrays vitimas, autores, testemunhas, condutor e outros DEVEM bater EXATAMENTE com a chave dentro de envolvidos_detalhes.

---

### ESTRUTURA DO JSON ESPERADA:
{{
  "success": true,
  "dados": {{
    "boe": "número do BOE (somente o número principal)",
    "ip": "número do Inquérito Policial, se houver, senão vazio",
    "delegado": "nome da autoridade policial, senão vazio",
    "escrivao": "nome do escrivão, senão vazio",
    "delegacia": "nome da unidade policial/delegacia",
    "data_fato": "data do ocorrido (DD/MM/YYYY)",
    "hora_fato": "hora do ocorrido (HH:MM)",
    "end_fato": "endereço completo do local do fato",
    "natureza": "natureza(s) do fato — se múltiplas, unir com ' / '",
    "vitimas": ["NOME COMPLETO 1"],
    "autores": ["NOME COMPLETO 2"],
    "testemunhas": ["NOME COMPLETO 3"],
    "condutor": ["NOME COMPLETO 4"],
    "outros": ["NOME COMPLETO 5"],
    "envolvidos_detalhes": {{
      "NOME COMPLETO 1": {{
        "nome": "NOME COMPLETO 1",
        "alcunha": "apelido se houver, senão vazio",
        "nascimento": "DD/MM/YYYY",
        "cpf": "números do CPF",
        "rg": "número/ORGAO (ex: 10683541/SDS/PE)",
        "mae": "NOME DA MÃE",
        "pai": "NOME DO PAI",
        "estado_civil": "SOLTEIRO, CASADO, AMASIADO, etc.",
        "naturalidade": "CIDADE - UF",
        "profissao": "PROFISSÃO LIMPA",
        "telefone": "número(s) separados por vírgula",
        "endereco": "logradouro, número, bairro, cidade",
        "escolaridade": "grau de instrução simplificado"
      }}
    }}
  }}
}}

---

AGORA, EXTRAIA OS DADOS DO SEGUINTE TEXTO (PODE SER TEXTO COLADO OU CONTEÚDO EXTRAÍDO DE PDF VIA OCR):

{texto}
"""

    max_retries = 3
    for attempt in range(max_retries):
        try:
            response = client.models.generate_content(
                model='gemini-2.5-flash',
                contents=prompt,
            )
            texto_limpo = response.text.replace("```json", "").replace("```", "").strip()
            js_data = json.loads(texto_limpo)
            return js_data
        except Exception as e:
            err_str = str(e)
            if ('503' in err_str or '429' in err_str) and attempt < max_retries - 1:
                time.sleep(5)
                continue
            return {"success": False, "error": f"Erro na IA: {err_str}"}


if __name__ == "__main__":
    if len(sys.argv) < 2:
        fallback_json("Uso: python3 boe_extractor.py <caminho_arquivo>")

    file_path = sys.argv[1]
    texto = ler_arquivo(file_path)

    if not texto.strip():
        fallback_json("Arquivo vazio ou texto não localizável.")

    api_key = os.environ.get('GEMINI_API_KEY')
    if not api_key:
        try:
            env_path = os.path.join(os.path.dirname(__file__), '../../.env')
            with open(env_path, 'r', encoding='utf-8') as env_file:
                for line in env_file:
                    if line.startswith('GEMINI_API_KEY='):
                        api_key = line.split('=', 1)[1].strip()
                        break
        except:
            pass

    if not api_key:
        fallback_json("Chave API não encontrada.")

    resultado = extract_with_gemini(texto, api_key)
    print(json.dumps(resultado, ensure_ascii=False))
