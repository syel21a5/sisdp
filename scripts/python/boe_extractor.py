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
    """Remove acentos, prefixos de tipo e normaliza espaços para comparação."""
    if not nome: return ""
    # Remove prefixos comuns do BOE PMPE
    nome = re.sub(r'^(?:FISICA\s+PESSOA|JURIDICA\s+PESSOA|FISICA|JURIDICA):\s*', '', nome, flags=re.IGNORECASE)
    nfkd = unicodedata.normalize('NFKD', nome)
    sem_acentos = ''.join(c for c in nfkd if not unicodedata.combining(c))
    return re.sub(r'\s+', ' ', sem_acentos).strip().upper()

def remover_acentos(texto: str) -> str:
    """Remove acentos/diacríticos (ã→a, ç→c, é→e, etc.) sem alterar caixa."""
    if not texto:
        return texto
    nfkd = unicodedata.normalize('NFKD', texto)
    return ''.join(c for c in nfkd if not unicodedata.combining(c))

def _formatar_endereco(end_raw: str) -> str:
    """Reformata endereço do BOE mantendo partes originais mas limpando pontuação.
    Ex: 'RUA X ,99;CIDADE;PE' -> 'RUA X, 99; CIDADE/PE'
    """
    if not end_raw: return ""
    
    # 1. Separa por ; e limpa cada parte
    partes = [p.strip() for p in end_raw.split(';') if p.strip()]
    
    # 2. Mapa de estados para sigla
    st_map = {
        'ACRE':'AC','ALAGOAS':'AL','AMAPA':'AP','AMAPÁ':'AP','AMAZONAS':'AM','BAHIA':'BA',
        'CEARA':'CE','CEARÁ':'CE','DISTRITO FEDERAL':'DF','ESPIRITO SANTO':'ES','ESPÍRITO SANTO':'ES',
        'GOIAS':'GO','GOIÁS':'GO','MARANHAO':'MA','MARANHÃO':'MA','MATO GROSSO':'MT',
        'MATO GROSSO DO SUL':'MS','MINAS GERAIS':'MG','PARA':'PA','PARÁ':'PA','PARAIBA':'PB',
        'PARAÍBA':'PB','PARANA':'PR','PARANÁ':'PR','PERNAMBUCO':'PE','PIAUI':'PI','PIAUÍ':'PI',
        'RIO DE JANEIRO':'RJ','RIO GRANDE DO NORTE':'RN','RIO GRANDE DO SUL':'RS',
        'RONDONIA':'RO','RONDÔNIA':'RO','RORAIMA':'RR','SANTA CATARINA':'SC',
        'SAO PAULO':'SP','SÃO PAULO':'SP','SERGIPE':'SE','TOCANTINS':'TO'
    }
    ufs = set(st_map.values())
    
    # 3. Processa cada parte
    final_parts = []
    i = 0
    while i < len(partes):
        p = partes[i]
        
        # Ajusta vírgula: "RUA X ,99" -> "RUA X, 99"
        p = re.sub(r'\s*,\s*', ', ', p).strip()
        
        # Verifica se esta parte é cidade e a próxima é UF ou Estado por extenso
        if i + 1 < len(partes):
            prox_raw = partes[i+1].upper().strip()
            # Converte para sigla se for nome completo
            prox_uf = st_map.get(prox_raw, prox_raw)
            
            if prox_uf in ufs:
                # Une Cidade/UF
                p = f"{p}/{prox_uf}"
                i += 1 # Pula a próxima parte (UF)
        
        final_parts.append(p)
        i += 1
        
    # 4. Une por "; " (espaço após ponto e vírgula)
    return "; ".join(final_parts)


