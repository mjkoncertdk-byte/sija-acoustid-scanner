<?php
/**
 * SIJA Scanner Server Test
 * Upload this next to sija-acoustid-scanner.php and open it in browser.
 */

header('Content-Type: text/plain; charset=utf-8');

echo "SIJA Scanner Server Test\n\n";
echo "PHP version: " . phpversion() . "\n";
echo "shell_exec: " . (function_exists('shell_exec') ? 'YES' : 'NO') . "\n";

echo "\nfpcalc test:\n";
$output = shell_exec('fpcalc -version 2>&1');
echo $output ?: "No fpcalc output. fpcalc may not be installed.\n";

echo "\nDone.\n";
