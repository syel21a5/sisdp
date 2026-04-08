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
        __import__(module_name)  # type: ignore[import]
    except ImportError:
        try:
            subprocess.check_call([sys.executable, "-m", "pip", "install", pip_name])
            __import__(module_name)  # type: ignore[import]
        except Exception as e:
            print(json.dumps({"success": False, "error": f"A biblioteca '{pip_name}' não está instalada no Python local '{sys.executable}' e a instalação falhou."}))
            sys.exit(1)

ensure_package('google.genai', 'google-genai')
ensure_package('openai', 'openai')
ensure_package('fitz', 'PyMuPDF')

from google import genai  # type: ignore
import fitz  # type: ignore  # PyMuPDF
try:
    from openai import OpenAI
except:
    pass

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

def extract_with_ai(texto: str, gemini_key: str, deepseek_key: str, ext_type: str = 'apfd'):
    use_deepseek = bool(deepseek_key)

    # Prompt base com regras comuns
    prompt_base = rf"""Você é um sistema especializado em extração de dados estruturados a partir de textos de Boletins de Ocorrência Policial (BOE) brasileiros.
Sua missão é extrair as informações solicitadas e retornar **somente** um JSON válido, sem formato markdown.

REGRAS GERAIS:
1. Todas as strings devem estar em MAIÚSCULO (exceto chaves do JSON).
2. Para `boe`: extraia apenas o número limpo do Boletim de Ocorrência (ex: "25E0257000128").
3. Para `ip`: inquérito policial (se houver).
4. Em caso de não encontrar dados, use os formatos vazios especificados na estrutura.
5. **PROIBIÇÃO ABSOLUTA**: JAMAIS extraia o nome do profissional que apenas efetuou o registro (geralmente indicado como "B.O. registrado pelo policial: ..."). Esse nome DEVE SER COMPLETAMENTE IGNORADO e NUNCA pode ser incluído em nenhuma lista de envolvidos (vítimas, autores, condutor, testemunhas, outros).
"""

    if ext_type == 'veiculo':
        prompt = prompt_base + rf"""
Foque APENAS em dados relacionados a VEÍCULOS e as pessoas diretamente ligadas a eles (proprietários ou condutores).
IGNORE aparelhos celulares, drogas, armas e outras pessoas não relacionadas a veículos.

ESTRUTURA ESPERADA ("success" sempre true se retornou formato JSON corretamente, "dados" com os dados extraídos):
{{
  "success": true,
  "dados": {{
    "boe": "número do BOE",
    "ip": "número do IP se houver",
    "veiculos": [
      {{
        "marca_modelo": "MARCA E MODELO DO VEÍCULO",
        "placa": "PLACA (AAA-0000 ou AAA0A00)",
        "chassi": "NÚMERO DO CHASSI ou 'SUPRIMIDA / NAO IDENTIFICADA'",
        "cor": "COR",
        "proprietario": "NOME DO PROPRIETÁRIO (Se houver)"
      }}
    ],
    "pessoas_relacionadas": ["Lista apenas com nomes envolvidos com o veículo"]
  }}
}}

TEXTO DO BOE:
{texto}
"""
    elif ext_type == 'celular':
        prompt = prompt_base + rf"""
Foque APENAS em dados relacionados a CELULARES/TELEFONES MÓVEIS e os proprietários.
IGNORE veículos, drogas e outras testemunhas não vinculadas aos celulares.

ESTRUTURA ESPERADA ("success" sempre true se retornou formato JSON corretamente, "dados" com os dados extraídos):
{{
  "success": true,
  "dados": {{
    "boe": "número do BOE",
    "ip": "número do IP se houver",
    "hora_fato": "hora do ocorrido (HH:MM Se houver)",
    "celulares": [
      {{
        "marca_modelo": "MARCA E MODELO DO CELULAR",
        "imei1": "IMEI 1 (15 dígitos)",
        "imei2": "IMEI 2 (15 dígitos)",
        "cor": "COR",
        "proprietario": "NOME DO PROPRIETÁRIO DO CELULAR (Se houver)"
      }}
    ],
    "pessoas_relacionadas": ["Lista apenas com nomes envolvidos com o celular"]
  }}
}}

TEXTO DO BOE:
{texto}
"""
    elif ext_type == 'intimacao':
        prompt = prompt_base + rf"""
Foque EXCLUSIVAMENTE nos DADOS BÁSICOS do BOE e em TODAS AS PESSOAS físicas citadas.
Isto é para um formulário de INTIMAÇÃO.
Como uma Intimação NÃO trata de bens materiais e foca apenas em chamar as partes para depor, VOCÊ DEVE IGNORAR COMPLETAMENTE aparelhos celulares, veículos, drogas, armas e qualquer outro objeto apreendido. Foque 100% nas pessoas e nos detalhes delas, para economizar tempo de análise.

REGRAS ESPECÍFICAS PARA PESSOAS:
- 'vitimas': Nomes das Vítimas (VÍTIMA, OFENDIDO, SOCIEDADE).
- 'autores': Nomes dos Autores (AUTOR, SUSPEITO, INDICIADO, IMPUTADO).
- 'testemunhas': Nomes das Testemunhas.
- 'condutor': ESTRITAMENTE o Policial (Condutor da ocorrência). Nunca coloque o suspeito aqui.
- 'outros': Outros envolvidos (COMUNICANTE, NOTICIANTE).
- UMA MESMA PESSOA NÃO PODE ESTAR EM DUAS CATEGORIAS SIMULTANEAMENTE.

ESTRUTURA ESPERADA:
{{
  "success": true,
  "dados": {{
    "boe": "número BOE",
    "delegado": "Delegado",
    "escrivao": "Escrivao",
    "delegacia": "Delegacia",
    "cidade": "Cidade",
    "vitimas": ["NOME 1"],
    "autores": ["NOME 2"],
    "testemunhas": ["NOME 3"],
    "condutor": [],
    "outros": ["NOME 4"],
    "envolvidos_detalhes": {{
      "NOME DA PESSOA EM MAIÚSCULO": {{
        "nome": "NOME REGISTRADO (OBRIGATÓRIO)",
        "cpf": "CPF",
        "rg": "RG",
        "nascimento": "NASCIMENTO (DD/MM/YYYY)",
        "mae": "Mãe",
        "pai": "Pai",
        "estado_civil": "Estado Civil",
        "naturalidade": "CIDADE / ESTADO (Não coloque o país)",
        "profissao": "Profissão",
        "telefone": "Telefone ou Celular se listado",
        "endereco": "Endereço Completo",
        "escolaridade": "Grau de Instrução"
      }}
    }}
  }}
}}

TEXTO DO BOE:
{texto}
"""
    elif ext_type == 'administrativo':
        prompt = prompt_base + rf"""
Foque APENAS nos dados principais do caso (Natureza), objetos apreendidos gerais e as PESSOAS envolvidas.

REGRAS ESPECÍFICAS PARA PESSOAS:
- 'vitimas': Nomes das Vítimas (VÍTIMA, OFENDIDO, SOCIEDADE).
- 'autores': Nomes dos Autores (AUTOR, SUSPEITO, INDICIADO, IMPUTADO). Mesmo que o texto diga "estava conduzindo o veículo", ele é o AUTOR, não condutor policial.
- 'testemunhas': Nomes das Testemunhas.
- 'condutor': ESTRITAMENTE a pessoa listada expressamente como "Condutor da ocorrência:" (pode ser PM, PC, Guarda, etc). Nunca coloque o criminoso/traficante aqui NEM O POLICIAL QUE REGISTROU O BO. Se o tópico "Condutor da ocorrência" não existir, deixe a lista vazia.
- 'outros': Outros envolvidos (COMUNICANTE, NOTICIANTE, etc).
- REGRA DE OURO 1: DEVE RETORNAR APENAS AS STRINGS (Nomes completos em maiúsculo). Não extraia CPF ou RG aqui.
- REGRA DE OURO 2: UMA MESMA PESSOA NÃO PODE ESTAR EM DUAS CATEGORIAS SIMULTANEAMENTE. Escolha apenas a categoria MAIS RELEVANTE para cada pessoa. Nunca duplique o mesmo nome.

ESTRUTURA ESPERADA:
{{
  "success": true,
  "dados": {{
    "boe": "número do BOE",
    "ip": "número do IP se houver",
    "natureza": "natureza do fato — se múltiplas, unir com ' / '",
    "hora_fato": "HH:MM",
    "end_fato": "Endereço completo do fato",
    "vitimas": ["NOME 1"],
    "autores": ["NOME 2"],
    "testemunhas": ["NOME 3"],
    "condutor": [],
    "outros": ["NOME 4"],
    "celulares": [],
    "veiculos": [],
    "objetos_apreendidos": "Separe os objetos com quebras de linha explícitas (\\n). Use ponto e vírgula (;) no final de cada objeto, e finalize o último objeto com um ponto final (.)."
  }}
}}

TEXTO DO BOE:
{texto}
"""
    else:
        # Padrão APFD completo
        prompt = prompt_base + rf"""
Foque em extrair os dados estruturados do BOE para o formulário completo do inquérito.
MAPEAMENTO DE PESSOAS: 
- 'vitimas' (VÍTIMA/OFENDIDO)
- 'autores' (IMPUTADO/SUSPEITO/AUTOR) - Mesmo que ele "conduzia" o veículo roubado, a função dele é AUTOR.
- 'testemunhas'
- 'condutor' - ISSO É EXCLUSIVO PARA O "Condutor da ocorrência" explícito no documento (pode ser PM, PC, Guarda Municipal ou qualquer pessoa em tal campo). NÃO confunda com motorista de veículo infrator. SE NÃO HOUVER UM CAMPO "Condutor da ocorrência:", DEIXE VAZIO!
- 'outros' (NOTICIANTE/COMUNICANTE).

REGRAS CRÍTICAS PARA PESSOAS:
1. UMA MESMA PESSOA NÃO PODE ESTAR EM DUAS CATEGORIAS SIMULTANEAMENTE.
2. O "IMPUTADO" nunca pode ser o "CONDUTOR". 
3. **NUNCA EXTRAIR O POLICIAL QUE REGISTROU O BO.** Quem digitou o BO ("B.O. registrado pelo policial:") não teve envolvimento no caso. NUNCA extraia esse nome para nenhuma array de pessoas!
4. **REGRA DE CONDUTOR ÚNICO**: Extraia APENAS a pessoa que consta explicitamente sob o rótulo 'Condutor da ocorrência:'. Se houver outros policiais da guarnição, coloque-os em 'testemunhas'. A lista 'condutor' deve ter no máximo 1 nome.
5. **FILTRO DE PESSOAS**: Extraia apenas pessoas que aparecem nas seções de qualificação (Envolvidos). IGNORE nomes citados apenas no texto narrativo (Histórico) se eles não estiverem qualificados no BOE.

ESTRUTURA ESPERADA:
{{
  "success": true,
  "dados": {{
    "boe": "número BOE", "ip": "IP", "delegado": "Delegado", "escrivao": "Escrivao", "delegacia": "Delegacia",
    "data_fato": "DATA", "hora_fato": "HORA", "end_fato": "ENDERECO", "natureza": "NATUREZAS",
    "objetos_apreendidos": "SEPARE CADA OBJETO COM \\n. USE PONTO E VÍRGULA (;) NO FINAL DE CADA OBJETO, E PONTO FINAL (.) NO ÚLTIMO.",
    "celulares": [ {{"marca_modelo": "", "imei1": "", "imei2": "", "cor": "", "proprietario": ""}} ],
    "veiculos": [ {{"marca_modelo": "", "placa": "", "chassi": "", "cor": "", "proprietario": ""}} ],
    "vitimas": [], "autores": [], "testemunhas": [], "condutor": [], "outros": [],
    "envolvidos_detalhes": {{
      "NOME 1": {{"nome": "NOME 1", "cpf": "", "rg": "", "nascimento": "", "mae": "", "pai": "", "estado_civil": "", "naturalidade": "CIDADE / ESTADO (Não coloque o país)", "profissao": "", "telefone": "", "endereco": "", "escolaridade": ""}}
    }}
  }}
}}

TEXTO DO BOE:
{texto}
"""

    max_retries = 3
    for attempt in range(max_retries):
        try:
            import re
            if use_deepseek:
                client = OpenAI(api_key=deepseek_key, base_url="https://api.deepseek.com")
                response = client.chat.completions.create(
                    model="deepseek-chat",
                    messages=[
                        {"role": "system", "content": "Você é um assistente especializado em extrair dados de documentos e retornar ESTRITAMENTE em formato JSON."},
                        {"role": "user", "content": prompt}
                    ],
                    response_format={"type": "json_object"}
                )
                content = response.choices[0].message.content
            else:
                client = genai.Client(api_key=gemini_key)
                response = client.models.generate_content(
                    model='gemini-2.5-flash',
                    contents=prompt,
                )
                content = response.text
                
            match = re.search(r'\{.*\}', content, re.DOTALL)
            if match:
                texto_limpo = match.group(0)
            else:
                texto_limpo = content.replace("```json", "").replace("```", "").strip()
            
            js_data = json.loads(texto_limpo)
            return js_data
        except Exception as e:
            err_str = str(e)
            if ('503' in err_str or '429' in err_str or '502' in err_str) and attempt < max_retries - 1:
                time.sleep(5)
                continue
            provider_name = "DeepSeek" if use_deepseek else "Gemini"
            return {"success": False, "error": f"Erro na IA ({provider_name}): {err_str}"}

