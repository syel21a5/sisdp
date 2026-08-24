"""
proxy_util.py — Seleção inteligente de proxy para os robôs (INFOPOL e SEI).

Estratégia (Opção A):
  1. Se o túnel reverso do PC do Van estiver aberto (porta 1080 escutando
     na VPS, apontando para o IP residencial dele), USAR esse túnel:
       socks5h://127.0.0.1:1080
     Motivo: sai do IP residencial real (Brasil/PE) -> governo aceita.
  2. Caso contrário, usa o proxy do .env (Data Impulse) como fallback.

Uso:
    from proxy_util import select_proxy
    proxy_settings = select_proxy()
    browser = p.chromium.launch(headless=True, proxy=proxy_settings)

    # proxy_settings retorna None se nenhum proxy estiver disponível.
"""

import os
import socket
from pathlib import Path


TUNNEL_HOST = "127.0.0.1"
TUNNEL_PORT = 1080
TUNNEL_TIMEOUT = 0.5  # segundos — teste rápido


def _tunnel_is_open(host: str = TUNNEL_HOST, port: int = TUNNEL_PORT,
                    timeout: float = TUNNEL_TIMEOUT) -> bool:
    """Verifica se a porta do túnel reverso está escutando na VPS."""
    try:
        with socket.create_connection((host, port), timeout=timeout):
            return True
    except OSError:
        return False


def _read_env_proxy() -> dict:
    """Lê PROXY_SERVER/USERNAME/PASSWORD do .env do projeto."""
    env_path = Path(__file__).resolve().parent.parent.parent / ".env"
    proxy_server = os.environ.get("PROXY_SERVER")
    proxy_username = os.environ.get("PROXY_USERNAME")
    proxy_password = os.environ.get("PROXY_PASSWORD")

    if not proxy_server and env_path.exists():
        try:
            with open(env_path, "r", encoding="utf-8") as f:
                for line in f:
                    line = line.strip()
                    if "=" in line and not line.startswith("#"):
                        k, v = line.split("=", 1)
                        if k == "PROXY_SERVER":
                            proxy_server = v
                        elif k == "PROXY_USERNAME":
                            proxy_username = v
                        elif k == "PROXY_PASSWORD":
                            proxy_password = v
        except OSError:
            pass

    return {"server": proxy_server, "username": proxy_username, "password": proxy_password}


def select_proxy(prefer_tunnel: bool = True) -> dict:
    """Retorna o dict 'proxy' para o Playwright (ou None se sem proxy).

    prefer_tunnel=True  -> prioriza o túnel reverso do PC (127.0.0.1:1080).
    """
    # 1) Túnel do PC (melhor opção: IP residencial brasileiro)
    if prefer_tunnel and _tunnel_is_open():
        proxy_settings = {"server": f"socks5h://{TUNNEL_HOST}:{TUNNEL_PORT}"}
        return proxy_settings

    # 2) Fallback: proxy do .env (Data Impulse)
    env = _read_env_proxy()
    if env.get("server"):
        proxy_settings = {"server": env["server"]}
        if env.get("username") and env.get("password"):
            proxy_settings["username"] = env["username"]
            proxy_settings["password"] = env["password"]
        return proxy_settings

    return None
