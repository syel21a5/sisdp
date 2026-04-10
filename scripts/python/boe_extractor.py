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
def parse_boe_python(texto: str) -> dict:
    import re
    dados = {
        "boe": "", "ip": "", "natureza": "",
        "delegado": "", "escrivao": "", "delegacia": "",
        "data_fato": "", "hora_fato": "", "end_fato": "",
        "vitimas": [], "autores": [], "testemunhas": [],
        "condutor": [], "outros": [], "celulares": [], "veiculos": [],
        "objetos_apreendidos": "", "envolvidos_detalhes": {}
    }
    
    # Text cleaning
    texto = re.sub(r'\d{2}/\d{2}/\d{4},\s*\d{2}:\d{2}\s*\nSecretaria de Defesa Social\s*::\s*INFOPOL\s*\nhttps?://[^\n]+(?:\n\d+/\d+)?', '\n', texto, flags=re.IGNORECASE)
    
    # BOE
    m_boe = re.search(r'N[^\d]+(\d+[A-Z]\d+)', texto)
    if m_boe: dados["boe"] = m_boe.group(1).strip()
    
    # Natureza
    nats = re.findall(r'Natureza:(.*?)\nData:', texto, flags=re.DOTALL)
    if nats: dados["natureza"] = ' / '.join([n.strip().replace('\n', ' ') for n in nats])
    
    # Data/Hora
    m_data = re.search(r'Data:\s*([\d/]+)\s*Hora:\s*([\d:]+)', texto)
    if m_data:
        dados["data_fato"] = m_data.group(1).strip()
        dados["hora_fato"] = m_data.group(2).strip()
        
    m_end = re.search(r'Endereço do fato:(.*?)\nLocal do fato:', texto, flags=re.DOTALL)
    if m_end: dados["end_fato"] = m_end.group(1).strip().replace('\n', ' ')

    bloco_envolvidos_idx = re.findall(r'Envolvidos:\s*\n(.*?)(?=\nObjetos:|\nNatureza:|\nCondutor da ocorrência:|\nEnvolvidos\s*\n)', texto, flags=re.DOTALL)
    papel_map = {}
    for bloco in bloco_envolvidos_idx:
        for linha in bloco.strip().split('\n'):
            m = re.match(r'^(.*?)\s+\(\s*([^()]+?)\s*\)$', linha.strip())
            if m:
                nome = m.group(1).strip().upper()
                papel = m.group(2).strip().upper()
                papel_map[nome] = papel
                if 'VITIMA' in papel or 'VÍTIMA' in papel:
                    if nome not in dados['vitimas']: dados['vitimas'].append(nome)
                elif 'AUTOR' in papel or 'AGENTE' in papel or 'IMPUTADO' in papel or 'SUSPEITO' in papel:
                    if nome not in dados['autores']: dados['autores'].append(nome)
                elif 'TESTEMUNHA' in papel:
                    if nome not in dados['testemunhas']: dados['testemunhas'].append(nome)
                else:
                    if nome not in dados['outros']: dados['outros'].append(nome)

    bloco_envolvidos = re.search(r'Envolvidos\s*\n(.*?)(?=\nObjetos\s*\n|\nComplemento\s*\n|\Z)', texto, flags=re.DOTALL)
    if bloco_envolvidos:
        txt_env = bloco_envolvidos.group(1)
        pessoas_matches = list(re.finditer(r'^([A-Z\s]+?)\s+\([^()]*?\)\s*Sexo:', txt_env, flags=re.MULTILINE))
        for i, match in enumerate(pessoas_matches):
            nome_raw = match.group(1).strip().upper()
            start = match.end()
            end = pessoas_matches[i+1].start() if i+1 < len(pessoas_matches) else len(txt_env)
            ficha = txt_env[start:end]
            
            p = {
                "nome": nome_raw, "cpf": "", "rg": "", "nascimento": "", 
                "mae": "", "pai": "", "estado_civil": "", "naturalidade": "",
                "profissao": "", "telefone": "", "endereco": "", "escolaridade": ""
            }
            
            m_mae = re.search(r'Mãe:\s*([^\n;]+)', ficha)
            if m_mae: p['mae'] = m_mae.group(1).strip()
            
            m_pai = re.search(r'Pai:\s*([^\n;]+)', ficha)
            if m_pai: p['pai'] = m_pai.group(1).strip()
            
            m_nasc = re.search(r'Nascimento:\s*([\d/]+)', ficha)
            if m_nasc: p['nascimento'] = m_nasc.group(1).strip()
            
            m_cpf = re.search(r'(\d{11})\s*\(CPF\)', ficha)
            if m_cpf: p['cpf'] = m_cpf.group(1).strip()
            
            m_rg = re.search(r'([\d/]+[A-Za-z/]+)\s*\(RG\)', ficha)
            if m_rg: p['rg'] = m_rg.group(1).strip()

            m_natural = re.search(r'Naturalidade:\s*([^\n;]+)', ficha)
            if m_natural: p['naturalidade'] = m_natural.group(1).replace('/', ' / ').strip()
            
            m_prof = re.search(r'Profissão:\s*([^\n;]+)', ficha)
            if m_prof: p['profissao'] = m_prof.group(1).strip()
                
            m_tel = re.search(r'Telefones Celulares:\s*\n-\s*([\d\s]+)', ficha)
            if m_tel: p['telefone'] = m_tel.group(1).strip()

            dados['envolvidos_detalhes'][nome_raw] = p

    # Condutor
    m_condutor = re.search(r'Condutor da ocorrência:\s*\nNome:\s*(.*?)(?=\n|3\s+SARGENTO|MAT\.|Cargo:)', texto)
    if m_condutor:
        condutor_nome = m_condutor.group(1).strip().upper()
        # Remove eventuais titulos militares que escaparam
        condutor_nome = re.sub(r'(SARGENTO|CABO|SOLDADO|CAPITAO|TENENTE|MAJOR|CORONEL).*$', '', condutor_nome).strip()
        condutor_nome = re.sub(r'\d+\s*$', '', condutor_nome).strip()
        if condutor_nome not in dados['condutor']:
            dados['condutor'].append(condutor_nome)

    # Validacao de Sucesso Inicial
    has_boe = bool(dados["boe"])
    has_pessoas = len(dados["envolvidos_detalhes"]) > 0
    # Checar se as pessoas tem nome ou cpf minimamente
    all_ok = True
    for p in dados["envolvidos_detalhes"].values():
        if not p["cpf"] and not p["nascimento"]:
            all_ok = False # Faltou cpf ou nasc de alguem
            
    is_success = has_boe and has_pessoas and all_ok
    return is_success, dados


