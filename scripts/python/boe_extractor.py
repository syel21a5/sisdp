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
import unicodedata
def normalizar_nome(nome: str) -> str:
    """Remove acentos e normaliza espaços para comparação."""
    nfkd = unicodedata.normalize('NFKD', nome)
    sem_acentos = ''.join(c for c in nfkd if not unicodedata.combining(c))
    return re.sub(r'\s+', ' ', sem_acentos).strip().upper()

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
    m_boe = re.search(r'N[^\d]+(\d+[A-Z]\d+)', texto, flags=re.IGNORECASE)
    if not m_boe:
        # Tenta achar o padrao puro do Boletim de Pernambuco (ex: 26E0257000953)
        m_boe = re.search(r'\b(\d{2,}[A-Z]\d{5,})\b', texto, flags=re.IGNORECASE)
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
        pessoas_matches = list(re.finditer(r'^([A-ZÀ-ÿ\s]+?)\s+\([^()]*?\)\s*Sexo:', txt_env, flags=re.MULTILINE))
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
            if m_natural:
                nat_raw = m_natural.group(1)
                nat_parts = [x.strip() for x in nat_raw.split('/') if x.strip() and x.strip() != 'BRASIL']
                if len(nat_parts) >= 2:
                    city = nat_parts[0]
                    state = nat_parts[1].upper()
                    st_map = {'ACRE':'AC','ALAGOAS':'AL','AMAPA':'AP','AMAPÁ':'AP','AMAZONAS':'AM','BAHIA':'BA','CEARA':'CE','CEARÁ':'CE','DISTRITO FEDERAL':'DF','ESPIRITO SANTO':'ES','ESPÍRITO SANTO':'ES','GOIAS':'GO','GOIÁS':'GO','MARANHAO':'MA','MARANHÃO':'MA','MATO GROSSO':'MT','MATO GROSSO DO SUL':'MS','MINAS GERAIS':'MG','PARA':'PA','PARÁ':'PA','PARAIBA':'PB','PARAÍBA':'PB','PARANA':'PR','PARANÁ':'PR','PERNAMBUCO':'PE','PIAUI':'PI','PIAUÍ':'PI','RIO DE JANEIRO':'RJ','RIO GRANDE DO NORTE':'RN','RIO GRANDE DO SUL':'RS','RONDONIA':'RO','RONDÔNIA':'RO','RORAIMA':'RR','SANTA CATARINA':'SC','SAO PAULO':'SP','SÃO PAULO':'SP','SERGIPE':'SE','TOCANTINS':'TO'}
                    if city in ['NÃO INFORMADO', 'NAO INFORMADO']:
                        p['naturalidade'] = 'NÃO INFORMADO'
                    else:
                        uf = st_map.get(state, state)
                        p['naturalidade'] = f"{city}-{uf}"
                elif len(nat_parts) == 1:
                    p['naturalidade'] = nat_parts[0]
            
            m_prof = re.search(r'Profissão:\s*([^\n;]+)', ficha)
            if m_prof:
                prof_raw = m_prof.group(1).strip()
                prof_raw = re.sub(r'(?i)\s*Telefones Celulares:?$', '', prof_raw).strip()
                p['profissao'] = prof_raw
                
            m_tel = re.search(r'Telefones Celulares:\s*\n-\s*([\d\s]+)', ficha)
            if m_tel: p['telefone'] = m_tel.group(1).strip()

            dados['envolvidos_detalhes'][nome_raw] = p

    # Reconciliar chaves de envolvidos_detalhes com os nomes das listas
    # (as listas podem ter acentos, mas as chaves do detalhe não)
    todos_nomes_listas = set()
    for k in ['vitimas', 'autores', 'testemunhas', 'condutor', 'outros']:
        todos_nomes_listas.update(dados[k])
    
    detalhes_reconciliados = {}
    for nome_lista in todos_nomes_listas:
        # Tenta chave exata primeiro
        if nome_lista in dados['envolvidos_detalhes']:
            detalhes_reconciliados[nome_lista] = dados['envolvidos_detalhes'][nome_lista]
            detalhes_reconciliados[nome_lista]['nome'] = nome_lista
            continue
        # Tenta match normalizado (sem acentos)
        nome_norm = normalizar_nome(nome_lista)
        for chave_det, val_det in dados['envolvidos_detalhes'].items():
            if normalizar_nome(chave_det) == nome_norm:
                val_det['nome'] = nome_lista
                detalhes_reconciliados[nome_lista] = val_det
                break
    
    # Mantém também chaves originais que não foram reconciliadas
    for chave_det, val_det in dados['envolvidos_detalhes'].items():
        if chave_det not in detalhes_reconciliados:
            nome_norm = normalizar_nome(chave_det)
            ja_existe = any(normalizar_nome(k) == nome_norm for k in detalhes_reconciliados)
            if not ja_existe:
                detalhes_reconciliados[chave_det] = val_det
    
    dados['envolvidos_detalhes'] = detalhes_reconciliados

    # Condutor
    m_condutor = re.search(r'Condutor da ocorrência:\s*\nNome:\s*(.*?)(?=\n|3\s+SARGENTO|MAT\.|Cargo:)', texto)
    if m_condutor:
        condutor_nome = m_condutor.group(1).strip().upper()
        # Remove eventuais titulos militares que escaparam
        condutor_nome = re.sub(r'(SARGENTO|CABO|SOLDADO|CAPITAO|TENENTE|MAJOR|CORONEL).*$', '', condutor_nome).strip()
        condutor_nome = re.sub(r'\d+\s*$', '', condutor_nome).strip()
        if condutor_nome not in dados['condutor']:
            dados['condutor'].append(condutor_nome)

    # --- NOVO: Extração de Objetos (Veículos e Celulares) ---
    bloco_objetos = re.search(r'Objetos\s*\n(.*?)(?=\nComplemento|\nHistórico|\nNarrativa|\nCondutor da ocorrência:|\Z)', texto, flags=re.DOTALL | re.IGNORECASE)
    if bloco_objetos:
        txt_obj = bloco_objetos.group(1)
        # Identifica cada item (VEICULO, CELULAR, etc)
        # O cabeçalho costuma ser algo como "NOME DO OBJETO (CATEGORIA)"
        itens = re.split(r'\n(?=[^:\n]+ \([^()\n]+\)\n)', "\n" + txt_obj)
        
        obj_list_text = []
        for item in itens:
            item = item.strip()
            if not item: continue
            
            # Pega o cabeçalho do item
            m_header = re.match(r'^([A-ZÀ-ÿ0-9/.\-\s]+) \(([^()]+)\)', item, re.IGNORECASE)
            if not m_header: continue
            
            nome_obj = m_header.group(1).strip().upper()
            categoria = m_header.group(2).strip().upper()
            obj_list_text.append(f"{nome_obj} ({categoria})")
            
            # --- Se for VEICULO ---
            if 'VEICULO' in categoria or 'VEÍCULO' in categoria or 'VEICULO' in nome_obj:
                v = {"marca_modelo": "", "placa": "", "chassi": "", "cor": ""}
                
                # Marca/Modelo
                m_v_mod = re.search(r'Categoria/Marca/Modelo:\s*([^/-]+)', item)
                if m_v_mod: v['marca_modelo'] = m_v_mod.group(1).replace('NÃO INFORMADO', '').strip(' /').strip()
                
                # Cor
                m_v_cor = re.search(r'Cor:\s*([A-ZÀ-ÿ]+)', item, re.IGNORECASE)
                if m_v_cor: v['cor'] = m_v_cor.group(1).strip()
                
                # Placa
                m_v_placa = re.search(r'Placa:\s*([A-Z0-9]{7})', item, re.IGNORECASE)
                if m_v_placa: v['placa'] = m_v_placa.group(1).strip()
                
                # Chassi
                m_v_chassi = re.search(r'Chassi:\s*([A-Z0-9]{17})', item, re.IGNORECASE)
                if m_v_chassi: v['chassi'] = m_v_chassi.group(1).strip()
                
                if v['placa'] or v['marca_modelo'] or v['chassi']:
                    dados['veiculos'].append(v)

            # --- Se for CELULAR ---
            elif 'TELEF' in categoria or 'CELULAR' in categoria or 'CELULAR' in nome_obj:
                c = {"marca_modelo": nome_obj, "imei1": "", "imei2": ""}
                
                # Busca por IMEIs (15 dígitos)
                imeis = re.findall(r'\b(\d{15})\b', item)
                if len(imeis) >= 1: c['imei1'] = imeis[0]
                if len(imeis) >= 2: c['imei2'] = imeis[1]
                
                # Se não achou na busca global, tenta label específica
                if not c['imei1']:
                    m_imei = re.search(r'IMEI\s*\d?:\s*(\d{15})', item, re.IGNORECASE)
                    if m_imei: c['imei1'] = m_imei.group(1)
                
                dados['celulares'].append(c)

        if obj_list_text:
            dados['objetos_apreendidos'] = " / ".join(obj_list_text)

    # Deduplicacao e Filtragem de Categorias (Hierarquia: Autores > Vitimas > Condutor > Testemunhas > Outros)
    for k in ['autores', 'vitimas', 'condutor', 'testemunhas', 'outros']:
        dados[k] = list(dict.fromkeys(dados[k]))
    
    def remove_from_list(target_list, to_remove_lists):
        for rem_list in to_remove_lists:
            target_list = [x for x in target_list if x not in rem_list]
        return target_list

    dados['vitimas'] = remove_from_list(dados['vitimas'], [dados['autores']])
    dados['condutor'] = remove_from_list(dados['condutor'], [dados['autores'], dados['vitimas']])
    dados['testemunhas'] = remove_from_list(dados['testemunhas'], [dados['autores'], dados['vitimas'], dados['condutor']])
    dados['outros'] = remove_from_list(dados['outros'], [dados['autores'], dados['vitimas'], dados['condutor'], dados['testemunhas']])

    # Validacao de Sucesso Inicial
    has_boe = bool(dados["boe"])
    has_pessoas = len(dados["envolvidos_detalhes"]) > 0
    # Checar se as pessoas tem nome
    all_ok = True
    for p in dados["envolvidos_detalhes"].values():
        if not p["nome"]:
            all_ok = False # Faltou o nome!
            
    # Nao vamos reprovar se faltar CPF, porque 'SOCIEDADE' nao tem CPF e muitas vitimas tbm nao.
    # Basta ter o BOE e pelo menos ter encontrado as categorias de envolvidos (vitimas/autores)
    has_categorias = len(dados['vitimas']) > 0 or len(dados['autores']) > 0 or len(dados['testemunhas']) > 0
    is_success = has_boe and (has_pessoas or has_categorias)
    return is_success, dados


