<?php
// Save this as C:\xampp\htdocs\CStwIT\api\users\debug.php

echo "Checking paths:<br>";
echo "Current file: " . __FILE__ . "<br>";
echo "One level up: " . dirname(__FILE__) . "<br>";
echo "Two levels up: " . dirname(dirname(__FILE__)) . "<br>";
echo "Three levels up: " . dirname(dirname(dirname(__FILE__))) . "<br>";

$functionsPath = dirname(dirname(dirname(__FILE__))) . '/includes/functions.php';
echo "Functions path: " . $functionsPath . "<br>";
echo "File exists: " . (file_exists($functionsPath) ? "YES" : "NO") . "<br>";

// Try to list files in the includes directory
$includesDir = dirname(dirname(dirname(__FILE__))) . '/includes';
echo "Includes directory: " . $includesDir . "<br>";
echo "Directory exists: " . (is_dir($includesDir) ? "YES" : "NO") . "<br>";

if (is_dir($includesDir)) {
    echo "Files in includes directory:<br>";
    $files = scandir($includesDir);
    foreach ($files as $file) {
        echo "- " . $file . "<br>";
    }
}