def clean_boe_raw_text(texto: str) -> str:
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
                
            full_text = ""
            for page in doc:
                full_text += page.get_text()
            doc.close()
            return clean_boe_raw_text(full_text)
        else:
            with open(file_path, 'r', encoding='utf-8') as f:
                return clean_boe_raw_text(f.read())
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
def call_deepseek(texto, api_key):
    url = "https://api.deepseek.com/chat/completions"
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {api_key}"
    }
    payload = {
        "model": "deepseek-chat",
        "messages": [
            {"role": "system", "content": "Você é um assistente especialista em extrair dados de Boletins de Ocorrência da Polícia Civil de Pernambuco."},
            {"role": "user", "content": get_prompt(texto)}
        ],
        "temperature": 0.1
    }
    
    req = urllib.request.Request(url, data=json.dumps(payload).encode(), headers=headers)
    with urllib.request.urlopen(req, timeout=60) as response:
        res = json.loads(response.read().decode())
        return res['choices'][0]['message']['content']

def process_with_deepseek(texto, config):
    if not config['deepseek_key'] or config['deepseek_key'] == '':
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

    deepseek_key = config['deepseek_key']
    gemini_key = config['gemini_keys'][0] if config['gemini_keys'] else None

    if not deepseek_key and not gemini_key:
        fallback_json("Nenhuma chave de IA (GEMINI_API_KEY ou DEEPSEEK_API_KEY) foi configurada.")

    # TENTA COMPLETAMENTE VIA PYTHON PRIMEIRO (Custo Zero)
    texto_raw_original = ler_arquivo(args.file_path, clean_mode=False) # Lemos sem cortar caso o Py falhe
    success_py, py_data = parse_boe_python(texto_raw_original)
    
    if success_py:
        print(json.dumps({"success": True, "dados": py_data}, ensure_ascii=False))
    else:
        # FALHOU O PYTHON - ACIONAR IA (LAPIDADA FINAL)
        print("[SISTEMA] Acionando IA como Fallback devido a formatação densa do PDF...", file=sys.stderr)
        texto_limpo_ia = clean_boe_raw_text(texto_raw_original)
        resultado = extract_with_ai(texto_limpo_ia, gemini_key or "", deepseek_key or "", args.type)
        if resultado.get("success"):
            print(json.dumps(resultado, ensure_ascii=False))
        else:
            # IA falhou tamem, retorna o q o python salvou
            print(json.dumps({"success": True, "dados": py_data, "obs": "Baseado puramente em regex (IA falhou)"}, ensure_ascii=False))