def clean_boe_raw_text(texto: str) -> str:
    import re
    # Remove cabecalhos, rodapes de impressao e URLs geradas pelo PDF do INFOPOL
    texto = re.sub(r'\d{2}/\d{2}/\d{4},\s*\d{2}:\d{2}\s*\nSecretaria de Defesa Social\s*::\s*INFOPOL\s*\nhttps?://[^\n]+(?:\n\d+/\d+)?', '\n', texto, flags=re.IGNORECASE)
    
    # Remove textos discursivos gigantes (Histórico/Complemento) e para antes dos dados vitais finais 
    pattern_historico = r'(?:^|\n)(Complemento|Histórico(?:\s+da\s+ocorrência)?|Historico)[:\s]*\n.*?(?=\nCondutor da ocorrência:|\nB\.O\. registrado pelo policial:|\Z)'
    texto = re.sub(pattern_historico, '\n\n[HISTORICO/NARRATIVA REMOVIDO VIA PYTHON PARA ECONOMIA DE TOKENS DA IA]\n\n', texto, flags=re.DOTALL | re.IGNORECASE)

    # Limpeza final de espacamentos inuteis
    texto = re.sub(r'\n{3,}', '\n\n', texto)
    return texto.strip()

def fallback_json(error_msg: str):
    import json
    import sys
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

