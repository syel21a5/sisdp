import os
import sys
import time
import re
import json
import argparse
import platform
from pathlib import Path
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeoutError


def unlock_memory_limits():
    """Remove os limites de memória virtual impostos pelo LiteSpeed no Linux."""
    if platform.system() == "Linux":
        try:
            import resource
            # Tenta elevar o limite de memória virtual (Address Space) para ilimitado
            resource.setrlimit(resource.RLIMIT_AS, (resource.RLIM_INFINITY, resource.RLIM_INFINITY))
        except Exception:
            # Se falhar (ex: hard limit baixo), tenta igualar soft ao hard
            try:
                soft, hard = resource.getrlimit(resource.RLIMIT_AS)
                resource.setrlimit(resource.RLIMIT_AS, (hard, hard))
            except:
                pass


def send_msg(success, message, status="processing", data=None):
    """Envia uma mensagem JSON para o PHP capturar via STDOUT."""
    print(json.dumps({
        "success": success,
        "message": message,
        "status": status,
        "data": data,
        "timestamp": time.strftime("%H:%M:%S")
    }, ensure_ascii=False), flush=True)


def load_config(config_path=None):
    """Lê credenciais de um arquivo JSON (preferencial) ou do STDIN."""
    # Prioridade 1: Arquivo enviado por argumento --config (mais seguro com sudo)
    if config_path and os.path.exists(config_path):
        try:
            with open(config_path, 'r', encoding='utf-8') as f:
                data = json.load(f)
                if data.get("usuario") and data.get("senha"):
                    return data
        except Exception:
            pass

    # Prioridade 2: STDIN (fallback)
    if not sys.stdin.isatty():
        try:
            line = sys.stdin.readline()
            if line:
                return json.loads(line)
        except Exception:
            pass
    
    return None


