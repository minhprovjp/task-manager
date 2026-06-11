import os
import sys
import zlib
import base64

def obfuscate_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        # Prepend '?>' to the content so eval starts in HTML mode
        code_str = content
        
        # Compress with raw DEFLATE (wbits = -15)
        compressor = zlib.compressobj(9, zlib.DEFLATED, -15)
        compressed = compressor.compress(code_str.encode('utf-8'))
        compressed += compressor.flush()
        
        b64 = base64.b64encode(compressed).decode('utf-8')
        
        obfuscated_content = f"<?php eval('?>'.gzinflate(base64_decode('{b64}'))); ?>"
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(obfuscated_content)
    except Exception as e:
        print(f"Failed to obfuscate {filepath}: {e}")

def obfuscate_sql(target_dir):
    sql_path = os.path.join(target_dir, 'task_manager.sql')
    db_path = os.path.join(target_dir, 'task_manager.db')
    if os.path.isfile(sql_path):
        try:
            print(f"Obfuscating database schema: {sql_path} -> {db_path}")
            with open(sql_path, 'rb') as f:
                data = f.read()
            
            key = b'nhom4_dbs401'
            xored = bytes(data[i] ^ key[i % len(key)] for i in range(len(data)))
            b64 = base64.b64encode(xored).decode('utf-8')
            
            with open(db_path, 'w', encoding='utf-8') as f:
                f.write(b64)
            
            os.remove(sql_path)
            print("Database schema obfuscated successfully.")
        except Exception as e:
            print(f"Failed to obfuscate SQL schema: {e}")

def main():
    if len(sys.argv) < 2:
        print("Usage: python3 obfuscate.py <directory>")
        sys.exit(1)
        
    target_dir = sys.argv[1]
    if not os.path.isdir(target_dir):
        print(f"Error: {target_dir} is not a directory")
        sys.exit(1)
        
    obfuscate_sql(target_dir)
    
    print(f"Obfuscating PHP files in {target_dir}...")
    for root, dirs, files in os.walk(target_dir):
        for file in files:
            if file.endswith('.php'):
                # Skip config/constants.php
                if file == 'constants.php':
                    continue
                filepath = os.path.join(root, file)
                print(f"Obfuscating: {filepath}")
                obfuscate_file(filepath)
    print("Obfuscation complete.")

if __name__ == '__main__':
    main()
