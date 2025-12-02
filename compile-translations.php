<?php
/**
 * Compile Translation Files (.po to .mo)
 * Run this file to compile .po files into .mo files
 * 
 * Usage: php compile-translations.php
 */

// Check if we're running from command line
if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

echo "WooCommerce Booking Exporter - Translation Compiler\n";
echo "===================================================\n\n";

$languages_dir = __DIR__ . '/languages/';

if (!is_dir($languages_dir)) {
    die("Error: Languages directory not found at: $languages_dir\n");
}

// Find all .po files
$po_files = glob($languages_dir . '*.po');

if (empty($po_files)) {
    die("Error: No .po files found in: $languages_dir\n");
}

echo "Found " . count($po_files) . " translation file(s):\n";

foreach ($po_files as $po_file) {
    $filename = basename($po_file);
    $mo_file = str_replace('.po', '.mo', $po_file);
    
    echo "\nCompiling: $filename\n";
    
    // Check if msgfmt command exists
    $msgfmt_check = shell_exec('which msgfmt 2>&1');
    
    if (empty($msgfmt_check)) {
        echo "  Warning: msgfmt command not found. Using PHP fallback...\n";
        
        // PHP fallback compilation
        $result = compile_po_to_mo_php($po_file, $mo_file);
        
        if ($result) {
            echo "  ✓ Successfully compiled to: " . basename($mo_file) . "\n";
        } else {
            echo "  ✗ Failed to compile!\n";
        }
    } else {
        // Use msgfmt command
        $output = [];
        $return_var = 0;
        
        exec("msgfmt -o " . escapeshellarg($mo_file) . " " . escapeshellarg($po_file) . " 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "  ✓ Successfully compiled to: " . basename($mo_file) . "\n";
            echo "  File size: " . format_bytes(filesize($mo_file)) . "\n";
        } else {
            echo "  ✗ Failed to compile!\n";
            echo "  Error: " . implode("\n  ", $output) . "\n";
        }
    }
}

echo "\n===================================================\n";
echo "Compilation complete!\n\n";
echo "Next steps:\n";
echo "1. Upload the .mo files to your WordPress site\n";
echo "2. Make sure they're in the /languages/ directory\n";
echo "3. Clear your WordPress cache\n";
echo "4. Test the translations in WordPress admin\n\n";

/**
 * PHP fallback function to compile .po to .mo
 */
function compile_po_to_mo_php($po_file, $mo_file) {
    $po_content = file_get_contents($po_file);
    
    if ($po_content === false) {
        return false;
    }
    
    // Simple regex-based parser
    preg_match_all('/msgid\s+"(.+?)"\s+msgstr\s+"(.+?)"/s', $po_content, $matches, PREG_SET_ORDER);
    
    $translations = [];
    foreach ($matches as $match) {
        $msgid = stripcslashes($match[1]);
        $msgstr = stripcslashes($match[2]);
        
        if (!empty($msgid) && !empty($msgstr)) {
            $translations[$msgid] = $msgstr;
        }
    }
    
    if (empty($translations)) {
        return false;
    }
    
    // Create simple .mo file structure
    // This is a simplified version and may not work for all cases
    // For production, use msgfmt command
    
    $mo_data = pack('Iiiiiii', 0x950412de, 0, count($translations), 28, 28 + count($translations) * 8, 0, 28 + count($translations) * 16);
    
    $ids = '';
    $strs = '';
    $offsets = [];
    
    foreach ($translations as $id => $str) {
        $offsets[] = [strlen($ids), strlen($id), strlen($strs), strlen($str)];
        $ids .= $id . "\0";
        $strs .= $str . "\0";
    }
    
    $keyoffset = 28 + count($translations) * 16;
    $valueoffset = $keyoffset + strlen($ids);
    
    foreach ($offsets as $offset) {
        $mo_data .= pack('ii', $offset[1], $keyoffset + $offset[0]);
        $mo_data .= pack('ii', $offset[3], $valueoffset + $offset[2]);
    }
    
    $mo_data .= $ids . $strs;
    
    return file_put_contents($mo_file, $mo_data) !== false;
}

/**
 * Format bytes to human readable format
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