def main():
    unlock_memory_limits()
    # Log imediato para o Laravel saber que o script começou
    send_msg(True, "COMANDO RECEBIDO PELO PYTHON", "started")
    
    parser = argparse.ArgumentParser()
    parser.add_argument('--action', choices=['login', 'search', 'download'], default='download')
    parser.add_argument('--nome', default='')
    parser.add_argument('--inicio', default='')
    parser.add_argument('--fim', default='')
    parser.add_argument('--delegacia', default='')
    parser.add_argument('--output_dir', default='')
    parser.add_argument('--session_file', default='')
    parser.add_argument('--config', default='')
    parser.add_argument('--indices', default='')
    args = parser.parse_args()

    config = load_config(args.config)
    if not config and args.action == 'login':
        send_msg(False, "Credenciais não configuradas para login.", "error")
        return

    nome_pesquisa = args.nome or (config.get("nome") if config else "Desconhecido")
    
    if args.output_dir:
        pasta_destino = Path(args.output_dir)
    else:
        pasta_destino = Path.home() / "Desktop" / f"BOEs - {nome_pesquisa.upper()}"

    try:
        send_msg(True, "Iniciando Playwright...", "processing")
        with sync_playwright() as p:
            send_msg(True, "Lançando navegador Chrome...", "processing")
            
            # Caminho que confirmamos no probe.php
            chrome_path = "/home/www/.cache/ms-playwright/chromium_headless_shell-1208/chrome-headless-shell-linux64/chrome-headless-shell"
            
            launch_args = {
                "headless": True,
                "args": [
                    '--no-sandbox', 
                    '--disable-setuid-sandbox', 
                    '--disable-dev-shm-usage', 
                    '--disable-gpu',
                    '--no-zygote'
                ]
            }
            
            if os.path.exists(chrome_path):
                launch_args["executable_path"] = chrome_path
                send_msg(True, "Usando executável customizado do Chrome...", "processing")

            browser = p.chromium.launch(**launch_args)
            
            context_args = {}
            if args.session_file and os.path.exists(args.session_file):
                context_args["storage_state"] = args.session_file
            
            context = browser.new_context(**context_args)
            page = context.new_page()

            # --- LOGIN ---
            if args.action == 'login':
                try:
                    send_msg(True, "Navegando para o INFOPOL...", "processing")
                    page.goto("https://infopol.sds.pe.gov.br/", timeout=90000, wait_until="domcontentloaded")
                    
                    send_msg(True, "Aguardando campos de login...", "processing")
                    page.wait_for_selector('input[name="txtLogin"]', timeout=30000)
                    
                    send_msg(True, "Preenchendo credenciais...", "processing")
                    page.fill('input[name="txtLogin"]', config["usuario"])
                    page.fill('input[name="txtSenha"]', config["senha"])
                    
                    page.click('input[name="btnEntrar"]')
                    
                    send_msg(True, "Aguardando resposta do servidor SDS...", "processing")
                    page.wait_for_load_state('networkidle', timeout=30000)

                    if "j_security_check" in page.url or page.get_by_text(re.compile("usuário ou senha inválidos", re.IGNORECASE)).is_visible():
                        send_msg(False, "Falha no login: Usuário ou senha incorretos.", "error")
                        return

                    if args.session_file:
                        os.makedirs(os.path.dirname(args.session_file), exist_ok=True)
                        context.storage_state(path=args.session_file)
                        send_msg(True, "Conectado com sucesso!", "finished")
                    else:
                        send_msg(True, "Conectado com sucesso (sessão não salva)!", "finished")
                
                except Exception as e:
                    ts = int(time.time())
                    error_img = f"/tmp/erro_infopol_{ts}.png"
                    try:
                        page.screenshot(path=error_img)
                        send_msg(False, f"Erro no login: {str(e)}. Screenshot salva em {error_img}", "error")
                    except Exception as sce:
                        send_msg(False, f"Erro no login: {str(e)} (Falha screenshot: {str(sce)})", "error")
                    return

            # --- SEARCH ---
            elif args.action == 'search':
                send_msg(True, f"Buscando boletins para: {args.nome}", "searching")
                page.goto("https://infopol.sds.pe.gov.br/consultarBoletim.do?acao=prepararConsulta", wait_until="networkidle")
                
                if args.nome:
                    page.fill('input[name="txtNomeEnvolvido"]', args.nome)
                if args.inicio:
                    page.fill('input[name="txtDataInicio"]', args.inicio)
                if args.fim:
                    page.fill('input[name="txtDataFim"]', args.fim)
                if args.delegacia:
                    page.select_option('select[name="selUnidade"]', label=args.delegacia)
                
                page.click('input[name="btnConsultar"]')
                page.wait_for_load_state('networkidle')
                
                rows = page.query_selector_all('table.tabela_dados tr.linha_impar, table.tabela_dados tr.linha_par')
                resultados = []
                for i, row in enumerate(rows):
                    cols = row.query_selector_all('td')
                    if len(cols) >= 5:
                        resultados.append({
                            "id": i,
                            "numero": cols[1].inner_text().strip(),
                            "data": cols[2].inner_text().strip(),
                            "envolvido": cols[3].inner_text().strip(),
                            "unidade": cols[4].inner_text().strip()
                        })
                
                send_msg(True, f"Busca concluída: {len(resultados)} boletins encontrados.", "finished", data=resultados)

            # --- DOWNLOAD ---
            elif args.action == 'download':
                indices = [int(i) for i in args.indices.split(',')] if args.indices else []
                os.makedirs(pasta_destino, exist_ok=True)
                
                send_msg(True, f"Iniciando download de {len(indices)} boletins...", "downloading")
                
                rows = page.query_selector_all('table.tabela_dados tr.linha_impar, table.tabela_dados tr.linha_par')
                downloads_sucesso = 0
                
                for idx in indices:
                    if idx < len(rows):
                        try:
                            with page.expect_download() as download_info:
                                rows[idx].query_selector('a[title*="Imprimir"], a[title*="Visualizar"]').click()
                            
                            download = download_info.value
                            filepath = pasta_destino / f"BOE_{idx}_{int(time.time())}.pdf"
                            download.save_as(filepath)
                            downloads_sucesso += 1
                            send_msg(True, f"Baixado {downloads_sucesso}/{len(indices)}...", "processing")
                        except Exception as e:
                            send_msg(True, f"Falha ao baixar índice {idx}: {str(e)}", "processing")
                
                send_msg(True, f"Download finalizado: {downloads_sucesso} arquivos salvos.", "finished")

            browser.close()

    except Exception as e:
        send_msg(False, f"Erro crítico: {str(e)}", "error")


if __name__ == "__main__":
    main()
