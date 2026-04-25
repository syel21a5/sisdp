from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
import sys
import os
import json

# Adiciona o diretório atual ao sys.path para importar os módulos locais
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

import boe_extractor

app = FastAPI(title="SisDP Microservice", description="Serviço rápido para IA e Robôs do SisDP", version="1.0.0")

class ExtractRequest(BaseModel):
    file_path: str
    type: str = 'apfd'

@app.get("/")
def read_root():
    return {"status": "ok", "message": "SisDP FastAPI Server Running"}

@app.post("/quick-extract")
def quick_extract(request: ExtractRequest):
    if not os.path.exists(request.file_path):
        return {"boe": None}
    
    import re
    ext = os.path.splitext(request.file_path)[1].lower()
    t = ""
    if ext == '.txt':
        with open(request.file_path, 'r', encoding='utf-8') as f:
            t = f.read()
    elif ext == '.pdf':
        import fitz
        try:
            doc = fitz.open(request.file_path)
            t = doc[0].get_text()
            doc.close()
        except:
            pass
            
    m = re.search(r"N[^\d]+(\d+[A-Z]\d+)", t, re.I)
    m = m if m else re.search(r"\b(\d{2,}[A-Z]\d{5,})\b", t, re.I)
    m = m if m else re.search(r"BOLETIM DE OCORR[ÊE]NCIA N[ºO]?:\s*(\d{10,})\b", t, re.I)
    
    return {"boe": m.group(1) if m else None}

@app.post("/extract-boe")
def extract_boe(request: ExtractRequest):
    if not os.path.exists(request.file_path):
        raise HTTPException(status_code=404, detail="Arquivo não encontrado")
    
    # Executar a mesma lógica que o CLI faria, mas retornando um dicionário python
    # Ler o texto do arquivo (o mesmo que boe_extractor faz)
    texto_raw_original = boe_extractor.ler_arquivo(request.file_path, clean_mode=False)
    
    if texto_raw_original.startswith("ERRO_DEBUG:"):
        raise HTTPException(status_code=500, detail=texto_raw_original)
    if not texto_raw_original.strip():
        raise HTTPException(status_code=400, detail="Arquivo vazio ou PDF sem texto selecionável.")

    # Carregar configs de IA do .env se existir
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

    # Tenta extração regex primeiro
    success_py, py_data = boe_extractor.parse_boe_python(texto_raw_original)
    
    use_ai = os.environ.get('BOE_USE_AI', '').lower() == 'true'
    if use_ai and not deepseek_key and not gemini_key:
        use_ai = False

    if success_py:
        py_data['texto_raw'] = texto_raw_original
        return {"success": True, "dados": py_data}
    elif use_ai:
        texto_limpo_ia = boe_extractor.clean_boe_raw_text(texto_raw_original)
        resultado = boe_extractor.process_with_deepseek(texto_limpo_ia, config)
        if "success" in resultado and not resultado["success"]:
            return {"success": True, "dados": py_data, "obs": "IA falhou, dados parciais do Python"}
        else:
            return {"success": True, "dados": resultado}
    else:
        py_data['texto_raw'] = texto_raw_original
        return {"success": True, "dados": py_data, "obs": "Extração parcial via Python (IA desativada)"}

class GeneratePdfRequest(BaseModel):
    input_html_path: str
    output_pdf_path: str

@app.post("/generate-pdf")
def api_generate_pdf(request: GeneratePdfRequest):
    import gerar_pdf
    if not os.path.exists(request.input_html_path):
        raise HTTPException(status_code=404, detail="Arquivo HTML não encontrado")
    
    result = gerar_pdf.generate_pdf(request.input_html_path, request.output_pdf_path)
    if not result.get("success"):
        raise HTTPException(status_code=500, detail=result.get("error", "Erro desconhecido na geração do PDF"))
    return result

from fastapi.responses import StreamingResponse
from typing import Optional, List
import asyncio

class SeiRobotRequest(BaseModel):
    action: str
    base_url: str
    session_file: Optional[str] = None
    seis_file: Optional[str] = None
    output_dir: Optional[str] = None
    keywords: Optional[str] = None
    orgao: Optional[str] = None
    job_id: Optional[str] = None
    usuario: Optional[str] = None
    senha: Optional[str] = None

@app.post("/sei-robot")
async def sei_robot(request: SeiRobotRequest):
    async def run_and_stream():
        script_path = os.path.join(os.path.dirname(__file__), "verificar_sei.py")
        args = [sys.executable, script_path, "--action", request.action, "--base_url", request.base_url]
        
        if request.session_file: args.extend(["--session_file", request.session_file])
        if request.seis_file: args.extend(["--seis_file", request.seis_file])
        if request.output_dir: args.extend(["--output_dir", request.output_dir])
        if request.keywords: args.extend(["--keywords", request.keywords])
        if request.orgao: args.extend(["--orgao", request.orgao])
        if request.job_id: args.extend(["--job_id", request.job_id])

        process = await asyncio.create_subprocess_exec(
            *args,
            stdin=asyncio.subprocess.PIPE,
            stdout=asyncio.subprocess.PIPE,
            stderr=asyncio.subprocess.PIPE
        )
        
        if request.usuario and request.senha:
            creds = json.dumps({"usuario": request.usuario, "senha": request.senha, "orgao": request.orgao or ""})
            process.stdin.write((creds + "\n").encode())
            await process.stdin.drain()
        process.stdin.close()

        while True:
            line = await process.stdout.readline()
            if not line:
                break
            yield line

        await process.wait()

    return StreamingResponse(run_and_stream(), media_type="text/event-stream")

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("api_server:app", host="127.0.0.1", port=8001, reload=True)
