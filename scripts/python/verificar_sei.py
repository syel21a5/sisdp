import argparse
import json
import os
import re
import sys
import time
from pathlib import Path
from urllib.parse import urlparse

from playwright.sync_api import sync_playwright


def send_msg(success, message, status="processing", data=None):
    print(
        json.dumps(
            {
                "success": success,
                "message": message,
                "status": status,
                "data": data,
            },
            ensure_ascii=False,
        ),
        flush=True,
    )


def load_credentials():
    if not sys.stdin.isatty():
        try:
            line = sys.stdin.readline()
            if line:
                line = line.lstrip("\ufeff").strip()
                data = json.loads(line)
                if data.get("usuario") and data.get("senha"):
                    return data
        except Exception:
            pass

    config_file = Path(__file__).parent / "sei_config.txt"
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


def extract_sei_root(base_url: str) -> str:
    """
    Extrai a URL raiz do servidor SEI a partir de qualquer URL colada.
    Exemplos:
      https://sei.pe.gov.br/sip/login.php?a=b  -> https://sei.pe.gov.br
      https://sei.pe.gov.br/sei/controlador.php -> https://sei.pe.gov.br
      https://sei.pe.gov.br                     -> https://sei.pe.gov.br
    """
    base_url = (base_url or "").strip().rstrip("/")
    try:
        parsed = urlparse(base_url)
        return f"{parsed.scheme}://{parsed.netloc}"
    except Exception:
        return base_url


def normalize_base_url(base_url: str) -> str:
    base_url = (base_url or "").strip()
    return base_url.rstrip("/")


def take_screenshot(page, output_dir: Path, prefix: str, sei: str | None = None):
    output_dir.mkdir(parents=True, exist_ok=True)
    filename = f"{prefix}_{int(time.time())}.png"
    path = output_dir / filename
    try:
        page.screenshot(path=str(path), full_page=True)
        payload = {"filename": filename}
        if sei:
            payload["sei"] = sei
        send_msg(True, "Screenshot capturado.", "screenshot", payload)
    except Exception as e:
        send_msg(False, f"Falha ao capturar screenshot: {e}", "error")


def is_login_page(page) -> bool:
    try:
        if page.locator("#txtUsuario, input[name='txtUsuario'], input[name='usuario']").first.is_visible():
            return True
    except Exception:
        pass
    try:
        text = page.inner_text("body")[:5000].lower()
        if "senha" in text and "usuário" in text and "sei" in text:
            return True
    except Exception:
        pass
    return False


def is_logged_in(page) -> bool:
    try:
        text = page.inner_text("body")[:8000].lower()
        if "iniciar processo" in text or "controle de processos" in text or "pesquisa no menu" in text:
            return True
    except Exception:
        pass
    return False


def close_popups(page):
    """
    Fecha popups/avisos que o SEI exibe após o login (ex: AVISO IMPORTANTE).
    Tenta várias abordagens: botão fechar, tecla Escape, clicar fora.
    """
    # Tenta fechar pelo botão X do popup do SEI (framework 'infra')
    close_selectors = [
        "a[onclick*='fechar']",
        "a[onclick*='Fechar']",
        ".infraBarraComandosSuperior a:last-child",
        "img[title='Fechar']",
        "img[alt='Fechar']",
        "a:has(img[src*='fechar'])",
        "a:has(img[src*='Fechar'])",
        ".botaoFechar",
    ]
    for sel in close_selectors:
        try:
            btn = page.locator(sel).first
            if btn.is_visible(timeout=1000):
                btn.click()
                send_msg(True, "Popup fechado.", "processing")
                page.wait_for_timeout(500)
                return True
        except Exception:
            pass

    # Tenta fechar com Escape
    try:
        page.keyboard.press("Escape")
        page.wait_for_timeout(500)
    except Exception:
        pass

    # Tenta clicar no fundo para fechar modais
    try:
        page.mouse.click(10, 10)
        page.wait_for_timeout(300)
    except Exception:
        pass

    return False


def goto_login(page, base_url: str):
    # Se a URL já for uma página de login direta, tenta ela primeiro e para
    if "login.php" in base_url or "controlador.php" in base_url:
        candidates = [base_url]
    else:
        candidates = [
            base_url,
            f"{base_url}/controlador.php?acao=usuario_logon",
            f"{base_url}/controlador.php?acao=login",
            f"{base_url}/sei/controlador.php?acao=usuario_logon",
            f"{base_url}/sei/controlador.php?acao=login",
        ]

    for url in candidates:
        try:
            send_msg(True, f"Abrindo login: {url}", "navigating")
            page.goto(url, timeout=30000, wait_until="domcontentloaded")
            # Se carregou algo que parece login ou já logado, sucesso
            if is_login_page(page) or is_logged_in(page):
                return True
        except Exception:
            continue
    return False


