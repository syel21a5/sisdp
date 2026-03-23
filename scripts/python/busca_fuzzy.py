import sys
import json
import os
import subprocess

try:
    import site
    user_site = site.getusersitepackages()
    if user_site and user_site not in sys.path:
        sys.path.append(user_site)
except:
    pass

def ensure_package(module_name: str, pip_name: str):
    try:
        __import__(module_name)
    except ImportError:
        try:
            print(json.dumps({"success": False, "status": f"Instalando {pip_name}..."}), file=sys.stderr)
            subprocess.check_call([sys.executable, "-m", "pip", "install", pip_name])
            __import__(module_name)
        except Exception as e:
            print(json.dumps({"success": False, "error": f"Erro instalando '{pip_name}': {str(e)}"}))
            sys.exit(1)

ensure_package('thefuzz', 'thefuzz')
ensure_package('mysql.connector', 'mysql-connector-python')

from thefuzz import process, fuzz
import mysql.connector

def read_env_config():
    env_path = os.path.join(os.path.dirname(__file__), '../../.env')
    config = {
        'host': '127.0.0.1',
        'port': 3306,
        'user': 'root',
        'password': '',
        'database': ''
    }
    try:
        if os.path.exists(env_path):
            with open(env_path, 'r', encoding='utf-8') as f:
                for line in f:
                    line = line.strip()
                    if line.startswith('DB_HOST='): config['host'] = line.split('=', 1)[1]
                    elif line.startswith('DB_PORT='): config['port'] = int(line.split('=', 1)[1])
                    elif line.startswith('DB_DATABASE='): config['database'] = line.split('=', 1)[1]
                    elif line.startswith('DB_USERNAME='): config['user'] = line.split('=', 1)[1]
                    elif line.startswith('DB_PASSWORD='): config['password'] = line.split('=', 1)[1]
    except Exception as e:
        pass
    return config

def search_similar_names(target_name, limit=5, threshold=85):
    try:
        db_config = read_env_config()
        
        connection = mysql.connector.connect(
            host=db_config['host'],
            port=db_config['port'],
            database=db_config['database'],
            user=db_config['user'],
            password=db_config['password']
        )
        
        if connection.is_connected():
            cursor = connection.cursor(dictionary=True)
            # Buscar todos os nomes únicos para comparar
            cursor.execute("SELECT DISTINCT Nome as nome FROM cadpessoa WHERE Nome IS NOT NULL AND Nome != ''")
            records = cursor.fetchall()
            
            names = list(set([r['nome'] for r in records if r['nome']]))
            
            # Executa a busca fuzzy
            results = process.extract(target_name, names, scorer=fuzz.token_sort_ratio, limit=limit)
            
            matches = []
            for name, score in results:
                if score >= threshold:
                    matches.append({
                        "nome": name,
                        "score": score
                    })
            
            cursor.close()
            connection.close()
            
            print(json.dumps({
                "success": True,
                "matches": matches
            }))
            
    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "Uso: python busca_fuzzy.py <nome_alvo>"}))
        sys.exit(1)

    target_name = sys.argv[1]
    search_similar_names(target_name)
