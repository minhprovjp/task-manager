import os
import re
import sys
import zlib
import base64


def deobfuscate_php(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()

        # Match:
        # <?php eval('?>'.gzinflate(base64_decode('...'))); ?>
        pattern = r"gzinflate\s*\(\s*base64_decode\s*\(\s*'([^']+)'\s*\)\s*\)"
        match = re.search(pattern, content)

        if not match:
            print(f"Skipping (not obfuscated): {filepath}")
            return

        b64_data = match.group(1)

        # Base64 decode
        compressed = base64.b64decode(b64_data)

        # Raw DEFLATE inflate (wbits=-15)
        decompressed = zlib.decompress(compressed, -15)

        original_content = decompressed.decode('utf-8', errors='ignore')

        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(original_content)

        print(f"Deobfuscated: {filepath}")

    except Exception as e:
        print(f"Failed to deobfuscate {filepath}: {e}")


def deobfuscate_sql(target_dir):
    db_path = os.path.join(target_dir, 'task_manager.db')
    sql_path = os.path.join(target_dir, 'task_manager.sql')

    if os.path.isfile(db_path):
        try:
            print(f"Restoring database schema: {db_path} -> {sql_path}")

            with open(db_path, 'r', encoding='utf-8') as f:
                b64_data = f.read()

            # Base64 decode
            xored = base64.b64decode(b64_data)

            # XOR decrypt
            key = b'nhom4_dbs401'
            original = bytes(
                xored[i] ^ key[i % len(key)]
                for i in range(len(xored))
            )

            with open(sql_path, 'wb') as f:
                f.write(original)

            os.remove(db_path)
            print("Database schema restored successfully.")

        except Exception as e:
            print(f"Failed to restore SQL schema: {e}")


def main():
    if len(sys.argv) < 2:
        print("Usage: python3 deobfuscate.py <directory>")
        sys.exit(1)

    target_dir = sys.argv[1]

    if not os.path.isdir(target_dir):
        print(f"Error: {target_dir} is not a directory")
        sys.exit(1)

    # Restore SQL schema
    deobfuscate_sql(target_dir)

    # Restore PHP files
    print(f"Deobfuscating PHP files in {target_dir}...")

    for root, dirs, files in os.walk(target_dir):
        for file in files:
            if file.endswith('.php'):
                if file == 'constants.php':
                    continue

                filepath = os.path.join(root, file)
                deobfuscate_php(filepath)

    print("Deobfuscation complete.")


if __name__ == '__main__':
    main()