import os
import glob

for f in glob.glob("*.php"):
    with open(f, 'r') as file:
        content = file.read()
    
    target = '<a href="<?php echo SITEURL; ?>contact.php">Contact Us</a>'
    
    if target in content and "logout.php" not in content:
        # Check indentation for the target line
        lines = content.split('\n')
        indent = ""
        for line in lines:
            if target in line:
                indent = line[:line.find('<a href')]
                break
                
        replacement = target + '\n' + indent + '<a href="<?php echo SITEURL; ?>logout.php">Logout</a>'
        content = content.replace(target, replacement)
        
        with open(f, 'w') as file:
            file.write(content)
        print(f"Updated {f}")