def do_login(page, base_url: str, usuario: str, senha: str, orgao: str, output_dir: Path):
    send_msg(True, "Acessando SEI para login...", "logging_in")
    if not goto_login(page, base_url):
        take_screenshot(page, output_dir, "login_nao_abriu")
        send_msg(False, "Não foi possível abrir a tela de login do SEI.", "error")
        return False

    if is_logged_in(page) and not is_login_page(page):
        send_msg(True, "Sessão já estava ativa no SEI.", "connected")
        return True

    send_msg(True, "Preenchendo credenciais...", "logging_in")

    user_locators = [
        "#txtUsuario",
        "input[name='txtUsuario']",
        "input[name='usuario']",
        "input[type='text']",
    ]
    pass_locators = [
        "#pwdSenha",
        "input[name='pwdSenha']",
        "input[name='senha']",
        "input[type='password']",
    ]
    btn_locators = [
        "#sbmAcessar",
        "#sbmLogin",
        "input[type='submit']",
        "button:has-text('Acessar')",
        "button:has-text('Entrar')",
    ]
    orgao_locators = [
        "#selOrgao",
        "select[name='selOrgao']",
        "select",
    ]

    user_filled = False
    for sel in user_locators:
        try:
            loc = page.locator(sel).first
            loc.wait_for(state="visible", timeout=15000)
            loc.fill(usuario)
            user_filled = True
            break
        except Exception:
            pass

    pass_filled = False
    for sel in pass_locators:
        try:
            loc = page.locator(sel).first
            loc.wait_for(state="visible", timeout=15000)
            loc.fill(senha)
            pass_filled = True
            break
        except Exception:
            pass

    if orgao:
        for sel in orgao_locators:
            try:
                loc = page.locator(sel).first
                loc.wait_for(state="visible", timeout=10000)
                # Tenta selecionar por label ou valor
                try:
                    loc.select_option(label=orgao)
                except Exception:
                    loc.select_option(value=orgao)
                break
            except Exception:
                pass

    if not user_filled or not pass_filled:
        take_screenshot(page, output_dir, "login_campos_nao_encontrados")
        send_msg(False, "Não foi possível localizar os campos de login do SEI.", "error")
        return False

    clicked = False
    for sel in btn_locators:
        try:
            btn = page.locator(sel).first
            btn.wait_for(state="visible", timeout=15000)
            with page.expect_navigation(wait_until="domcontentloaded", timeout=60000):
                btn.click()
            clicked = True
            break
        except Exception:
            pass

    if not clicked:
        take_screenshot(page, output_dir, "login_botao_nao_encontrado")
        send_msg(False, "Não foi possível acionar o login do SEI.", "error")
        return False

    if is_login_page(page):
        take_screenshot(page, output_dir, "login_falhou")
        send_msg(False, "Falha no login do SEI (verifique usuário/senha).", "error")
        return False

    # Fecha popups que aparecem após o login (ex: AVISO IMPORTANTE)
    send_msg(True, "Login OK. Fechando avisos...", "logging_in")
    page.wait_for_timeout(2000)
    close_popups(page)

    send_msg(True, "Conectado com sucesso!", "connected")
    return True


def go_to_search(page, base_url: str):
    """
    Tenta acessar a pesquisa. No SEI PE, tentamos primeiro garantir
    que estamos na tela principal onde a 'Pesquisa Rápida' (topo) está disponível.
    """
    root = extract_sei_root(base_url)
    
    # Verifica se já estamos em uma página que tem a busca rápida
    try:
        if page.locator("#txtPesquisaRapida").is_visible(timeout=2000):
            return True
    except:
        pass

    candidates = [
        f"{root}/sei/controlador.php?acao=procedimento_controlar",
        f"{root}/controlador.php?acao=procedimento_controlar",
        f"{root}/sei/",
        root
    ]
    for url in candidates:
        try:
            send_msg(True, f"Indo para tela inicial: {url}", "navigating")
            page.goto(url, timeout=30000, wait_until="domcontentloaded")
            page.wait_for_timeout(1000)
            close_popups(page)
            if is_logged_in(page):
                return True
        except Exception:
            continue
    return False


def execute_quick_search(page, sei: str):
    """
    Executa a pesquisa rápida no topo da página (conforme imagem do usuário).
    """
    try:
        search_input = page.locator("#txtPesquisaRapida")
        search_input.wait_for(state="visible", timeout=5000)
        search_input.fill(sei)
        send_msg(True, f"Pesquisando {sei} no topo...", "processing")
        page.keyboard.press("Enter")
        
        # Aguarda a árvore carregar ou lista de resultados
        page.wait_for_timeout(2000)
        close_popups(page)
        return True
    except Exception as e:
        send_msg(True, f"Erro na pesquisa rápida: {e}", "error")
        return False