def ler_arquivo(file_path: str, clean_mode: bool = True) -> str:
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
            return clean_boe_raw_text(full_text) if clean_mode else full_text
        else:
            with open(file_path, 'r', encoding='utf-8') as f:
                raw = f.read()
            return clean_boe_raw_text(raw) if clean_mode else raw
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
    
    import time
    max_tentativas = 3
    for tentativa in range(1, max_tentativas + 1):
        try:
            sys.stderr.write(f"[BOE-IA] Tentativa {tentativa}/{max_tentativas} com DEEPSEEK...\n")
            res = call_deepseek(texto, config['deepseek_key'])
            match = re.search(r'\{.*\}', res, re.DOTALL)
            dados = json.loads(match.group(0) if match else res)
            sys.stderr.write(f"[BOE-IA] Extração concluída na tentativa {tentativa}\n")
            return dados
        except Exception as e:
            err_msg = str(e)
            sys.stderr.write(f"[BOE-IA] Tentativa {tentativa} falhou: {err_msg}\n")
            # Se for erro 503 (sobrecarga) ou timeout, espera e tenta de novo
            if tentativa < max_tentativas and ("503" in err_msg or "timeout" in err_msg.lower() or "unavailable" in err_msg.lower()):
                sys.stderr.write(f"[BOE-IA] Aguardando 5s antes da proxima tentativa...\n")
                time.sleep(5)
            else:
                return {"success": False, "error": f"Falha na IA (DeepSeek) apos {tentativa} tentativa(s): {err_msg}"}

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
    texto_raw_original = ler_arquivo(args.file_path, clean_mode=False) 
    
    if texto_raw_original.startswith("ERRO_DEBUG:"):
        fallback_json(texto_raw_original)
    if not texto_raw_original.strip():
        fallback_json("O arquivo foi lido mas resultou em texto vazio. Verifique se o PDF contém texto selecionável.")
        
    success_py, py_data = parse_boe_python(texto_raw_original)
    
    # MODO RÁPIDO (Padrão): Retorna resultado do Python IMEDIATAMENTE
    # A IA só é acionada se BOE_USE_AI=true estiver no .env (desligado por padrão)
    use_ai = os.environ.get('BOE_USE_AI', '').lower() == 'true'
    
    if success_py:
        # Python extraiu tudo com sucesso → retorna instantâneo
        print(json.dumps({"success": True, "dados": py_data}, ensure_ascii=False))
    elif use_ai:
        # MODO IA ATIVADO (apenas se BOE_USE_AI=true no .env)
        print("[SISTEMA] Acionando IA como Fallback (BOE_USE_AI=true)...", file=sys.stderr)
        texto_limpo_ia = clean_boe_raw_text(texto_raw_original)
        resultado = process_with_deepseek(texto_limpo_ia, config)
        
        if "success" in resultado and not resultado["success"]:
            final_json = {"success": True, "dados": py_data, "obs": "IA falhou, dados parciais do Python"}
            print(json.dumps(final_json, ensure_ascii=False))
        else:
            final_json = {"success": True, "dados": resultado}
            print(json.dumps(final_json, ensure_ascii=False))
    else:
        # MODO RÁPIDO: Python não extraiu tudo, mas retorna o que achou sem chamar IA
        # Campos faltantes ficam vazios para preenchimento manual
        print(json.dumps({"success": True, "dados": py_data, "obs": "Extração parcial via Python (IA desativada)"}, ensure_ascii=False))
            
    # Salva rastreio oculto para diagnostico
    try:
        with open("/tmp/debug_boe_trace.json", "w", encoding="utf-8") as ft:
            ft.write(json.dumps({"fonte": "PYTHON" if success_py else ("IA_DEEPSEEK" if use_ai else "PYTHON_PARCIAL"), "success_py": success_py, "dados": py_data}, ensure_ascii=False))
    except: pass

