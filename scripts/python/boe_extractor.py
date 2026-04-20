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

def remover_acentos(texto: str) -> str:
    """Remove acentos/diacríticos (ã→a, ç→c, é→e, etc.) sem alterar caixa."""
    if not texto:
        return texto
    nfkd = unicodedata.normalize('NFKD', texto)
    return ''.join(c for c in nfkd if not unicodedata.combining(c))

def _formatar_endereco(end_raw: str) -> str:
    """Reformata endereço do BOE (separado por ;) para a ordem:
    Rua, Número, Bairro, Cidade, Estado.
    
    Formato BOE típico: 
    'RUA PEDRO IVO , 144; RUA PEDRO IVO, BORGES; 0; CENTRO; AFOGADOS DA INGAZEIRA; PERNAMBUCO; BRASIL; BAR DA SEPARACAO'
    Ou: 'MUNICIPIO DE AFOGADOS DA INGAZEIRA, 97; RUA CLETO CAMPELO; 55000-000'
    """
    # Separa por ;
    partes = [p.strip() for p in end_raw.split(';') if p.strip()]
    
    # Remove BRASIL, CEPs (00000-000 ou 55000-000) e zeros puros
    limpo = []
    for p in partes:
        p_upper = p.upper().strip()
        if p_upper == 'BRASIL':
            continue
        if re.match(r'^\d{5}-?\d{3}$', p_upper):
            continue
        if p_upper == '0' or p_upper == '00':
            continue
        # Remove estado por extenso se já temos cidade
        limpo.append(p)
    
    if not limpo:
        return end_raw
    
    # Mapa de estados para abreviação
    st_map = {'ACRE':'AC','ALAGOAS':'AL','AMAPA':'AP','AMAPÁ':'AP','AMAZONAS':'AM','BAHIA':'BA',
              'CEARA':'CE','CEARÁ':'CE','DISTRITO FEDERAL':'DF','ESPIRITO SANTO':'ES','ESPÍRITO SANTO':'ES',
              'GOIAS':'GO','GOIÁS':'GO','MARANHAO':'MA','MARANHÃO':'MA','MATO GROSSO':'MT',
              'MATO GROSSO DO SUL':'MS','MINAS GERAIS':'MG','PARA':'PA','PARÁ':'PA','PARAIBA':'PB',
              'PARAÍBA':'PB','PARANA':'PR','PARANÁ':'PR','PERNAMBUCO':'PE','PIAUI':'PI','PIAUÍ':'PI',
              'RIO DE JANEIRO':'RJ','RIO GRANDE DO NORTE':'RN','RIO GRANDE DO SUL':'RS',
              'RONDONIA':'RO','RONDÔNIA':'RO','RORAIMA':'RR','SANTA CATARINA':'SC',
              'SAO PAULO':'SP','SÃO PAULO':'SP','SERGIPE':'SE','TOCANTINS':'TO'}
    
    # Identifica o estado (se houver) e converte para sigla
    estado_uf = ''
    partes_sem_estado = []
    for p in limpo:
        p_upper = p.upper().strip()
        if p_upper in st_map:
            estado_uf = st_map[p_upper]
        else:
            partes_sem_estado.append(p)
    
    # Tenta identificar componentes do endereço
    rua = ''
    numero = ''
    bairro = ''
    cidade = ''
    complemento = ''
    referencia = ''
    
    for idx, p in enumerate(partes_sem_estado):
        p_clean = p.strip()
        p_upper = p_clean.upper()
        
        # Primeira parte geralmente é "RUA X, NUMERO" ou "MUNICIPIO DE X, NUMERO"
        if idx == 0:
            # Verifica se tem número embutido (ex: "RUA PEDRO IVO , 144" ou "MUNICIPIO DE X, 97")
            m_rua_num = re.match(r'^(.+?)\s*,\s*(\d+)\s*$', p_clean)
            if m_rua_num:
                rua = m_rua_num.group(1).strip()
                numero = m_rua_num.group(2).strip()
                # Se começa com MUNICIPIO DE, é indicador de zona rural
                if re.match(r'(?i)MUNICIPIO\s+DE\s+', rua):
                    # Extrai a cidade do "MUNICIPIO DE X"
                    m_mun = re.match(r'(?i)MUNICIPIO\s+DE\s+(.*)', rua)
                    if m_mun:
                        cidade = m_mun.group(1).strip()
                        rua = ''  # Será preenchido pela próxima parte
            else:
                rua = p_clean
        elif idx == 1:
            # Pode ser repetição da rua (com complemento), bairro, ou nome do local
            if not rua:
                # Se rua ficou vazio (era MUNICIPIO DE), esta é a rua real
                rua = p_clean
            elif p_upper.startswith('RUA ') or p_upper.startswith('AV ') or p_upper.startswith('AVENIDA '):
                # Repetição de rua com possível complemento - ignora se já temos
                pass
            else:
                # Provavelmente é o bairro ou complemento
                if not bairro:
                    bairro = p_clean
                else:
                    complemento = p_clean
        elif idx == 2:
            if not bairro:
                bairro = p_clean
            elif not cidade:
                cidade = p_clean
            else:
                referencia = p_clean
        elif idx == 3:
            if not cidade:
                cidade = p_clean
            else:
                referencia = p_clean
        else:
            # Referências extras
            if p_clean and p_upper not in ['NAO INFORMADO', 'NÃO INFORMADO']:
                referencia = p_clean
    
    # Monta o endereço formatado: Rua, Número, Bairro, Cidade-UF
    resultado = []
    if rua:
        # Limpa espaçamento extra do rua (ex: "RUA PEDRO IVO " -> "RUA PEDRO IVO")
        rua = re.sub(r'\s+', ' ', rua).strip()
        resultado.append(rua)
    if numero and numero != '0' and numero != '00':
        resultado.append(numero)
    if bairro:
        resultado.append(bairro)
    if cidade:
        if estado_uf:
            resultado.append(f"{cidade}-{estado_uf}")
        else:
            resultado.append(cidade)
    elif estado_uf:
        resultado.append(estado_uf)
    
    if not resultado:
        # Fallback: retorna original limpo
        return ', '.join(partes_sem_estado)
    
    return ', '.join(resultado)


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
    m_boe = re.search(r'BOLETIM DE OCORRÊNCIA Nº:\s*(\d{10,})\b', texto, flags=re.IGNORECASE)
    if not m_boe:
        m_boe = re.search(r'N[^\d]+(\d+[A-Z]\d+)', texto, flags=re.IGNORECASE)
    if not m_boe:
        # Tenta achar o padrao puro do Boletim de Pernambuco (ex: 26E0257000953)
        m_boe = re.search(r'\b(\d{2,}[A-Z]\d{5,})\b', texto, flags=re.IGNORECASE)
    if m_boe: dados["boe"] = m_boe.group(1).strip()
    
    # Natureza
    nats = re.findall(r'Natureza:(.*?)\nData:', texto, flags=re.DOTALL)
    if nats: dados["natureza"] = remover_acentos(' / '.join([n.strip().replace('\n', ' ') for n in nats]))
    
    # Data/Hora
    m_data = re.search(r'Data:\s*([\d/]+)\s*Hora:\s*([\d:]+)', texto)
    if m_data:
        dados["data_fato"] = m_data.group(1).strip()
        dados["hora_fato"] = m_data.group(2).strip()
        
    m_end = re.search(r'Endereço do fato:(.*?)\nLocal do fato:', texto, flags=re.DOTALL)
    if m_end: dados["end_fato"] = remover_acentos(m_end.group(1).strip().replace('\n', ' '))

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
        pessoas_matches = list(re.finditer(r'^([A-ZÀ-ÿ\s]+?)\s+\([^()]*?\)\s*Sexo:', txt_env, flags=re.MULTILINE))
        for i, match in enumerate(pessoas_matches):
            nome_raw = remover_acentos(match.group(1).strip().upper())
            start = match.end()
            end = pessoas_matches[i+1].start() if i+1 < len(pessoas_matches) else len(txt_env)
            ficha_raw = txt_env[start:end]
            
            # ✅ FIX: Unir linhas quebradas pelo PDF para evitar truncamento de campos
            # O PDF quebra linhas no meio de frases. Juntamos linhas que continuam
            # um campo (linha que NÃO começa com palavra-chave conhecida).
            ficha = re.sub(r'\n(?!Mãe:|Pai:|Nascimento:|Naturalidade:|Documentos:|Estado\s+Civil:|Escolaridade:|Profissão:|Telefones?|Endereço|Características|Idade\s+aparente|Pessoa\s+com|[A-ZÀ-ÿ]{3,}[A-ZÀ-ÿ\s]+\([^()]*\)\s*Sexo:)', ' ', ficha_raw)
            
            p = {
                "nome": nome_raw, "cpf": "", "rg": "", "nascimento": "", 
                "mae": "", "pai": "", "estado_civil": "", "naturalidade": "",
                "profissao": "", "telefone": "", "endereco": "", "escolaridade": "",
                "alcunha": ""
            }
            
            # Mãe: captura até o próximo campo (Pai:) ou fim de seção
            m_mae = re.search(r'Mãe:\s*(.+?)(?=;\s*Pai:|Pai:|$)', ficha, re.IGNORECASE)
            if m_mae: p['mae'] = re.sub(r'\s+', ' ', m_mae.group(1).strip().rstrip(';').strip())
            
            # Pai: captura até o próximo campo (Nascimento:) ou fim de seção
            m_pai = re.search(r'Pai:\s*(.+?)(?=;\s*Nascimento:|Nascimento:|$)', ficha, re.IGNORECASE)
            if m_pai: p['pai'] = re.sub(r'\s+', ' ', m_pai.group(1).strip().rstrip(';').strip())
            
            m_nasc = re.search(r'Nascimento:\s*([\d/]+)', ficha)
            if m_nasc: p['nascimento'] = m_nasc.group(1).strip()
            
            # CPF: Extrair e FORMATAR como XXX.XXX.XXX-XX
            m_cpf = re.search(r'(\d{11})\s*\(CPF\)', ficha)
            if m_cpf:
                cpf_raw = m_cpf.group(1).strip()
                p['cpf'] = f"{cpf_raw[:3]}.{cpf_raw[3:6]}.{cpf_raw[6:9]}-{cpf_raw[9:]}"
            
            m_rg = re.search(r'([\d/]+[A-Za-z/]+)\s*\(RG\)', ficha)
            if m_rg: p['rg'] = m_rg.group(1).strip()

            # Alcunha/Apelido
            m_alc = re.search(r'Apelido:\s*([^;\n]+)', ficha, re.IGNORECASE)
            if m_alc:
                alc = m_alc.group(1).strip()
                if alc.upper() not in ['NAO INFORMADO', 'NÃO INFORMADO', '']:
                    p['alcunha'] = alc

            # Naturalidade: captura multi-linha (após join) até ;
            m_natural = re.search(r'Naturalidade:\s*(.+?)(?=Documentos:|$)', ficha, re.IGNORECASE)
            if m_natural:
                nat_raw = m_natural.group(1).strip().rstrip(';').strip()
                nat_parts = [x.strip() for x in nat_raw.split('/') if x.strip() and x.strip() != 'BRASIL']
                if len(nat_parts) >= 2:
                    city = nat_parts[0]
                    state = nat_parts[1].upper()
                    st_map = {'ACRE':'AC','ALAGOAS':'AL','AMAPA':'AP','AMAPÁ':'AP','AMAZONAS':'AM','BAHIA':'BA','CEARA':'CE','CEARÁ':'CE','DISTRITO FEDERAL':'DF','ESPIRITO SANTO':'ES','ESPÍRITO SANTO':'ES','GOIAS':'GO','GOIÁS':'GO','MARANHAO':'MA','MARANHÃO':'MA','MATO GROSSO':'MT','MATO GROSSO DO SUL':'MS','MINAS GERAIS':'MG','PARA':'PA','PARÁ':'PA','PARAIBA':'PB','PARAÍBA':'PB','PARANA':'PR','PARANÁ':'PR','PERNAMBUCO':'PE','PIAUI':'PI','PIAUÍ':'PI','RIO DE JANEIRO':'RJ','RIO GRANDE DO NORTE':'RN','RIO GRANDE DO SUL':'RS','RONDONIA':'RO','RONDÔNIA':'RO','RORAIMA':'RR','SANTA CATARINA':'SC','SAO PAULO':'SP','SÃO PAULO':'SP','SERGIPE':'SE','TOCANTINS':'TO'}
                    if city in ['NÃO INFORMADO', 'NAO INFORMADO']:
                        p['naturalidade'] = 'NAO INFORMADO'
                    else:
                        uf = st_map.get(state, state)
                        p['naturalidade'] = f"{city}-{uf}"
                elif len(nat_parts) == 1:
                    val = nat_parts[0]
                    if val.upper() not in ['NAO', 'NÃO']:
                        p['naturalidade'] = val
            
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
            for m_end in re.finditer(r'Endere[çc]o\s+(?:Residencial|Comercial):\s*(.+?)(?=\nEndere[çc]o|\n[A-ZÀ-ÿ]{3,}[A-ZÀ-ÿ\s]+\([^()]*\)|Características|$)', ficha_raw, re.IGNORECASE | re.DOTALL):
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
    # Regex relaxada para capturar o bloco de objetos mesmo com variações de quebra de linha ou símbolos
    bloco_objetos = re.search(r'Objetos:?\s*[\r\n]+(.*?)(?=[\r\n]+(?:Complemento|Dados Complementares|Histórico|Narrativa|Condutor da ocorrência:|Dados da ocorrência:)|$)', texto, flags=re.DOTALL | re.IGNORECASE)
    if bloco_objetos:
        txt_obj = bloco_objetos.group(1)
        # Identifica cada item (VEICULO, CELULAR, etc)
        # O cabeçalho costuma ser algo como "NOME DO OBJETO (CATEGORIA)"
        # Melhoria: Split mais flexível que não exige quebra de linha após a categoria
        itens = re.split(r'[\r\n]+(?=[A-ZÀ-ÿ0-9/.\-\s]{3,} \([^()]+\))', "\n" + txt_obj)
        
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
                
                # Marca/Modelo (Suporta Categoria/Marca/Modelo ou apenas Marca/Modelo)
                m_v_mod = re.search(r'(?:Marca/Modelo|Modelo):\s*([^\n\r]+)', item, re.IGNORECASE)
                if m_v_mod: 
                    v['marca_modelo'] = m_v_mod.group(1).replace('NÃO INFORMADO', '').strip(' /').strip()
                else: 
                    # Tenta pegar do próprio nome do objeto se não achou label específica
                    v['marca_modelo'] = nome_obj
                
                # Cor
                m_v_cor = re.search(r'Cor(?:\s*Predominante)?:\s*([A-ZÀ-ÿ\s]+?)(?=[\r\n]|$)', item, re.IGNORECASE)
                if m_v_cor: v['cor'] = m_v_cor.group(1).strip()
                
                # Placa (suporta modelos novos Mercosul ou antigos)
                m_v_placa = re.search(r'Placa:\s*([A-Z0-9\-\s]{7,8})', item, re.IGNORECASE)
                if m_v_placa: 
                    v['placa'] = re.sub(r'[^A-Z0-9]', '', m_v_placa.group(1).upper()).strip()
                
                # Chassi
                m_v_chassi = re.search(r'Chassi:\s*([A-Z0-9]{17})', item, re.IGNORECASE)
                if m_v_chassi: v['chassi'] = m_v_chassi.group(1).strip()
                
                if v['placa'] or v['marca_modelo'] or v['chassi']:
                    if v['marca_modelo'] == 'NÃO INFORMADO': v['marca_modelo'] = nome_obj
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

    # --- FALLBACK: Se não detectou veículos na seção Objetos, busca no texto todo se a natureza sugerir ---
    natureza_full = (dados['natureza'] or '').upper()
    if not dados['veiculos'] and ('VEICULO' in natureza_full or 'VEÍCULO' in natureza_full or 'AUTO' in natureza_full):
        # Busca placa no formato Mercosul ou antigo em qualquer lugar
        placas_soltas = re.findall(r'Placa:\s*([A-Z]{3}[0-9][A-Z0-9][0-9]{2})', texto, re.I)
        for p in placas_soltas:
            p_upper = p.upper()
            if not any(v['placa'] == p_upper for v in dados['veiculos']):
                dados['veiculos'].append({"marca_modelo": "VEÍCULO DETECTADO (NATUREZA)", "placa": p_upper, "chassi": "", "cor": ""})
                if "VEÍCULO" not in (dados['objetos_apreendidos'] or ''):
                    dados['objetos_apreendidos'] = (dados['objetos_apreendidos'] or "") + (" / " if dados['objetos_apreendidos'] else "") + "VEÍCULO (DETECTADO)"

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

