import os

# Arquivo original e destino
sql_file = r"I:\PHP\sisdp.statsfut.com\outros_backups\vanlim73_sisdp.sql"
output_file = r"I:\PHP\sisdp.statsfut.com\outros_backups\inserts_producao.sql"

tabelas_importar = {
    'usuario', 'cadpessoa', 'cadprincipal', 'cadintimacao',
    'cadveiculo', 'cadcelular', 'administrativo', 'administrativo_pessoas',
    'boe_pessoas_vinculos', 'apfd_pessoas_detalhes', 'sequencias_oficio'
}

with open(sql_file, 'r', encoding='utf-8') as f:
    lines = f.readlines()

output_lines = [
    "SET NAMES utf8mb4;\n",
    "SET FOREIGN_KEY_CHECKS = 0;\n",
    "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n",
    "SET AUTOCOMMIT = 0;\n",
    "START TRANSACTION;\n\n"
]

in_insert = False
current_table = ""
count = 0

for line in lines:
    if line.startswith('INSERT INTO `'):
        # Get table name
        parts = line.split('`')
        if len(parts) > 1:
            table_name = parts[1]
            if table_name in tabelas_importar:
                in_insert = True
                current_table = table_name
                output_lines.append(line)
                count += 1
            else:
                in_insert = False
        continue

    if in_insert:
        # If we reach another top level statement or comment, stop.
        if line.startswith('/*!') or line.startswith('--') or line.startswith('DROP') or line.startswith('CREATE') or line.startswith('ALTER') or line.startswith('UNLOCK'):
            in_insert = False
            continue
            
        output_lines.append(line)
        # Semicolon at the end of the line means the insert is finished usually
        if line.strip().endswith(';'):
            in_insert = False

output_lines.append("\nSET FOREIGN_KEY_CHECKS = 1;\n")
output_lines.append("COMMIT;\n")

with open(output_file, 'w', encoding='utf-8') as f:
    f.writelines(output_lines)

print(f"Extraídos {count} blocos confiaveis de INSERT para {output_file}")
