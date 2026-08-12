import sys
import os
import json
import re

# Adiciona o caminho do script original para importar as funções
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '../scripts/python')))

# Importar apenas as partes necessárias do script original ou mockar
# Como o script original é um monólito, vou copiar a função parse_boe_python modificada aqui para teste rápido
# ou simplesmente rodar o script original se ele puder ser importado.

def test_extraction():
    texto_teste = """
Natureza: CLONAGEM / ADULTERAÇÃO DE VEÍCULO AUTOMOTOR
Objetos:
FORD/KA SE 1.0 HA (VEÍCULO ADULTERADO)
Marca/Modelo: FORD/KA SE 1.0 HA
Cor: BRANCO
Placa: PZZ-1A23
Chassi: 9BWZZZ31ZLP000000

Histórico:
O veículo foi abordado...
"""
    
    # Simula o comportamento do script original
    # (Vou rodar o próprio boe_extractor.py passando um arquivo temporário)
    tmp_file = "scratch/test_input.txt"
    with open(tmp_file, "w", encoding="utf-8") as f:
        f.write(texto_teste)
    
    # Rodar o script
    import subprocess
    cmd = [sys.executable, "scripts/python/boe_extractor.py", tmp_file]
    result = subprocess.run(cmd, capture_output=True, text=True)
    
    print("Output do script:")
    print(result.stdout)
    
    try:
        # Tenta achar o JSON no output
        match = re.search(r'\{.*\}', result.stdout, re.DOTALL)
        if match:
            dados = json.loads(match.group(0))
            print("\nDados Extraídos:")
            print(json.dumps(dados, indent=2, ensure_ascii=False))
            
            # Verificações
            veiculos = dados.get('dados', {}).get('veiculos', [])
            if len(veiculos) > 0:
                print("✅ Veículo detectado!")
                print(f"Placa: {veiculos[0]['placa']}")
                print(f"Marca/Modelo: {veiculos[0]['marca_modelo']}")
            else:
                print("❌ Nenhum veículo detectado.")
        else:
            print("❌ Nenhum JSON encontrado no output.")
    except Exception as e:
        print(f"❌ Erro ao parsear JSON: {e}")

if __name__ == "__main__":
    test_extraction()
