<?php
/**
 * Diagnostic file to check plugin requirements
 * Upload this file and access it via browser to see what's missing
 * URL: https://yoursite.com/wp-content/plugins/iyoraa/check-plugin.php
 */

echo "<h1>Iyoraa Plugin Diagnostic</h1>";
echo "<pre>";

$pluginDir = __DIR__;

// Check vendor/autoload.php
echo "1. Checking vendor/autoload.php: ";
if (file_exists($pluginDir . '/vendor/autoload.php')) {
    echo "✓ EXISTS\n";
} else {
    echo "✗ MISSING (CRITICAL ERROR - This is why plugin fails!)\n";
}

// Check vendor/composer directory
echo "2. Checking vendor/composer/: ";
if (is_dir($pluginDir . '/vendor/composer')) {
    echo "✓ EXISTS\n";
    $composerFiles = scandir($pluginDir . '/vendor/composer');
    echo "   Files: " . implode(', ', array_diff($composerFiles, ['.', '..'])) . "\n";
} else {
    echo "✗ MISSING\n";
}

// Check main files
echo "3. Checking iyoraa.php: ";
echo file_exists($pluginDir . '/iyoraa.php') ? "✓ EXISTS\n" : "✗ MISSING\n";

echo "4. Checking inc/Core/Main.php: ";
echo file_exists($pluginDir . '/inc/Core/Main.php') ? "✓ EXISTS\n" : "✗ MISSING\n";

echo "5. Checking inc/Core/Activator.php: ";
echo file_exists($pluginDir . '/inc/Core/Activator.php') ? "✓ EXISTS\n" : "✗ MISSING\n";

echo "6. Checking inc/Core/Database.php: ";
echo file_exists($pluginDir . '/inc/Core/Database.php') ? "✓ EXISTS\n" : "✗ MISSING\n";

// Check assets
echo "7. Checking assets/dist/: ";
if (is_dir($pluginDir . '/assets/dist')) {
    $distFiles = scandir($pluginDir . '/assets/dist');
    echo "✓ EXISTS (" . count(array_diff($distFiles, ['.', '..'])) . " files)\n";
} else {
    echo "✗ MISSING\n";
}

// List vendor directory contents
echo "\n8. Vendor directory contents:\n";
if (is_dir($pluginDir . '/vendor')) {
    $vendorContents = scandir($pluginDir . '/vendor');
    foreach (array_diff($vendorContents, ['.', '..']) as $item) {
        $itemPath = $pluginDir . '/vendor/' . $item;
        $type = is_dir($itemPath) ? '[DIR]' : '[FILE]';
        echo "   $type $item\n";
    }
} else {
    echo "   ✗ VENDOR DIRECTORY MISSING!\n";
}

// PHP version
echo "\n9. PHP Version: " . phpversion() . "\n";

echo "\n10. WordPress installed: ";
if (file_exists($pluginDir . '/../../wp-load.php')) {
    echo "✓ YES\n";
} else {
    echo "? Cannot verify\n";
}

echo "\n</pre>";

echo "<h2>SOLUTION:</h2>";
echo "<p>If vendor/autoload.php is MISSING, you need to:</p>";
echo "<ol>";
echo "<li>Upload <strong>vendor/autoload.php</strong> file</li>";
echo "<li>Upload entire <strong>vendor/composer/</strong> directory</li>";
echo "<li>These files are REQUIRED for the plugin to work!</li>";
echo "</ol>";
?>
