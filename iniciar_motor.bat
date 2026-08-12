@echo off
title SisDP - Motor de Inteligência Artificial e Extração (Python)
echo ========================================================
echo  SISDP FASTAPI SERVER
echo  Ligando os motores... Por favor, não feche esta janela!
echo ========================================================
cd scripts\python
python -m uvicorn api_server:app --host 127.0.0.1 --port 8001
pause
