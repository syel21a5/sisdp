#!/bin/bash
echo "Preparando o Motor Nativo do SisDP..."

# Entra na pasta do Python
cd scripts/python

# Instala as bibliotecas necessarias
echo "Instalando bibliotecas do Python..."
pip3 install -r requirements.txt --break-system-packages 2>/dev/null || pip3 install -r requirements.txt

# Instala os navegadores do Playwright para os robos (SEI/Infopol)
echo "Instalando dependencias do navegador (Playwright)..."
playwright install chromium
playwright install-deps

# Mata qualquer processo antigo que possa estar travando a porta 8001
echo "Limpando processos antigos..."
fuser -k 8001/tcp 2>/dev/null

# Inicia o servidor FastAPI em segundo plano usando nohup
echo "Ligando o servidor na porta 8001..."
nohup python3 -m uvicorn api_server:app --host 127.0.0.1 --port 8001 > motor.log 2>&1 &

echo "=========================================================="
echo "Motor Nativo INICIADO COM SUCESSO em segundo plano!"
echo "Voce pode testar a extracao de BOE no sistema agora."
echo "Os logs do motor ficarao salvos em: scripts/python/motor.log"
echo "=========================================================="
