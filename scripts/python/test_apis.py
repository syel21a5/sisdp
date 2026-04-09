import sys
import os
import time
import json
import urllib.request
import urllib.error
import socket

# Força IPv4 para não demorar
old_getaddrinfo = socket.getaddrinfo
def new_getaddrinfo(*args, **kwargs):
    responses = old_getaddrinfo(*args, **kwargs)
    return [res for res in responses if res[0] == socket.AF_INET]
socket.getaddrinfo = new_getaddrinfo

# Carrega as chaves do .env
config = {'gemini_keys': [], 'groq_keys': [], 'deepseek_key': ''}
env_path = os.path.join(os.path.dirname(__file__), '../../.env')
if os.path.exists(env_path):
    with open(env_path, 'r', encoding='utf-8') as f:
        for line in f:
            if '=' in line:
                k, v = line.strip().split('=', 1)
                if k == 'GEMINI_API_KEYS': config['gemini_keys'] = [x.strip() for x in v.split(',')]
                if k == 'GROQ_API_KEYS': config['groq_keys'] = [x.strip() for x in v.split(',')]
                if k == 'DEEPSEEK_API_KEY': config['deepseek_key'] = v

def testar_api(nome, url, headers, data):
    print(f"\n--- Testando {nome} ---")
    start = time.time()
    req = urllib.request.Request(url, data=data, headers=headers)
    try:
        with urllib.request.urlopen(req, timeout=120) as response:
            res = response.read().decode('utf-8')
            elapsed = time.time() - start
            print(f"[SUCESSO] Tempo: {elapsed:.2f}s")
            print(f"Resposta curta: {res[:100]}...")
    except urllib.error.HTTPError as e:
        elapsed = time.time() - start
        print(f"[ERRO HTTP {e.code}] Tempo: {elapsed:.2f}s - Motivo: {e.reason}")
        print(f"Corpo do erro: {e.read().decode('utf-8')}")
    except Exception as e:
        elapsed = time.time() - start
        print(f"[ERRO GERAL] Tempo: {elapsed:.2f}s - {str(e)}")

texto_teste = "Isto é um teste de latência de IA."
prompt_data_bobo = json.dumps({"contents": [{"parts": [{"text": texto_teste}]}]}).encode('utf-8')
prompt_data_openai = json.dumps({
    "model": "llama3-70b-8192", "messages": [{"role": "user", "content": texto_teste}]
}).encode('utf-8')

for idx, key in enumerate(config['gemini_keys']):
    testar_api(f"Gemini (Chave {idx+1})", 
               f"https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={key}",
               {'Content-Type': 'application/json'}, prompt_data_bobo)

for idx, key in enumerate(config['groq_keys']):
    testar_api(f"Groq (Chave {idx+1})", "https://api.groq.com/openai/v1/chat/completions",
               {'Content-Type': 'application/json', 'Authorization': f'Bearer {key}'}, prompt_data_openai)

if config['deepseek_key']:
    data_ds = json.dumps({"model": "deepseek-chat", "messages": [{"role": "user", "content": texto_teste}]}).encode('utf-8')
    testar_api("DeepSeek (Backup)", "https://api.deepseek.com/chat/completions",
               {'Content-Type': 'application/json', 'Authorization': f'Bearer {config["deepseek_key"]}'}, data_ds)
