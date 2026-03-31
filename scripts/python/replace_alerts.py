import os
import re

directory = r"i:\PHP\sisdp.statsfut.com\public\js"

count = 0
for root, _, files in os.walk(directory):
    for file in files:
        if file.endswith(".js"):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            
            original_content = content

            # Generic replace for alert(...) that is not preceded by a dot
            # This turns `alert(anything);` into `Swal.fire("Atenção", anything, "warning");`
            content = re.sub(r"(?<!\.)\balert\((['\"].*?['\"]|[^()]+)\);", r'Swal.fire("Atenção", \1, "warning");', content)
            
            if content != original_content:
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f"Updated: {path}")
                count += 1

print(f"Done. Updated {count} files.")
