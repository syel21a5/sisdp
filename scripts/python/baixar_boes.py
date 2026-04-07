import os
import sys
import time
import re
import json
import argparse
from pathlib import Path
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeoutError


def send_msg(success, message, status="processing", data=None):
    """Envia progresso para o PHP via STDOUT (JSON por linha)."""
    print(json.dumps({
        "success": success,
        "message": message,
        "status": status,
        "data": data
    }, ensure_ascii=False), flush=True)


def load_config():
    """Lê credenciais do STDIN (enviadas pelo PHP) ou do arquivo de fallback."""
    # Prioridade 1: STDIN (enviado pelo InfopolController via $process->setInput)
    if not sys.stdin.isatty():
        try:
            line = sys.stdin.readline()
            if line:
                data = json.loads(line)
                if data.get("usuario") and data.get("senha"):
                    return data
        except Exception:
            pass

    # Prioridade 2: Arquivo de configuração local (fallback para testes manuais)
    config_file = Path(__file__).parent / "infopol_config.txt"
    if config_file.exists():
        config = {}
        with open(config_file, "r", encoding="utf-8") as f:
            for line in f:
                if "=" in line:
                    key, val = line.strip().split("=", 1)
                    config[key] = val
        if config.get("usuario") and config.get("senha"):
            return config

    return None


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--action', choices=['login', 'search', 'download'], default='download')
    parser.add_argument('--nome', default='')
    parser.add_argument('--inicio', default='')
    parser.add_argument('--fim', default='')
    parser.add_argument('--delegacia', default='')
    parser.add_argument('--output_dir', default='')
    parser.add_argument('--session_file', default='')
    parser.add_argument('--indices', default='') # Lista de índices separados por vírgula para download
    args = parser.parse_args()

    config = load_config()
    # No modo search/download, as credenciais podem vir da sessão salva
    if not config and args.action == 'login':
        send_msg(False, "Credenciais não configuradas para login.", "error")
        sys.exit(1)

    nome_pesquisa = args.nome.strip()
    data_inicio = args.inicio.strip()
    data_fim = args.fim.strip()
    delegacia_filtro = args.delegacia.strip()
    session_file = args.session_file if args.session_file else None
    indices_selecionados = [int(i) for i in args.indices.split(',')] if args.indices else []

    # Determina pasta de destino
    if args.output_dir:
        pasta_destino = Path(args.output_dir)
    else:
        pasta_destino = Path.home() / "Desktop" / f"BOEs - {nome_pesquisa.upper()}"

    try:
        with sync_playwright() as p:
            # Configuração do Navegador
            browser = p.chromium.launch(headless=True, slow_mo=50)
            
            # Tenta carregar sessão existente
            context_args = {
                "viewport": {'width': 1366, 'height': 768},
                "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36"
            }
            if session_file and os.path.exists(session_file) and args.action != 'login':
                context_args["storage_state"] = session_file
            
            context = browser.new_context(**context_args)
            page = context.new_page()

            # ============================================================
            # AÇÃO: LOGIN
            # ============================================================
            if args.action == 'login':
                send_msg(True, "Acessando portal para login...", "logging_in")
                page.goto("https://security.sds.pe.gov.br/pernambuco/", timeout=90000, wait_until="domcontentloaded")
                
                page.locator("input[type='text'], input[name='j_username'], input[name*='Login']").first.fill(config["usuario"])
                page.locator("input[type='password'], input[name='j_password']").first.fill(config["senha"])
                
                # Clica e aguarda a navegação ser triggada (domcontentloaded) em vez de esperar 30s por networkidle
                with page.expect_navigation(wait_until="domcontentloaded", timeout=60000):
                    page.locator("input[type='submit'], button:has-text('Entrar'), #btnEntrar").first.click()

                if "j_security_check" in page.url or page.get_by_text(re.compile("usuário ou senha inválidos", re.IGNORECASE)).is_visible():
                    send_msg(False, "Falha no login: Usuário ou senha incorretos.", "error")
                    browser.close()
                    return

                if session_file:
                    os.makedirs(os.path.dirname(session_file), exist_ok=True)
                    context.storage_state(path=session_file)
                    send_msg(True, "Conectado com sucesso!", "connected")
                else:
                    send_msg(True, "Login ok (sessão não salva).", "connected")
                
                browser.close()
                return

            # ============================================================
            # AÇÃO: SEARCH (LISTAR BOES)
            # ============================================================
            if args.action == 'search':
                send_msg(True, "Navegando para pesquisa...", "searching")
                try:
                    page.goto("https://security.sds.pe.gov.br/pernambuco/PesquisaBO.do", timeout=40000, wait_until="domcontentloaded")
                except Exception:
                    try:
                        menu_proc = page.get_by_text("PROCEDIMENTOS")
                        menu_proc.hover()
                        link_pesquisa = page.get_by_text("Pesquisa de BO")
                        with page.expect_navigation(wait_until="domcontentloaded", timeout=60000):
                            link_pesquisa.click()
                    except Exception:
                        pass
                
                # Se não carregou a pesquisa, talvez a sessão expirou
                if "Login.do" in page.url or page.get_by_text(re.compile("usuário ou senha", re.IGNORECASE)).is_visible():
                    send_msg(False, "Sessão expirada. Por favor, conecte-se novamente.", "expired")
                    browser.close()
                    return

                # Preencher Nome
                try:
                    label_nome = page.get_by_text("Nome do envolvido:", exact=False).first
                    label_nome.locator("xpath=following::input[1]").fill(nome_pesquisa)
                except Exception:
                    try:
                        page.locator("input[name='nomeEnvolvido']").fill(nome_pesquisa)
                    except Exception:
                        send_msg(False, "Não foi possível localizar o campo 'Nome do envolvido'.", "error")
                        browser.close()
                        return

                # Seleciona a Entidade (Policia Civil)
                try:
                    label_entidade = page.get_by_text("Entidade:")
                    select_entidade = label_entidade.locator("xpath=following::select[1]")
                    current_entidade = select_entidade.evaluate("el => el.options[el.selectedIndex].text")
                    if "POLICIA CIVIL DE PERNAMBUCO" not in current_entidade.upper():
                        select_entidade.select_option(label=re.compile("POLICIA CIVIL DE PERNAMBUCO", re.IGNORECASE))
                        page.wait_for_timeout(1000)
                except Exception:
                    pass

                # Preencher Unidade
                if delegacia_filtro:
                    try:
                        label_unidade = page.get_by_text("Unidade Operacional de Registro:")
                        select_unidade = label_unidade.locator("xpath=following::select[1]")
                        options = select_unidade.evaluate("el => Array.from(el.options).map(o => o.text)")
                        target_option = None
                        for opt in options:
                            if delegacia_filtro in opt:
                                target_option = opt
                                break
                        if target_option:
                            select_unidade.select_option(label=target_option)
                        else:
                            select_unidade.select_option(label=re.compile(delegacia_filtro, re.IGNORECASE))
                    except Exception:
                        pass

                # Preencher Datas
                if data_inicio:
                    try:
                        label_data = page.get_by_text("Data de Registro:", exact=False).first
                        label_data.locator("xpath=following::input[1]").fill(data_inicio)
                    except:
                        try: page.locator("#dtRegistroDe").fill(data_inicio)
                        except: pass
                if data_fim:
                    try:
                        label_data = page.get_by_text("Data de Registro:", exact=False).first
                        label_data.locator("xpath=following::input[2]").fill(data_fim)
                    except:
                        try: page.locator("#dtRegistroAte").fill(data_fim)
                        except: pass

                # Clicar Pesquisar
                try:
                    button_pesquisar = page.get_by_role("button", name="Pesquisar")
                    button_pesquisar.click()
                except Exception:
                    page.locator("input.defaultButton[value='Pesquisar']").click()
                
                try:
                    page.wait_for_function(
                        "() => document.body.innerText.includes('TOTAL DE BOLETINS') || " +
                        "document.body.innerText.includes('Não existem registros') || " +
                        "document.body.innerText.includes('Não existem registros para os filtros') || " +
                        "document.body.innerText.includes('Erro de sistema') || " +
                        "document.body.innerText.includes('0 registro')",
                        timeout=90000
                    )
                except Exception:
                    pass

                content = page.content()
                if "TOTAL DE BOLETINS" not in content and "Completo" not in content:
                    send_msg(False, "Nenhum resultado encontrado.", "no_results")
                    browser.close()
                    return

                # Scrapar a tabela de resultados - Nova estratégia baseada em links 'Completo'
                results = []
                # Precisamos encontrar as linhas de tabela onde exite o link "Completo" ou "Visualização"
                
                links = page.get_by_text("Completo", exact=True).all()
                total_links = len(links)
                
                if total_links > 0:
                    send_msg(True, f"Processando {total_links} boletins...", "search_progress", {"current": 0, "total": total_links})

                for i, link in enumerate(links):
                    try:
                        # Extrai a linha (tr) parent
                        row = link.locator("xpath=ancestor::tr[1]")
                        cells = row.locator("td").all()
                        if len(cells) >= 6:
                            bo_num = cells[0].inner_text().strip()
                            if "TOTAL" not in bo_num.upper():
                                item = {
                                    "index": i,
                                    "numero": bo_num
                                }
                                results.append(item)
                                # Emite para o frontend colocar na interface instantaneamente
                                send_msg(True, f"Carregando {i+1}/{total_links}...", "partial_result", {"item": item, "current": i+1, "total": total_links})
                    except Exception: pass
                
                if len(results) == 0:
                     send_msg(False, "Nenhum resultado encontrado.", "no_results")
                     browser.close()
                     return

                send_msg(True, f"Concluído! {len(results)} boletins localizados.", "search_finished", {})
                browser.close()
                return

            # ============================================================
            # AÇÃO: DOWNLOAD (BAIXAR SELECIONADOS)
            # ============================================================
            if args.action == 'download':
                # Re-executa a pesquisa para chegar na lista (necessário pois o INFOPOL é stateful)
                page.goto("https://security.sds.pe.gov.br/pernambuco/PesquisaBO.do", timeout=40000, wait_until="domcontentloaded")
                
                # Preencher e pesquisar novamente (resumido)
                label_nome = page.get_by_text("Nome do envolvido:", exact=False).first
                label_nome.locator("xpath=following::input[1]").fill(nome_pesquisa)
                if data_inicio: page.locator("#dtRegistroDe").first.fill(data_inicio)
                if data_fim: page.locator("#dtRegistroAte").first.fill(data_fim)
                page.get_by_role("button", name="Pesquisar").click()
                page.wait_for_selector("text=TOTAL DE BOLETINS")

                links = page.get_by_text("Completo", exact=True).all()
                
                pasta_destino.mkdir(parents=True, exist_ok=True)
                downloaded_count = 0

                for idx in indices_selecionados:
                    if idx >= len(links): continue
                    
                    send_msg(True, f"Baixando item {idx+1}...", "downloading", {"current": downloaded_count + 1, "total": len(indices_selecionados)})
                    link = links[idx]
                    
                    # Tenta capturar o número do BO diretamente da tabela
                    numero_bo = ""
                    try:
                        row = link.locator("xpath=ancestor::tr[1]")
                        cells = row.locator("td").all()
                        if len(cells) >= 6:
                            numero_bo = cells[0].inner_text().strip()
                    except Exception: pass

                    with context.expect_page() as popup_info:
                        link.click()
                    
                    popup = popup_info.value
                    try:
                        popup.wait_for_load_state("load", timeout=30000)
                        popup.wait_for_timeout(500) # Pequena margem de segurança visual para gerar PDF

                        # Fallback agressivo por Regex se não achar pela Tabela (Formato: numérico seguido de letras e números)
                        if not numero_bo:
                            bo_match = re.search(r'\b\d+[A-Za-z]\d+\b', popup.content())
                            numero_bo = bo_match.group(0).upper() if bo_match else f"Item_Desconhecido_{idx+1}"

                        nome_pdf = f"BOE - {numero_bo}.pdf"
                        
                        popup.pdf(path=str(pasta_destino / nome_pdf), format="A4", print_background=True)
                        downloaded_count += 1
                        popup.close()
                    except Exception as e:
                        send_msg(True, f"Erro ao baixar item {idx+1}: {str(e)}", "warning")
                        if not popup.is_closed(): popup.close()

                send_msg(True, "Downloads concluídos!", "finished", {"total": downloaded_count})
                browser.close()
                return

    except Exception as e:
        send_msg(False, f"Erro crítico: {str(e)}", "error")
        if 'browser' in locals(): browser.close()


if __name__ == "__main__":
    main()
