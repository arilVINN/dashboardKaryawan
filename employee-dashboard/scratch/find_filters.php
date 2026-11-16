<?php

$dirs = [
    'resources/views/finance',
    'resources/views/kadiv',
];

function scanDirRec($dir) {
    $files = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $files = array_merge($files, scanDirRec($path));
        } elseif (str_ends_with($item, '.blade.php')) {
            $files[] = $path;
        }
    }
    return $files;
}

$allFiles = [];
foreach ($dirs as $d) {
    if (is_dir($d)) {
        $allFiles = array_merge($allFiles, scanDirRec($d));
    }
}

foreach ($allFiles as $file) {
    $content = file_get_contents($file);
    if (preg_match('/<form[^>]*method=[\'"]GET[\'"][^>]*>/i', $content, $m)) {
        echo "FILE: $file\n";
        // Find form content up to </form>
        if (preg_match('/<form[^>]*method=[\'"]GET[\'"][^>]*>([\s\S]*?)<\/form>/i', $content, $formMatch)) {
            $formBody = $formMatch[0];
            $lines = explode("\n", $formBody);
            foreach ($lines as $line) {
                if (stripos($line, '<button') !== false || stripos($line, 'filter') !== false || stripos($line, 'onchange') !== false || stripos($line, '<select') !== false || stripos($line, '<input') !== false) {
                    echo "   " . trim($line) . "\n";
                }
            }
        }
        echo "\n";
    }
}
