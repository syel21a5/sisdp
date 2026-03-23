import sys
import json
import os
import time
import subprocess

# Forçar inclusão das pastas de site-packages do usuário (Windows)
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

def ensure_package(module_name: str, pip_name: str):
    try:
        __import__(module_name)
    except ImportError:
        try:
            print(json.dumps({"success": False, "status": "Instalando dependencias via pip, aguarde...", "module": pip_name}), file=sys.stderr)
            subprocess.check_call([sys.executable, "-m", "pip", "install", pip_name])
            __import__(module_name)
        except Exception as e:
            print(json.dumps({"success": False, "error": f"A biblioteca '{pip_name}' não instalou: {str(e)}"}))
            sys.exit(1)

ensure_package('playwright', 'playwright')

from playwright.sync_api import sync_playwright

def generate_pdf(input_html_path, output_pdf_path):
    # Garante que o Chromium da Playwright está instalado
    try:
        with sync_playwright() as p:
            browser = p.chromium.launch()
            browser.close()
    except Exception as e:
        if 'Executable doesn\'t exist at' in str(e) or 'playwright install' in str(e):
            print(json.dumps({"success": False, "status": "Instalando navegador Chromium interno, aguarde... isso só acontece na primeira vez."}), file=sys.stderr)
            subprocess.check_call([sys.executable, "-m", "playwright", "install", "chromium"])
        else:
            print(json.dumps({"success": False, "error": f"Erro inicializando chromium: {str(e)}"}))
            sys.exit(1)

    try:
        with sync_playwright() as p:
            browser = p.chromium.launch(headless=True)
            page = browser.new_page()
            
            # Carrega o HTML do arquivo usando o protocolo file://
            uri = f"file:///{os.path.abspath(input_html_path).replace(chr(92), '/')}"
            page.goto(uri, wait_until='networkidle')
            
            # --- INJECT JS TO RESTRUCTURE DOM FOR CHROME NATIVE REPEATING HEADERS ---
            page.evaluate("""() => {
                const header = document.querySelector('.header');
                const footer = document.querySelector('.footer');
                const content = document.querySelector('.content');

                if (header && footer && content) {

                    // 1. NEUTRALIZE OLD POSITIONING ON HEADER - force white bg to prevent black boxes
                    header.style.cssText = 'position:relative !important; top:auto !important; left:auto !important; right:auto !important; bottom:auto !important; width:100% !important; background:white !important; margin:0 !important; padding:0 !important; border:none !important; height:auto !important;';
                    header.querySelectorAll('img').forEach(function(img) {
                        img.style.marginTop = '0px';
                        img.style.setProperty('margin-top', '0px', 'important');
                    });

                    // 2. NEUTRALIZE FOOTER - keep it fixed at the bottom of every page
                    footer.style.cssText = 'position:fixed !important; bottom:0 !important; left:0 !important; right:0 !important; background:white !important; margin:0 !important; padding-top:5px !important; border-top:1px solid #ccc; text-align:center; font-size:10pt; z-index:9999;';

                    // 3. BUILD A TABLE WITH THEAD (repeating header) + TBODY (content) + TFOOT (spacer)
                    var table = document.createElement('table');
                    table.style.cssText = 'width:100%; border-collapse:collapse; border:none; border-spacing:0; background:white;';

                    var thead = document.createElement('thead');
                    thead.style.cssText = 'display:table-header-group; background:white;';
                    var trHead = document.createElement('tr');
                    trHead.style.cssText = 'background:white;';
                    var tdHead = document.createElement('td');
                    tdHead.style.cssText = 'padding:0; border:none; background:white; vertical-align:top;';
                    tdHead.appendChild(header);
                    trHead.appendChild(tdHead);
                    thead.appendChild(trHead);

                    var tbody = document.createElement('tbody');
                    var trBody = document.createElement('tr');
                    trBody.style.cssText = 'background:white;';
                    var tdBody = document.createElement('td');
                    tdBody.style.cssText = 'padding:0; border:none; background:white; vertical-align:top;';
                    tdBody.appendChild(content);
                    trBody.appendChild(tdBody);
                    tbody.appendChild(trBody);

                    // TFOOT is just a spacer so the fixed footer doesn't overlap last line of text
                    var tfoot = document.createElement('tfoot');
                    tfoot.style.cssText = 'display:table-footer-group; background:white;';
                    var trFoot = document.createElement('tr');
                    trFoot.style.cssText = 'background:white;';
                    var tdFoot = document.createElement('td');
                    tdFoot.style.cssText = 'padding:0; border:none; background:white;';
                    var spacer = document.createElement('div');
                    spacer.style.cssText = 'height:55px; background:white;';
                    tdFoot.appendChild(spacer);
                    trFoot.appendChild(tdFoot);
                    tfoot.appendChild(trFoot);

                    table.appendChild(thead);
                    table.appendChild(tbody);
                    table.appendChild(tfoot);

                    // 4. REBUILD BODY
                    document.body.innerHTML = '';
                    document.body.style.cssText = 'margin:0; padding:0; background:white;';
                    document.body.appendChild(table);
                    document.body.appendChild(footer); // fixed footer appended outside table

                    // 5. OVERRIDE @page MARGINS to avoid conflicts with old CSS
                    var styles = document.querySelectorAll('style');
                    for (var i = 0; i < styles.length; i++) {
                        styles[i].innerHTML = styles[i].innerHTML.replace(/@page\s*\{[^}]*\}/gi, '');
                    }
                    var newStyle = document.createElement('style');
                    newStyle.innerHTML = '@page { margin: 25px 30px 40px 30px !important; } * { -webkit-print-color-adjust: exact !important; }';
                    document.head.appendChild(newStyle);
                }
            }""")
            # ------------------------------------------------------------------------
            
            page.pdf(
                path=output_pdf_path,
                format="A4",
                print_background=True,
                prefer_css_page_size=True
            )
            browser.close()
            
        print(json.dumps({
            "success": True, 
            "path": os.path.abspath(output_pdf_path)
        }))
    except Exception as e:
        print(json.dumps({
            "success": False,
            "error": f"Erro ao gerar PDF: {str(e)}"
        }))
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"success": False, "error": "Uso: python gerar_pdf.py <caminho_entrada_html> <caminho_saida_pdf>"}))
        sys.exit(1)

    input_html = sys.argv[1]
    output_pdf = sys.argv[2]
    
    if not os.path.exists(input_html):
        print(json.dumps({"success": False, "error": f"Arquivo HTML nao encontrado: {input_html}"}))
        sys.exit(1)
        
    generate_pdf(input_html, output_pdf)