def find_and_open_processo(page, sei: str):
    try:
        link = page.get_by_role("link", name=re.compile(re.escape(sei))).first
        link.wait_for(state="visible", timeout=8000)
        with page.expect_navigation(wait_until="domcontentloaded", timeout=60000):
            link.click()
        return True
    except Exception:
        pass

    try:
        link = page.get_by_text(re.compile(re.escape(sei))).first
        link.wait_for(state="visible", timeout=8000)
        with page.expect_navigation(wait_until="domcontentloaded", timeout=60000):
            link.click()
        return True
    except Exception:
        return False


def check_keywords_in_page(page, keywords):
    """
    Varre todos os frames da página procurando pelas palavras-chave.
    Especialmente útil para o SEI que usa frames para a árvore e conteúdo.
    """
    found_keywords = set()
    
    # Aguarda um pouco para os frames carregarem completamente
    page.wait_for_timeout(1000)
    
    for frame in page.frames:
        try:
            # Tenta pegar o texto visível do frame
            text = frame.inner_text("body").upper()
            for k in keywords:
                if k.upper() in text:
                    found_keywords.add(k)
        except Exception:
            continue

    return list(found_keywords)


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--action", choices=["login", "check"], required=True)
    parser.add_argument("--base_url", default="")
    parser.add_argument("--session_file", default="")
    parser.add_argument("--seis_file", default="")
    parser.add_argument("--output_dir", default="")
    parser.add_argument("--keywords", default="")
    parser.add_argument("--orgao", default="")
    parser.add_argument("--job_id", default="") # Aceita o job_id para evitar erro de argumentos
    args = parser.parse_args()

    base_url = normalize_base_url(args.base_url)
    if not base_url:
        send_msg(False, "Informe a URL do SEI.", "error")
        sys.exit(1)

    output_dir = Path(args.output_dir) if args.output_dir else (Path.home() / "Desktop" / "SEI_CHECK")
    session_file = args.session_file.strip() if args.session_file else ""

    keywords_raw = (args.keywords or "").strip()
    if keywords_raw:
        keywords = [k.strip() for k in keywords_raw.split(",") if k.strip()]
    else:
        keywords = ["LAUDO PERICIAL", "LAUDO", "PERÍCIA", "PERICIA"]

    credentials = load_credentials()
    if args.action == "login" and not credentials:
        send_msg(False, "Credenciais não informadas para login.", "error")
        sys.exit(1)

    with sync_playwright() as p:
        # Navegador em segundo plano (headless=True) agora que está tudo validado.
        browser = p.chromium.launch(headless=True, args=['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu', '--disable-dev-shm-usage'])

        context_args = {
            "viewport": {"width": 1366, "height": 768},
            "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
            "ignore_https_errors": True,
        }
        if session_file and os.path.exists(session_file) and args.action != "login":
            context_args["storage_state"] = session_file

        context = browser.new_context(**context_args)
        page = context.new_page()
        page.set_default_timeout(20000)
        page.set_default_navigation_timeout(70000)

        orgao = args.orgao or ""

        if args.action == "login":
            if credentials:
                orgao = orgao or credentials.get("orgao", "")
            ok = do_login(page, base_url, credentials["usuario"], credentials["senha"], orgao, output_dir)
            if ok and session_file:
                Path(session_file).parent.mkdir(parents=True, exist_ok=True)
                context.storage_state(path=session_file)
            browser.close()
            return

        try:
            # --- AÇÃO CHECK ---
            send_msg(True, "Preparando para verificar...", "processing")
            seis_file = args.seis_file.strip()
            if not seis_file or not os.path.exists(seis_file):
                send_msg(False, "Arquivo de SEIs não encontrado.", "error")
                browser.close()
                return

            try:
                with open(seis_file, "r", encoding="utf-8") as f:
                    seis_list = json.load(f)
            except Exception as e:
                send_msg(False, f"Falha ao ler lista de SEIs: {e}", "error")
                browser.close()
                return

            seis_list = [str(s).strip() for s in seis_list if str(s).strip()]
            if not seis_list:
                send_msg(False, "Lista de SEIs vazia.", "error")
                browser.close()
                return

            send_msg(True, f"Verificando {len(seis_list)} SEIs...", "checking", {"current": 0, "total": len(seis_list)})

            # Testa a sessão navegando direto para a pesquisa
            send_msg(True, "Testando sessão para iniciar...", "processing")
            session_test = go_to_search(page, base_url)
        except Exception as global_e:
            send_msg(False, f"Erro inesperado no setup: {global_e}", "error")
            page.wait_for_timeout(5000)
            browser.close()
            return

        try:
            # Se sessão expirada, tenta AUTO-RELOGIN
            if session_test is None or is_login_page(page):
                if credentials:
                    send_msg(True, "Sessão expirada. Reconectando automaticamente...", "logging_in")
                    orgao_cred = orgao or (credentials.get("orgao", "") if credentials else "")
                    ok = do_login(page, base_url, credentials["usuario"], credentials["senha"], orgao_cred, output_dir)
                    if ok and session_file:
                        Path(session_file).parent.mkdir(parents=True, exist_ok=True)
                        context.storage_state(path=session_file)
                    if not ok:
                        send_msg(False, "Falha ao reconectar ao SEI.", "expired")
                        page.wait_for_timeout(5000)
                        browser.close()
                        return
                    # Após relogin, vai para pesquisa
                    session_test = go_to_search(page, base_url)
                    if not session_test:
                        take_screenshot(page, output_dir, "pos_relogin_falha")
                        send_msg(False, "Não foi possível acessar a pesquisa após reconexão.", "expired")
                        page.wait_for_timeout(5000)
                        browser.close()
                        return
                else:
                    send_msg(False, "Sessão expirada e sem credenciais para reconexão.", "expired")
                    page.wait_for_timeout(5000)
                    browser.close()
                    return
            elif session_test is False:
                take_screenshot(page, output_dir, "pesquisa_nao_encontrada")
                send_msg(False, "Não foi possível acessar a página de pesquisa do SEI.", "error")
                page.wait_for_timeout(5000)
                browser.close()
                return

            counters = {
                "total": len(seis_list),
                "encontrados": 0,
                "nao_encontrados": 0,
                "com_laudo": 0,
                "sem_laudo": 0,
                "erros": 0,
            }

            for idx, sei in enumerate(seis_list, start=1):
                send_msg(True, f"Consultando {sei} ({idx}/{len(seis_list)})...", "progress", {"current": idx, "total": len(seis_list), "sei": sei})

                # Cria a linha na tabela imediatamente para o usuário ver que começou
                send_msg(True, "Iniciando busca...", "partial_result", {
                    "item": {"sei": sei, "encontrado": None, "pericia": None, "status": "buscando"}
                })

                if not go_to_search(page, base_url):
                    send_msg(True, "Erro ao acessar tela de busca.", "error")
                    continue

                if not execute_quick_search(page, sei):
                    send_msg(True, "Falha na busca rápida.", "partial_result", {
                        "item": {"sei": sei, "encontrado": False, "status": "erro_busca"}
                    })
                    counters["erros"] += 1
                    continue

                # Se não carregou direto a árvore, tenta clicar no link
                if "acao=procedimento_trabalhar" not in page.url:
                    opened = find_and_open_processo(page, sei)
                    if not opened:
                        send_msg(True, "Processo não encontrado.", "partial_result", {
                            "item": {"sei": sei, "encontrado": False, "status": "nao_encontrado"}
                        })
                        counters["nao_encontrados"] += 1
                        continue

                # --- ANÁLISE DA ÁRVORE (Sua Foto 2) ---
                send_msg(True, "Analisando documentos...", "progress", {"current": idx, "total": len(seis_list), "sei": sei})
                
                # Aguarda um pouco mais para garantir que a árvore carregou todos os nomes
                page.wait_for_timeout(2500)
                
                matched = check_keywords_in_page(page, keywords)
                
                counters["encontrados"] += 1
                status_final = "pericia_encontrada" if matched else "sem_pericia"
                if matched:
                    counters["com_laudo"] += 1
                else:
                    counters["sem_laudo"] += 1
                
                # Envia o resultado FINAL para a tabela
                send_msg(
                    True,
                    "Concluído",
                    "partial_result",
                    {
                        "item": {
                            "sei": sei,
                            "encontrado": True,
                            "pericia": True if matched else False,
                            "status": status_final,
                            "matched": matched,
                            "url": page.url,
                            "detalhes": "Laudo localizado na árvore" if matched else "Laudo não encontrado na árvore"
                        }
                    },
                )
                
                # Pequena pausa para o usuário ver no navegador antes de ir para o próximo ou fechar
                page.wait_for_timeout(1000)

            send_msg(True, "Todos os SEIs verificados.", "finished", counters)
            page.wait_for_timeout(2000)
            browser.close()

        except Exception as global_loop_e:
            send_msg(False, f"Erro inesperado no fluxo de pesquisa: {global_loop_e}", "error")
            page.wait_for_timeout(5000)
            browser.close()
            return


if __name__ == "__main__":
    main()
