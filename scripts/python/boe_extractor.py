import sys
import json
import os
import time
import re
import subprocess
import socket

# --- PATCH DE REDE MÁGICO (Força IPv4) ---
# Resolve o problema de servidores Linux VPS (Vultr/DO/etc) com rotas IPv6 quebradas 
# que causam timeouts de exatos 2 minutos nas conexões com APIs externas.
old_getaddrinfo = socket.getaddrinfo
def new_getaddrinfo(*args, **kwargs):
    responses = old_getaddrinfo(*args, **kwargs)
    return [res for res in responses if res[0] == socket.AF_INET]
socket.getaddrinfo = new_getaddrinfo


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

import urllib.request
import urllib.error

# --- Dependências ---
def ensure_package(module_name: str, pip_name: str):
    try:
        __import__(module_name)
    except ImportError:
        try:
            subprocess.check_call([sys.executable, "-m", "pip", "install", pip_name], 
                                  stdout=subprocess.DEVNULL, 
                                  stderr=subprocess.DEVNULL)
        except: pass

ensure_package('fitz', 'PyMuPDF')
import fitz

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
    abs_path = os.path.abspath(file_path)
    if not os.path.exists(file_path):
        return f"ERRO_DEBUG: Arquivo nao encontrado no caminho: {abs_path}"
    
    ext = os.path.splitext(file_path)[1].lower()
    try:
        if ext == '.pdf':
            try:
                doc = fitz.open(file_path)
            except Exception as e_fitz:
                return f"ERRO_DEBUG: Biblioteca PyMuPDF (fitz) falhou ao abrir o PDF: {str(e_fitz)}"
                
            content = "\n".join([page.get_text() for page in doc])
            doc.close()
            return limpar_texto_boe(content)
        else:
            with open(file_path, 'r', encoding='utf-8') as f:
                return limpar_texto_boe(f.read())
    except Exception as e_gen:
        return f"ERRO_DEBUG: Erro geral ao ler arquivo: {str(e_gen)}"

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

# --- Lógica de Chamada (Apenas DeepSeek) ---
def process_with_deepseek(texto, config):
    if not config['deepseek_key']:
        return {"success": False, "error": "Chave do DeepSeek não configurada no .env."}
    
    try:
        sys.stderr.write(f"[BOE-IA] Iniciando extração com DEEPSEEK...\n")
        res = call_deepseek(texto, config['deepseek_key'])
        match = re.search(r'\{.*\}', res, re.DOTALL)
        dados = json.loads(match.group(0) if match else res)
        sys.stderr.write(f"[BOE-IA] Extração concluída com DEEPSEEK\n")
        return dados
    except Exception as e:
        return {"success": False, "error": f"Falha na IA (DeepSeek): {str(e)}"}

if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument("file_path")
    parser.add_argument("--type", default='apfd') # Mantido por retrocompatibilidade
    args = parser.parse_args()

    texto = ler_arquivo(args.file_path)
    if texto.startswith("ERRO_DEBUG:"):
        fallback_json(texto)
    if not texto:
        fallback_json("O arquivo foi lido mas resultou em texto vazio.")

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

    resultado = process_with_deepseek(texto, config)
    if "success" in resultado and not resultado["success"]:
        # Erro real vindo da IA - já está no formato correto
        print(json.dumps(resultado, ensure_ascii=False))
    else:
        # Sucesso - embrulha no formato que o PHP espera: {"success": true, "dados": {...}}
        print(json.dumps({"success": True, "dados": resultado}, ensure_ascii=False))
