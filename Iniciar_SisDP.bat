@echo off
color 0B
echo ===================================================
echo           INICIANDO O SISTEMA SISDP
echo ===================================================
echo.
echo Iniciando o servidor do Site (PHP)...
start "Servidor PHP SisDP" cmd /k "php artisan serve"
echo.
echo Iniciando o servidor de Inteligencia Artificial (Python)...
start "Servidor IA SisDP" cmd /k "python scripts\python\api_server.py"
echo.
echo ===================================================
echo TUDO PRONTO! O SisDP esta rodando.
echo Voce ja pode acessar: http://127.0.0.1:8000
echo.
echo (Deixe as duas janelas pretas que abriram abertas)
echo ===================================================
pause
