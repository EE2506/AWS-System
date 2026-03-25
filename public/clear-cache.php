<?php
// Quick cache clearer - DELETE THIS FILE AFTER USE
$cacheDir = realpath(__DIR__ . '/../bootstrap/cache');
$deleted = [];

if ($cacheDir && is_dir($cacheDir)) {
    foreach (glob($cacheDir . '/*.php') as $file) {
        $name = basename($file);
        // Keep .gitignore, delete everything else
        if ($name !== '.gitignore') {
            @unlink($file);
            $deleted[] = $name;
        }
    }
}

echo "Cleared cache files:\n";
echo implode("\n", $deleted ?: ['(none found)']);
echo "\n\nDone! Now DELETE this file.";
