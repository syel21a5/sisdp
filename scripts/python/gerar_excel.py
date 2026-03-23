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

ensure_package('pandas', 'pandas')
ensure_package('openpyxl', 'openpyxl')
ensure_package('mysql.connector', 'mysql-connector-python')

import pandas as pd
import mysql.connector
from mysql.connector import Error

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

def generate_excel(input_json_path, output_excel_path):
    try:
        with open(input_json_path, 'r', encoding='utf-8') as f:
            req_data = json.load(f)
            
        sql = req_data.get('sql')
        bindings = req_data.get('bindings', [])
        
        # O Laravel usa ? como placeholder, que não é 100% nativo do mysql-connector em format() mas funciona no execute
        # mysql-connector string interpolation usa %s
        sql = sql.replace('?', '%s')
        
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
            cursor.execute(sql, tuple(bindings))
            records = cursor.fetchall()
            
            df = pd.DataFrame(records)
            
            # Se a dataframe estiver vazia
            if df.empty:
                df = pd.DataFrame(["Nenhum registro encontrado."])
                
            # Formatação opcional de datas ou campos
            for col in df.columns:
                if df[col].dtype == 'datetime64[ns]' or 'data' in str(col).lower():
                    try:
                        df[col] = pd.to_datetime(df[col]).dt.strftime('%d/%m/%Y %H:%M:%S')
                    except:
                        pass
                if 'data_cadastro' in str(col).lower():
                    try:
                        df[col] = pd.to_datetime(df[col]).dt.strftime('%d/%m/%Y')
                    except:
                        pass
                        
            # Salvar como Excel Excel usando openpyxl
            with pd.ExcelWriter(output_excel_path, engine='openpyxl') as writer:
                df.to_excel(writer, index=False, sheet_name='Relatorio')
                
            cursor.close()
            connection.close()
            
            print(json.dumps({
                "success": True,
                "path": os.path.abspath(output_excel_path),
                "rows_exported": len(records)
            }))
            
    except Error as e:
        print(json.dumps({"success": False, "error": f"Erro de banco de dados: {e}"}))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({"success": False, "error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print(json.dumps({"success": False, "error": "Uso: python gerar_excel.py <json_input> <excel_output>"}))
        sys.exit(1)

    generate_excel(sys.argv[1], sys.argv[2])