import argparse

if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument("file_path", nargs="?", help="Caminho do arquivo BOE")
    parser.add_argument("--type", choices=['veiculo', 'celular', 'administrativo', 'apfd', 'intimacao'], default='apfd', help="Tipo de extração otimizada")
    args = parser.parse_args()

    if not args.file_path:
        fallback_json("Uso: python3 boe_extractor.py <caminho_arquivo> [--type t]")

    texto = ler_arquivo(args.file_path)

    if not texto.strip():
        fallback_json("Arquivo vazio ou texto não localizável.")

    gemini_key = os.environ.get('GEMINI_API_KEY')
    deepseek_key = os.environ.get('DEEPSEEK_API_KEY')
    
    try:
        env_path = os.path.join(os.path.dirname(__file__), '../../.env')
        if os.path.exists(env_path):
            with open(env_path, 'r', encoding='utf-8') as env_file:
                for line in env_file:
                    if line.startswith('GEMINI_API_KEY=') and not gemini_key:
                        gemini_key = line.split('=', 1)[1].strip()
                    if line.startswith('DEEPSEEK_API_KEY=') and not deepseek_key:
                        deepseek_key = line.split('=', 1)[1].strip()
    except:
        pass

    if not deepseek_key and not gemini_key:
        fallback_json("Nenhuma chave de IA (GEMINI_API_KEY ou DEEPSEEK_API_KEY) foi configurada.")

    resultado = extract_with_ai(texto, gemini_key or "", deepseek_key or "", args.type)
    print(json.dumps(resultado, ensure_ascii=False))
