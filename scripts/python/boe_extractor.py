import sys
import json
import os
import time
import random
import re
import subprocess

# --- Configuração de Ambiente para Windows ---
# Configuração de Ambiente (Apenas Windows)
if os.name == 'nt':
    try:
        import site
        user_site = site.getusersitepackages()
        if user_site and user_site not in sys.path:
            sys.path.append(user_site)
        appdata = os.environ.get('APPDATA')
        if appdata:
            py_ver = f"Python{sys.version_info.major}{sys.version_info.minor}"
            hard_path = os.path.join(appdata, "Python", py_ver, "site-packages")
            if os.path.exists(hard_path) and hard_path not in sys.path:
                sys.path.append(hard_path)
    except:
        pass

# --- Dependências ---
def ensure_package(module_name: str, pip_name: str):
    try:
        __import__(module_name)
    except ImportError:
        try:
            subprocess.check_call([sys.executable, "-m", "pip", "install", pip_name])
        except: pass

ensure_package('google.genai', 'google-genai')
ensure_package('openai', 'openai')
ensure_package('fitz', 'PyMuPDF')

import fitz
try:
    from google import genai
    from openai import OpenAI
except: pass

# --- utilitários ---
def fallback_json(error_msg: str):
    print(json.dumps({"success": False, "error": error_msg}))
    sys.exit(1)

def limpar_texto_boe(texto: str) -> str:
    """Remove lixo do BOE para economizar tokens."""
    marcos_fim = [r"\nComplemento\s*\n", r"\nHistórico\s*\n", r"\nNarrativa\s*\n"]
    texto_limpo = texto
    for marco in marcos_fim:
        partes = re.split(marco, texto_limpo, flags=re.IGNORECASE)
        if len(partes) > 1:
            texto_limpo = partes[0]
            break
    padroes_lixo = [
        r"GOVERNO DO ESTADO DE PERNAMBUCO", r"SECRETARIA\s*DE\s*DEFESA\s*SOCIAL",
        r"POLICIA\s*CIVIL\s*DE\s*PERNAMBUCO", r"BOLETIM DE OCORRÊNCIA\s*\(COMPLETO\)",
        r"Pág\s*\d+/\d+", r"B\.O\. registrado pelo policial:.*"
    ]
    linhas = texto_limpo.split('\n')
    linhas_filtradas = [l.strip() for l in linhas if l.strip() and not any(re.search(p, l, re.I) for p in padroes_lixo)]
    return "\n".join(linhas_filtradas)

def ler_arquivo(file_path: str) -> str:
    if not os.path.exists(file_path): return ""
    ext = os.path.splitext(file_path)[1].lower()
    try:
        if ext == '.pdf':
            doc = fitz.open(file_path)
            content = "\n".join([page.get_text() for page in doc])
            doc.close()
            return limpar_texto_boe(content)
        else:
            with open(file_path, 'r', encoding='utf-8') as f:
                return limpar_texto_boe(f.read())
    except: return ""

# --- Chamadas de IA ---
def get_prompt(texto):
    return f"""Extraia do BOE e retorne APENAS um JSON:
{{
  "boe": "numero", "ip": "numero", "delegado": "nome", "escrivao": "nome", "delegacia": "nome", "data_fato": "data", "hora_fato": "hora", "end_fato": "endereco", "natureza": "texto",
  "objetos_apreendidos": "lista com \\n",
  "vitimas": [], "autores": [], "testemunhas": [], "condutor": [], "outros": [],
  "veiculos": [{{"marca_modelo": "", "placa": "", "chassi": "", "cor": ""}}],
  "celulares": [{{"marca_modelo": "", "imei1": "", "imei2": ""}}],
  "envolvidos_detalhes": {{ "NOME": {{nome, cpf, rg, nascimento, mae, pai, profissao, endereco}} }}
}}
IMPORTANTE: JAMAIS extraia o policial que registrou o BO.
TEXTO: {texto}"""

def call_gemini(texto, key):
    client = genai.Client(api_key=key)
    response = client.models.generate_content(model='gemini-1.5-flash', contents=get_prompt(texto))
    return response.text

def call_groq(texto, key):
    client = OpenAI(api_key=key, base_url="https://api.groq.com/openai/v1")
    response = client.chat.completions.create(
        model="llama3-70b-8192",
        messages=[{"role": "user", "content": get_prompt(texto)}],
        response_format={"type": "json_object"}
    )
    return response.choices[0].message.content

def call_deepseek(texto, key):
    client = OpenAI(api_key=key, base_url="https://api.deepseek.com")
    response = client.chat.completions.create(
        model="deepseek-chat",
        messages=[{"role": "user", "content": get_prompt(texto)}],
        response_format={"type": "json_object"}
    )
    return response.choices[0].message.content

# --- Lógica de Rodízio ---
def process_with_rotation(texto, config):
    free_pool = []
    for k in config['gemini_keys']: free_pool.append({'type': 'gemini', 'key': k})
    for k in config['groq_keys']: free_pool.append({'type': 'groq', 'key': k})
    
    # Embaralha as chaves gratuitas para o rodízio
    random.shuffle(free_pool)
    
    for provider in free_pool:
        try:
            if provider['type'] == 'gemini': res = call_gemini(texto, provider['key'])
            else: res = call_groq(texto, provider['key'])
            match = re.search(r'\{.*\}', res, re.DOTALL)
            return json.loads(match.group(0) if match else res)
        except: continue
            
    if config['deepseek_key']:
        try:
            res = call_deepseek(texto, config['deepseek_key'])
            match = re.search(r'\{.*\}', res, re.DOTALL)
            return json.loads(match.group(0) if match else res)
        except Exception as e:
            return {"success": False, "error": f"Falha geral. Erro backup: {str(e)}"}
            
    return {"success": False, "error": "Nenhuma IA disponível."}

if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument("file_path")
    parser.add_argument("--type", default='apfd') # Mantido por retrocompatibilidade
    args = parser.parse_args()

    texto = ler_arquivo(args.file_path)
    if not texto: fallback_json("Arquivo não legível.")

    config = {'gemini_keys': [], 'groq_keys': [], 'deepseek_key': ''}
    try:
        env_path = os.path.join(os.path.dirname(__file__), '../../.env')
        if os.path.exists(env_path):
            with open(env_path, 'r', encoding='utf-8') as f:
                for line in f:
                    if '=' in line:
                        k, v = line.strip().split('=', 1)
                        if k == 'GEMINI_API_KEYS': config['gemini_keys'] = [x.strip() for x in v.split(',')]
                        if k == 'GROQ_API_KEYS': config['groq_keys'] = [x.strip() for x in v.split(',')]
                        if k == 'DEEPSEEK_API_KEY': config['deepseek_key'] = v
    except: pass

    resultado = process_with_rotation(texto, config)
    if "success" not in resultado: resultado["success"] = True
    print(json.dumps(resultado, ensure_ascii=False))