def parse_boe_python(texto: str) -> dict:
    import re
    dados = {
        "boe": "", "boe_pm": "", "ip": "", "natureza": "",
        "delegado": "", "escrivao": "", "delegacia": "",
        "data_fato": "", "hora_fato": "", "end_fato": "",
        "vitimas": [], "autores": [], "testemunhas": [],
        "condutor": [], "outros": [], "celulares": [], "veiculos": [],
        "objetos_apreendidos": "", "envolvidos_detalhes": {}
    }
    
    # 1. Identificação de Tipo de BO (PCPE vs PMPE) - DEVE SER ANTES DE QUALQUER LIMPEZA
    is_pcpe = "INFOPOL" in texto.upper() or "POLICIA CIVIL" in texto.upper()
    is_pmpe = ("POLICIA MILITAR" in texto.upper() or "SISBO" in texto.upper() or "2ª PARTE" in texto.upper() or "ENVOLVIDO Nº" in texto.upper()) and not is_pcpe

    # 2. Text cleaning (Remoção de lixo de cabeçalho INFOPOL)
    texto = re.sub(r'\d{2}/\d{2}/\d{4},\s*\d{2}:\d{2}\s*\nSecretaria de Defesa Social\s*::\s*INFOPOL\s*\nhttps?://[^\n]+(?:\n\d+/\d+)?', '\n', texto, flags=re.IGNORECASE)
    
    # 3. Extração de Números (BOE / BOPM)
    m_boe = re.search(r'BOLETIM DE OCORRÊNCIA Nº:\s*(\d+[A-Z]\d+|\d{10,})\b', texto, flags=re.IGNORECASE)
    if not m_boe:
        m_boe = re.search(r'N[^\d]+(\d+[A-Z]\d+)', texto, flags=re.IGNORECASE)
    if not m_boe:
        m_boe = re.search(r'\b(\d{2,}[A-Z]\d{5,})\b', texto, flags=re.IGNORECASE)
    
    if m_boe: 
        val = m_boe.group(1).strip()
        if is_pmpe:
            dados["boe_pm"] = val
            dados["boe"] = ""
        else:
            dados["boe"] = val
    
    # BO PM (Complementar ou Específico)
    m_bopm = re.search(r'(?:Complementa o )?BO\s*(?:PM)?\s*Número:\s*([A-Z0-9/\-]+)', texto, flags=re.IGNORECASE)
    if not m_bopm and is_pmpe:
        m_bopm = re.search(r'\b(M-?\d{7,})\b', texto, flags=re.IGNORECASE)
        
    if m_bopm:
        val_pm = m_bopm.group(1).strip()
        if is_pmpe:
            dados["boe_pm"] = val_pm
            dados["boe"] = ""
        else:
            # Se for Civil, salvamos o número da PM para informação, 
            # mas limpamos para o PHP não criar arquivo de cache duplicado
            dados["boe_pm"] = "" 
    
    if is_pcpe:
        # Garantia final: se é Civil, não deixa vazar número de PM para o cache
        dados["boe_pm"] = ""
    
    # Natureza
    nats = re.findall(r'Natureza:(.*?)\nData:', texto, flags=re.DOTALL)
    if nats: dados["natureza"] = remover_acentos(' / '.join([n.strip().replace('\n', ' ') for n in nats]))
    
    # Data/Hora
    m_data = re.search(r'Data:\s*([\d/]+)\s*Hora:\s*([\d:]+)', texto)
    if m_data:
        dados["data_fato"] = m_data.group(1).strip()
        dados["hora_fato"] = m_data.group(2).strip()
        
    m_end = re.search(r'Endereço do fato:(.*?)\nLocal do fato:', texto, flags=re.DOTALL)
    if m_end: 
        raw_end = m_end.group(1).strip().replace('\n', ' ')
        dados["end_fato"] = _formatar_endereco(raw_end)

    bloco_envolvidos_idx = re.findall(r'Envolvidos:\s*\n(.*?)(?=\nObjetos:|\nNatureza:|\nCondutor da ocorrência:|\nEnvolvidos\s*\n)', texto, flags=re.DOTALL)
    papel_map = {}
    for bloco in bloco_envolvidos_idx:
        for linha in bloco.strip().split('\n'):
            m = re.match(r'^(.*?)\s+\(\s*([^()]+?)\s*\)$', linha.strip())
            if m:
                nome = remover_acentos(m.group(1).strip().upper())
                papel = m.group(2).strip().upper()
                papel_map[nome] = papel
                if 'VITIMA' in papel or 'VÍTIMA' in papel or 'VITIMA' in remover_acentos(papel):
                    if nome not in dados['vitimas']: dados['vitimas'].append(nome)
                elif 'AUTOR' in papel or 'AGENTE' in papel or 'IMPUTADO' in papel or 'SUSPEITO' in papel or 'CAPTURADO' in papel or 'PRESO' in papel or 'DETIDO' in papel or 'CONDUZIDO' in papel:
                    if nome not in dados['autores']: dados['autores'].append(nome)
                elif 'TESTEMUNHA' in papel:
                    if nome not in dados['testemunhas']: dados['testemunhas'].append(nome)
                else:
                    if nome not in dados['outros']: dados['outros'].append(nome)

    bloco_envolvidos = re.search(r'Envolvidos\s*\n(.*?)(?=\nObjetos\s*\n|\nComplemento\s*\n|\Z)', texto, flags=re.DOTALL)
    if bloco_envolvidos:
        txt_env = bloco_envolvidos.group(1)
        
        # Branch para formato PMPE (ENVOLVIDO Nº X)
        is_pmpe_pessoas = bool(re.search(r'\nENVOLVIDO Nº\s*\d+', '\n' + txt_env, re.IGNORECASE))
        
        pessoas_raw = []
        if is_pmpe_pessoas:
            parts = re.split(r'\n(?=ENVOLVIDO Nº\s*\d+)', '\n' + txt_env, flags=re.IGNORECASE)
            for part in parts:
                if not part.strip(): continue
                # Tenta capturar o nome após "Pessoa:", garantindo que não pegue o "Tipo Pessoa:"
                m_nome = re.search(r'(?<!Tipo\s)Pessoa:\s*([^\n]+)', part, re.IGNORECASE)
                if m_nome:
                    nome = m_nome.group(1).strip()
                    # Se o nome capturado ainda começar com "FÍSICA " ou "JURÍDICA ", limpa
                    nome = re.sub(r'^(?:FÍSICA|JURÍDICA|FISICA|JURIDICA)\s+Pessoa:\s*', '', nome, flags=re.IGNORECASE).strip()
                    pessoas_raw.append((nome, part))
        else:
            pessoas_matches = list(re.finditer(r'^([A-ZÀ-ÿ\s]+?)\s+\([^()]*?\)\s*Sexo:', txt_env, flags=re.MULTILINE))
            for i, match in enumerate(pessoas_matches):
                nome = match.group(1).strip()
                start = match.end()
                end = pessoas_matches[i+1].start() if i+1 < len(pessoas_matches) else len(txt_env)
                pessoas_raw.append((nome, txt_env[start:end]))

        for nome_raw_match, ficha_raw in pessoas_raw:
            nome_raw = remover_acentos(nome_raw_match.upper())
            
            # ✅ FIX: Unir linhas quebradas pelo PDF para evitar truncamento de campos
            ficha = re.sub(r'\n(?!Mãe:|Pai:|Nascimento:|Data de Nascimento:|Naturalidade:|Documentos:|Estado\s+Civil:|Escolaridade:|Profissão:|Telefones?|Endereço|Características|Idade\s+aparente|Pessoa\s+com|[A-ZÀ-ÿ]{3,}[A-ZÀ-ÿ\s]+\([^()]*\)\s*Sexo:)', ' ', ficha_raw)
            
            p = {
                "nome": nome_raw, "cpf": "", "rg": "", "nascimento": "", 
                "mae": "", "pai": "", "estado_civil": "", "naturalidade": "",
                "profissao": "", "telefone": "", "endereco": "", "escolaridade": "",
                "alcunha": ""
            }
            
            # Mãe: captura até o próximo campo ou fim de seção
            m_mae = re.search(r'Mãe:\s*([^;\n]+?)(?=;\s*Pai:|Pai:|\n|$)', ficha, re.IGNORECASE)
            if m_mae: 
                mae = re.sub(r'\s+', ' ', m_mae.group(1)).strip().rstrip(';').strip()
                if mae.upper() not in ['NÃO INFORMADO', 'NAO INFORMADO', 'N/A']: p['mae'] = mae.upper()
            
            # Pai: captura até o próximo campo ou fim
            m_pai = re.search(r'Pai:\s*([^;\n]+?)(?=;\s*Nascimento:|Nascimento:|\s*Data de Nascimento:|\n|$)', ficha, re.IGNORECASE)
            if m_pai: 
                pai = re.sub(r'\s+', ' ', m_pai.group(1)).strip().rstrip(';').strip()
                if pai.upper() not in ['NÃO INFORMADO', 'NAO INFORMADO', 'N/A']: p['pai'] = pai.upper()
            
            m_nasc = re.search(r'(?:Data de )?Nascimento:\s*([\d/]+)', ficha, re.IGNORECASE)
            if m_nasc: p['nascimento'] = m_nasc.group(1).strip()
            
            # CPF: Extrair e FORMATAR
            m_cpf = re.search(r'(\d{11})\s*\(CPF\)', ficha)
            if not m_cpf: m_cpf = re.search(r'CPF:\s*(\d{11})', ficha)
            if m_cpf:
                cpf_raw = m_cpf.group(1).strip()
                p['cpf'] = f"{cpf_raw[:3]}.{cpf_raw[3:6]}.{cpf_raw[6:9]}-{cpf_raw[9:]}"
            
            m_rg = re.search(r'([\d/]+[A-Za-z/]+)\s*\(RG\)', ficha)
            if not m_rg: 
                m_rg2 = re.search(r'RG:\s*([^\s]+)', ficha)
                if m_rg2 and m_rg2.group(1) not in ['NÃO', 'NAO']: p['rg'] = m_rg2.group(1)
            else: p['rg'] = m_rg.group(1).strip()

            # Alcunha/Apelido
            m_alc = re.search(r'Apelido:\s*([^;\n]+)', ficha, re.IGNORECASE)
            if m_alc:
                alc = m_alc.group(1).strip().upper()
                if alc not in ['NAO INFORMADO', 'NÃO INFORMADO', '']:
                    p['alcunha'] = alc

            # Naturalidade:
            # Naturalidade:
            m_natural = re.search(r'Naturalidade:\s*([^;\n]+?)(?=Documentos:|Estado\s+Civil:|\n|$)', ficha, re.IGNORECASE)
            if m_natural:
                nat_raw = m_natural.group(1).strip().rstrip(';').strip().upper()
                # Tenta separar por / ou apenas pegar o valor se for único
                nat_parts = [x.strip() for x in re.split(r'[/;]', nat_raw) if x.strip() and x.strip() != 'BRASIL']
                if len(nat_parts) >= 2:
                    city = nat_parts[0]
                    state = nat_parts[1]
                    st_map = {'ACRE':'AC','ALAGOAS':'AL','AMAPA':'AP','AMAPÁ':'AP','AMAZONAS':'AM','BAHIA':'BA','CEARA':'CE','CEARÁ':'CE','DISTRITO FEDERAL':'DF','ESPIRITO SANTO':'ES','ESPÍRITO SANTO':'ES','GOIAS':'GO','GOIÁS':'GO','MARANHAO':'MA','MARANHÃO':'MA','MATO GROSSO':'MT','MATO GROSSO DO SUL':'MS','MINAS GERAIS':'MG','PARA':'PA','PARÁ':'PA','PARAIBA':'PB','PARAÍBA':'PB','PARANA':'PR','PARANÁ':'PR','PERNAMBUCO':'PE','PIAUI':'PI','PIAUÍ':'PI','RIO DE JANEIRO':'RJ','RIO GRANDE DO NORTE':'RN','RIO GRANDE DO SUL':'RS','RONDONIA':'RO','RONDÔNIA':'RO','RORAIMA':'RR','SANTA CATARINA':'SC','SAO PAULO':'SP','SÃO PAULO':'SP','SERGIPE':'SE','TOCANTINS':'TO'}
                    if city not in ['NÃO INFORMADO', 'NAO INFORMADO']:
                        uf = st_map.get(state, state)
                        p['naturalidade'] = f"{city} / {uf}"
                elif len(nat_parts) == 1:
                    val = nat_parts[0]
                    if val not in ['NAO', 'NÃO', 'NÃO INFORMADO', 'NAO INFORMADO']:
                        p['naturalidade'] = f"{val} / NÃO INFORMADO"
            
            # Profissão: captura até Telefones ou fim de seção
            m_prof = re.search(r'Profissão:\s*(.+?)(?=Telefones|$)', ficha, re.IGNORECASE)
            if m_prof:
                prof_raw = m_prof.group(1).strip().rstrip(';').strip()
                prof_raw = re.sub(r'(?i)\s*Telefones\s+(?:Celulares|Fixos):?$', '', prof_raw).strip()
                if prof_raw.upper() not in ['NAO INFORMADO', 'NÃO INFORMADO']:
                    p['profissao'] = prof_raw

            # Estado Civil: ex "Estado Civil: SOLTEIRO(A);"
            m_ec = re.search(r'Estado\s+Civil:\s*([^;]+)', ficha, re.IGNORECASE)
            if m_ec: 
                ec_val = m_ec.group(1).strip()
                if ec_val.upper() not in ['NAO INFORMADO', 'NÃO INFORMADO']:
                    p['estado_civil'] = ec_val

            # Escolaridade: captura até ; ou Profissão
            m_esc = re.search(r'(?:Escolaridade|Grau\s+de\s+Instru[çc][ãa]o):\s*(.+?)(?=;\s*Profissão|;\s*Profiss|Profissão|$)', ficha, re.IGNORECASE)
            if m_esc:
                esc_val = m_esc.group(1).strip().rstrip(';').strip()
                if esc_val.upper() not in ['NAO', 'NÃO', 'NAO INFORMADO', 'NÃO INFORMADO']:
                    p['escolaridade'] = esc_val

            # Telefone: tenta celular primeiro, depois fixo
            m_tel = re.search(r'Telefones?\s+Celulares?:\s*\n\s*-\s*\(?(\d{2})\)?\s*([\d\-\s\.]+)', ficha_raw)
            if m_tel:
                num_clean = re.sub(r'[\s\.]+', '', m_tel.group(2).strip())
                # Adiciona o espaço e hífen formatado, se for celular 9 dígitos
                if len(num_clean) == 9 and '-' not in num_clean:
                    num_clean = f"{num_clean[:5]}-{num_clean[5:]}"
                elif '-' not in num_clean and len(num_clean) == 8:
                    num_clean = f"{num_clean[:4]}-{num_clean[4:]}"
                p['telefone'] = f"({m_tel.group(1)}) {num_clean}"
            else:
                # Fallback: tenta telefone fixo
                m_tel2 = re.search(r'Telefones?\s+Fixos?:\s*\n\s*-\s*\(?(\d{2})\)?\s*([\d\-\s\.]+)', ficha_raw)
                if m_tel2:
                    num_clean2 = re.sub(r'[\s\.]+', '', m_tel2.group(2).strip())
                    if '-' not in num_clean2 and len(num_clean2) == 8:
                        num_clean2 = f"{num_clean2[:4]}-{num_clean2[4:]}"
                    p['telefone'] = f"({m_tel2.group(1)}) {num_clean2}"
                else:
                    # Fallback genérico: qualquer telefone no formato (XX) XXXXX-XXXX
                    m_tel3 = re.search(r'\((\d{2})\)\s*([\d\-\s\.]+)', ficha_raw)
                    if m_tel3:
                        num_clean3 = re.sub(r'[\s\.]+', '', m_tel3.group(2).strip())
                        if len(num_clean3) == 9 and '-' not in num_clean3:
                            num_clean3 = f"{num_clean3[:5]}-{num_clean3[5:]}"
                        elif '-' not in num_clean3 and len(num_clean3) == 8:
                            num_clean3 = f"{num_clean3[:4]}-{num_clean3[4:]}"
                        p['telefone'] = f"({m_tel3.group(1)}) {num_clean3}"

            # ✅ FIX: Endereço — Captura multi-linha e reformata na ordem:
            # Rua, Número, Bairro, Cidade, Estado
            enderecos = []
            # Ajustado para pegar "Endereço:" puro (comum no BO PM) ou Residencial/Comercial
            for m_end in re.finditer(r'Endere[çc]o\s*(?::|Residencial:|Comercial:)\s*(.+?)(?=\nEndere[çc]o|\nDados\s+Profissionais|\n[A-ZÀ-ÿ]{3,}[A-ZÀ-ÿ\s]+\([^()]*\)|Caracter[ií]sticas|$)', ficha_raw, re.IGNORECASE | re.DOTALL):
                end_raw = m_end.group(1).strip()
                # Junta linhas quebradas numa só
                end_raw = re.sub(r'\s*\n\s*', ' ', end_raw).strip()
                if end_raw and end_raw.upper() not in ['NÃO INFORMADO', 'NAO INFORMADO', 'N/A']:
                    enderecos.append(end_raw)
            
            if enderecos:
                formatted_addrs = []
                for end_raw in enderecos:
                    formatted_addrs.append(_formatar_endereco(end_raw))
                p['endereco'] = ' / '.join(formatted_addrs)

            # ✅ FIX: Remover acentos/caracteres especiais de TODOS os campos extraídos
            for key in p:
                if isinstance(p[key], str) and p[key]:
                    p[key] = remover_acentos(p[key])

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
        condutor_nome = remover_acentos(m_condutor.group(1).strip().upper())
        # Remove eventuais titulos militares que escaparam
        condutor_nome = re.sub(r'(SARGENTO|CABO|SOLDADO|CAPITAO|TENENTE|MAJOR|CORONEL).*$', '', condutor_nome).strip()
        condutor_nome = re.sub(r'\d+\s*$', '', condutor_nome).strip()
        if condutor_nome not in dados['condutor']:
            dados['condutor'].append(condutor_nome)

    # --- NOVO: Extração de Objetos (Veículos e Celulares) ---
    bloco_objetos = re.search(r'Objetos\s*\n(.*?)(?=\nComplemento|\nDados Complementares|\nHistórico|\nNarrativa|\nCondutor da ocorrência:|\Z)', texto, flags=re.DOTALL | re.IGNORECASE)
    if bloco_objetos:
        txt_obj = bloco_objetos.group(1)
        obj_list_text = []

        # Detecta se é o formato PMPE (OBJETO Nº X) ou PCPE (NOME (CATEGORIA))
        is_pmpe = bool(re.search(r'\nOBJETO Nº\s*\d+', '\n' + txt_obj, re.IGNORECASE))

        if is_pmpe:
            # Separar por OBJETO Nº
            itens = re.split(r'\n(?=OBJETO Nº\s*\d+)', '\n' + txt_obj, flags=re.IGNORECASE)
            
            for item in itens:
                item = item.strip()
                if not item: continue
                
                m_tipo = re.search(r'Tipo do Objeto:\s*([^\n]+)', item, re.IGNORECASE)
                m_cat = re.search(r'Categoria do Objeto:\s*([^\n]+)', item, re.IGNORECASE)
                
                if not m_tipo: continue
                
                nome_obj = m_tipo.group(1).strip().upper()
                categoria = m_cat.group(1).strip().upper() if m_cat else "NÃO INFORMADO"
                
                detalhes = []
                
                m_marca = re.search(r'Marca:\s*(.*?)(?=\s*Modelo:|\s*Cor:|\n|\Z)', item, re.IGNORECASE)
                m_modelo = re.search(r'Modelo:\s*(.*?)(?=\s*Cor:|\n|\Z)', item, re.IGNORECASE)
                m_cor = re.search(r'Cor:\s*(.*?)(?=\s*Caracter[ií]sticas|\n|\Z)', item, re.IGNORECASE)
                
                marca = m_marca.group(1).strip() if m_marca else ""
                modelo = m_modelo.group(1).strip() if m_modelo else ""
                cor = m_cor.group(1).strip() if m_cor else ""
                
                # Monta a Categoria/Marca/Modelo equivalente ao PCPE
                marca_modelo_parts = []
                if marca and 'NÃO' not in marca.upper() and 'NAO' not in marca.upper(): marca_modelo_parts.append(marca)
                if modelo and 'NÃO' not in modelo.upper() and 'NAO' not in modelo.upper(): marca_modelo_parts.append(modelo)
                marca_modelo_val = " ".join(marca_modelo_parts)
                if marca_modelo_val:
                    detalhes.append(f"Mod: {marca_modelo_val}")
                
                if cor and 'NÃO' not in cor.upper() and 'NAO' not in cor.upper():
                    detalhes.append(f"Cor: {cor}")

                # Para celular extra
                c = {"marca_modelo": marca_modelo_val or nome_obj, "imei1": "", "imei2": "", "proprietario": ""}
                
                # Extrai IMEIs usando Regex
                imeis = re.findall(r'\b(\d{15})\b', item)
                
                imei1_m = re.search(r'Imei 1:\s*(\d{15})', item, re.IGNORECASE)
                imei2_m = re.search(r'Imei 2:\s*(\d{15})', item, re.IGNORECASE)
                
                if imei1_m: c['imei1'] = imei1_m.group(1)
                elif len(imeis) >= 1: c['imei1'] = imeis[0]
                
                if imei2_m: c['imei2'] = imei2_m.group(1)
                elif len(imeis) >= 2: c['imei2'] = imeis[1]

                m_serie = re.search(r'N[úu]mero de S[ée]rie:\s*(.*?)(?=\s*Objeto Apreendido|\n|\Z)', item, re.IGNORECASE)
                if m_serie:
                    serie_val = m_serie.group(1).strip()
                    if serie_val and 'NÃO' not in serie_val.upper() and 'NAO' not in serie_val.upper():
                        detalhes.append(f"Série: {serie_val}")
                        if not c['imei1']: c['imei1'] = serie_val
                
                m_carac = re.search(r'Caracter[ií]sticas\s*(?:Adicionais)?:\s*(.*?)(?=\s*Quantidade:|\nImei|\n|\Z)', item, re.IGNORECASE | re.DOTALL)
                if m_carac:
                    carac_val = re.sub(r'\s+', ' ', m_carac.group(1)).strip()
                    if carac_val and 'NÃO' not in carac_val.upper() and 'NAO' not in carac_val.upper():
                        detalhes.append(f"Desc: {carac_val}")
                
                m_qtd = re.search(r'Quantidade:\s*([^\n]+)', item, re.IGNORECASE)
                if m_qtd:
                    qtd_val = m_qtd.group(1).strip()
                    if qtd_val and 'NÃO' not in qtd_val.upper() and 'NAO' not in qtd_val.upper():
                        detalhes.append(f"Qtd: {qtd_val}")

                is_celular = 'TELEF' in categoria or 'CELULAR' in categoria or 'CELULAR' in nome_obj
                if is_celular:
                    if c['imei1'] and 'Série:' not in ''.join(detalhes): detalhes.append(f"IMEI1: {c['imei1']}")
                    if c['imei2']: detalhes.append(f"IMEI2: {c['imei2']}")
                    dados['celulares'].append(c)
                elif 'VEIC' in categoria or 'VEÍC' in categoria or 'VEIC' in nome_obj or 'AUTOM' in categoria or 'MOTO' in categoria:
                    # Veiculo PM - tenta placa e chassi no bloco
                    v = {"marca_modelo": marca_modelo_val, "placa": "", "chassi": "", "cor": cor, "proprietario": ""}
                    
                    m_v_placa = re.search(r'Placa\s*[:\-]?\s*([A-Z]{3}[-\s]?[0-9][A-Z0-9][0-9]{2}|[A-Z]{3}[-\s]?[0-9]{4})\b', item, re.IGNORECASE)
                    if m_v_placa: v['placa'] = re.sub(r'[\-\s]', '', m_v_placa.group(1).upper()).strip()
                    
                    m_v_chassi = re.search(r'Chassi\s*[:\-]?\s*([A-Z0-9]{17})\b', item, re.IGNORECASE)
                    if m_v_chassi: v['chassi'] = m_v_chassi.group(1).upper().strip()
                    
                    if v['placa']: detalhes.append(f"Placa: {v['placa']}")
                    if v['chassi']: detalhes.append(f"Chassi: {v['chassi']}")
                    
                    dados['veiculos'].append(v)
                
                desc = f"{nome_obj} ({categoria})"
                if detalhes:
                    desc += " - " + ", ".join(detalhes)
                obj_list_text.append(desc)

        else:
            # Lógica PCPE
            itens = re.split(r'\n(?=[^:\n]+ \([^()\n]+\)\n)', "\n" + txt_obj)
            if len(itens) <= 2 and not txt_obj.startswith('\n'):
                 itens = re.split(r'(?<=\n)(?=[^:\n]+ \([^()\n]+\)(?:\s|$))', txt_obj)
            
            for item in itens:
                item = item.strip()
                if not item: continue
                
                # Pega o cabeçalho do item (suportando espaços bagunçados)
                m_header = re.match(r'^([A-ZÀ-ÿ0-9/.\-\s]+?)\s*\(([^()]+)\)', item, re.IGNORECASE)
                if not m_header: continue
                
                nome_obj = m_header.group(1).strip().upper()
                categoria = m_header.group(2).strip().upper()
                
                # --- Se for VEICULO ---
                if 'VEICULO' in categoria or 'VEÍCULO' in categoria or 'VEICULO' in nome_obj:
                    v = {"marca_modelo": "", "placa": "", "chassi": "", "cor": "", "proprietario": ""}
                    
                    # Marca/Modelo
                    m_v_mod = re.search(r'Categoria/Marca/Modelo:\s*(.*?)(?=\s*(?:\n|Cor:|Placa:|Chassi:|Propriet[aá]rio|Condutor|Respons[aá]vel|-|\/|\Z))', item, re.IGNORECASE | re.DOTALL)
                    if m_v_mod: 
                        mod_raw = m_v_mod.group(1).replace('NÃO INFORMADO', '').strip(' /').strip()
                        v['marca_modelo'] = re.sub(r'\s+', ' ', mod_raw)
                    
                    m_v_cor = re.search(r'Cor:\s*([A-ZÀ-ÿ]+)', item, re.IGNORECASE)
                    if m_v_cor: v['cor'] = m_v_cor.group(1).strip()
                    
                    m_v_placa = re.search(r'Placa\s*[:\-]?\s*([A-Z]{3}[-\s]?[0-9][A-Z0-9][0-9]{2}|[A-Z]{3}[-\s]?[0-9]{4})\b', item, re.IGNORECASE)
                    if not m_v_placa: m_v_placa = re.search(r'\b([A-Z]{3}[-\s]?[0-9][A-Z0-9][0-9]{2}|[A-Z]{3}[-\s]?[0-9]{4})\b', item, re.IGNORECASE)
                    if m_v_placa: v['placa'] = re.sub(r'[\-\s]', '', m_v_placa.group(1).upper()).strip()
                    
                    m_v_chassi = re.search(r'Chassi\s*[:\-]?\s*([A-Z0-9]{17})\b', item, re.IGNORECASE)
                    if not m_v_chassi: m_v_chassi = re.search(r'\b([A-Z0-9]{17})\b', item, re.IGNORECASE)
                    if m_v_chassi: v['chassi'] = m_v_chassi.group(1).upper().strip()
    
                    m_v_prop = re.search(r'(?:Propriet[aá]rio|Proprietario|Condutor|Respons[aá]vel|Em\s+nome\s+de|Vinculado\s+a|Pertencente\s+a)\s*:\s*([^\n;]+)', item, re.IGNORECASE)
                    if m_v_prop:
                        prop_raw = m_v_prop.group(1).strip()
                        prop_raw = re.split(r'\b(?:CPF|RG|NASCIMENTO|ENDERECO|ENDEREÇO|MAE|MÃE|PAI)\b', prop_raw, flags=re.IGNORECASE)[0].strip()
                        prop_raw = re.sub(r'\s*\([^)]*\)\s*$', '', prop_raw).strip()
                        prop_raw = re.sub(r'[^A-ZÀ-ÿ\s]', ' ', prop_raw, flags=re.IGNORECASE)
                        prop_raw = re.sub(r'\s+', ' ', prop_raw).strip()
                        prop_raw = remover_acentos(prop_raw).upper()
                        if prop_raw and prop_raw not in ['NAO INFORMADO', 'NÃO INFORMADO', 'N/A']:
                            v['proprietario'] = prop_raw
                    
                    detalhes = []
                    if v['marca_modelo']: detalhes.append(f"Marca/Modelo: {v['marca_modelo']}")
                    if v['placa']: detalhes.append(f"Placa: {v['placa']}")
                    if v['chassi']: detalhes.append(f"Chassi: {v['chassi']}")
                    if v['cor']: detalhes.append(f"Cor: {v['cor']}")
                    
                    desc = f"{nome_obj} ({categoria})"
                    if detalhes:
                        desc += " - " + ", ".join(detalhes)
                    obj_list_text.append(desc)
    
                    if v['placa'] or v['marca_modelo'] or v['chassi']:
                        dados['veiculos'].append(v)
    
                # --- Se for CELULAR ---
                elif 'TELEF' in categoria or 'CELULAR' in categoria or 'CELULAR' in nome_obj:
                    c = {"marca_modelo": nome_obj, "imei1": "", "imei2": "", "proprietario": ""}
                    
                    # Busca por IMEIs (15 dígitos)
                    imeis = re.findall(r'\b(\d{15})\b', item)
                    if len(imeis) >= 1: c['imei1'] = imeis[0]
                    if len(imeis) >= 2: c['imei2'] = imeis[1]
                    
                    if not c['imei1']:
                        m_imei = re.search(r'IMEI\s*\d?:\s*(\d{15})', item, re.IGNORECASE)
                        if m_imei: c['imei1'] = m_imei.group(1)
    
                    m_c_prop = re.search(r'(?:Propriet[aá]rio|Proprietario|Condutor|Respons[aá]vel|Em\s+nome\s+de|Vinculado\s+a|Pertencente\s+a)\s*:\s*([^\n;]+)', item, re.IGNORECASE)
                    if m_c_prop:
                        prop_raw = m_c_prop.group(1).strip()
                        prop_raw = re.split(r'\b(?:CPF|RG|NASCIMENTO|ENDERECO|ENDEREÇO|MAE|MÃE|PAI)\b', prop_raw, flags=re.IGNORECASE)[0].strip()
                        prop_raw = re.sub(r'\s*\([^)]*\)\s*$', '', prop_raw).strip()
                        prop_raw = re.sub(r'[^A-ZÀ-ÿ\s]', ' ', prop_raw, flags=re.IGNORECASE)
                        prop_raw = re.sub(r'\s+', ' ', prop_raw).strip()
                        prop_raw = remover_acentos(prop_raw).upper()
                        if prop_raw and prop_raw not in ['NAO INFORMADO', 'NÃO INFORMADO', 'N/A']:
                            c['proprietario'] = prop_raw
                    
                    detalhes = []
                    m_cat = re.search(r'Categoria/Marca/Modelo:\s*([^\n-]+)', item, re.IGNORECASE)
                    if m_cat:
                        cat_val = m_cat.group(1).replace('NÃO INFORMADO', '').replace('NAO INFORMADO', '').strip(' /').strip()
                        if cat_val:
                            detalhes.append(f"Mod: {cat_val}")
                            c['marca_modelo'] = cat_val
    
                    m_desc = re.search(r'Descri[çc][ãa]o:\s*(.*?)(?=\s*N[úu]mero de S[ée]rie|Cor|Quantidade|Valor Unit[áa]rio|\Z)', item, re.IGNORECASE | re.DOTALL)
                    if m_desc:
                        desc_val = re.sub(r'\s+', ' ', m_desc.group(1)).strip()
                        if desc_val and 'NÃO INFORMADO' not in desc_val.upper():
                            detalhes.append(f"Desc: {desc_val}")
    
                    m_serie = re.search(r'N[úu]mero de S[ée]rie:\s*(.*?)(?=\s*Cor|Quantidade|Valor Unit[áa]rio|\n|\Z)', item, re.IGNORECASE)
                    serie_val = ""
                    if m_serie:
                        serie_val = re.sub(r'\s+', ' ', m_serie.group(1)).strip()
                        if serie_val and 'NÃO INFORMADO' not in serie_val.upper() and 'NAO INFORMADO' not in serie_val.upper():
                            detalhes.append(f"Série: {serie_val}")
                            if not c['imei1']:
                                # Se não achou IMEI pela regex de 15 dígitos, usa o que tiver em Número de Série
                                c['imei1'] = serie_val
    
                    if c['imei1'] and 'Série:' not in ''.join(detalhes): detalhes.append(f"IMEI1: {c['imei1']}")
                    if c['imei2']: detalhes.append(f"IMEI2: {c['imei2']}")
                    
                    desc = f"{nome_obj} ({categoria})"
                    if detalhes:
                        desc += " - " + ", ".join(detalhes)
                    obj_list_text.append(desc)
    
                    dados['celulares'].append(c)
                
                else:
                    detalhes = []
                    m_cat = re.search(r'Categoria/Marca/Modelo:\s*([^\n-]+)', item, re.IGNORECASE)
                    if m_cat:
                        cat_val = m_cat.group(1).replace('NÃO INFORMADO', '').replace('NAO INFORMADO', '').strip(' /').strip()
                        if cat_val:
                            detalhes.append(f"Mod: {cat_val}")
                    
                    m_desc = re.search(r'Descri[çc][ãa]o:\s*(.*?)(?=\s*N[úu]mero de S[ée]rie|Cor|Quantidade|Valor Unit[áa]rio|\Z)', item, re.IGNORECASE | re.DOTALL)
                    if m_desc:
                        desc_val = re.sub(r'\s+', ' ', m_desc.group(1)).strip()
                        if desc_val and 'NÃO INFORMADO' not in desc_val.upper():
                            detalhes.append(f"Desc: {desc_val}")
    
                    m_serie = re.search(r'N[úu]mero de S[ée]rie:\s*(.*?)(?=\s*Cor|Quantidade|Valor Unit[áa]rio|\n|\Z)', item, re.IGNORECASE)
                    if m_serie:
                        serie_val = re.sub(r'\s+', ' ', m_serie.group(1)).strip()
                        if serie_val and 'NÃO INFORMADO' not in serie_val.upper() and 'NAO INFORMADO' not in serie_val.upper():
                            detalhes.append(f"Série: {serie_val}")
    
                    m_qtd = re.search(r'Quantidade:\s*([^\n]+?)(?=\s*Valor Unit[áa]rio|\n|\Z)', item, re.IGNORECASE)
                    if m_qtd:
                        qtd_val = m_qtd.group(1).replace('UNIDADE NÃO INFORMADA', '').replace('UNIDADE NAO INFORMADA', '').strip(' ()').strip()
                        if qtd_val and qtd_val.upper() not in ['NAO INFORMADO', 'NÃO INFORMADO', 'UNIDADE NAO INFORMADA']:
                            detalhes.append(f"Qtd: {qtd_val}")
    
                    desc = f"{nome_obj} ({categoria})"
                    if detalhes:
                        desc += " - " + ", ".join(detalhes)
    
                    obj_list_text.append(desc)

        if obj_list_text:
            dados['objetos_apreendidos'] = "\n".join(obj_list_text)

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

    proprietario_padrao = ''
    for k in ['autores', 'condutor', 'vitimas', 'testemunhas', 'outros']:
        if dados.get(k) and len(dados[k]) > 0:
            proprietario_padrao = dados[k][0]
            break

    if proprietario_padrao:
        for v in dados.get('veiculos', []):
            if isinstance(v, dict) and not v.get('proprietario'):
                v['proprietario'] = proprietario_padrao
        for c in dados.get('celulares', []):
            if isinstance(c, dict) and not c.get('proprietario'):
                c['proprietario'] = proprietario_padrao

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
    return f"""Extraia todos os dados deste Boletim de Ocorrência (BOE) e retorne APENAS um JSON válido.

IMPORTANTE SOBRE OBJETOS APREENDIDOS:
1. Localize a seção "Objetos" e extraia TODOS os itens listados. 
2. NÃO resuma nem agrupe itens diferentes.
3. Para cada objeto, inclua: [Tipo/Nome] - Marca: [Marca], Mod: [Modelo], Cor: [Cor], Qtd: [Quantidade], Desc: [Descrição completa].
4. Se for um celular, inclua também o IMEI ou Número de Série na descrição.
5. Separe cada objeto por uma quebra de linha (\\n) dentro da string "objetos_apreendidos".

ESTRUTURA DO JSON:
{{
  "boe": "número completo",
  "ip": "número do procedimento se houver",
  "delegado": "nome",
  "escrivao": "nome",
  "delegacia": "nome da unidade",
  "data_fato": "DD/MM/AAAA",
  "hora_fato": "HH:MM",
  "end_fato": "endereço completo formatado",
  "natureza": "todas as naturezas separadas por /",
  "objetos_apreendidos": "Item 1\\nItem 2\\nItem 3...",
  "vitimas": ["NOME 1", "NOME 2"],
  "autores": ["NOME 1", "NOME 2"],
  "testemunhas": ["NOME 1"],
  "condutor": ["NOME"],
  "outros": [],
  "veiculos": [{{"marca_modelo": "", "placa": "", "chassi": "", "cor": ""}}],
  "celulares": [{{"marca_modelo": "", "imei1": "", "imei2": ""}}],
  "envolvidos_detalhes": {{
     "NOME COMPLETO": {{
        "nome": "NOME COMPLETO",
        "cpf": "000.000.000-00",
        "rg": "0000000/SDS/PE",
        "nascimento": "DD/MM/AAAA",
        "mae": "NOME DA MÃE",
        "pai": "NOME DO PAI",
        "profissao": "CARGO",
        "endereco": "ENDEREÇO COMPLETO"
     }}
  }}
}}

REGRAS ADICIONAIS:
- JAMAIS extraia o nome do policial que apenas registrou o BO no sistema.
- Se um campo não for encontrado, deixe-o vazio ("").
- Mantenha os nomes em CAIXA ALTA.

TEXTO DO BOE:
{texto}"""

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
    if use_ai and not deepseek_key and not gemini_key:
        sys.stderr.write("[BOE-IA] BOE_USE_AI=true, mas nenhuma chave (GEMINI/DEEPSEEK) foi configurada. IA desativada.\n")
        use_ai = False
    
    if success_py:
        # Python extraiu tudo com sucesso → retorna instantâneo
        # ✅ FIX: Inclui texto_raw para que o JS possa usar como fallback (para uploads de PDF)
        py_data['texto_raw'] = texto_raw_original
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
        py_data['texto_raw'] = texto_raw_original
        print(json.dumps({"success": True, "dados": py_data, "obs": "Extração parcial via Python (IA desativada)"}, ensure_ascii=False))
            
    # Salva rastreio oculto para diagnostico
    try:
        with open("/tmp/debug_boe_trace.json", "w", encoding="utf-8") as ft:
            ft.write(json.dumps({"fonte": "PYTHON" if success_py else ("IA_DEEPSEEK" if use_ai else "PYTHON_PARCIAL"), "success_py": success_py, "dados": py_data}, ensure_ascii=False))
    except: pass
