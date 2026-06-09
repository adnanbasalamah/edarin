<?php
/**
 * Fix JWT Key - tambahkan jwt.key ke .env, lalu HAPUS file ini
 */
$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    die('.env not found.');
}

$content = file_get_contents($envFile);

// Cek apakah jwt.key sudah ada
if (strpos($content, 'jwt.key') !== false) {
    echo "jwt.key already exists in .env. No fix needed.<br>";
    echo "<strong>Delete this file: fix-jwt.php</strong>";
    exit;
}

// Generate 32-byte random key (64 hex chars, well above 32 min)
$key = bin2hex(random_bytes(32));

// Tambahkan setelah encryption.key
$content = preg_replace(
    '/(encryption\.key\s*=.*)/',
    "$1\njwt.key = {$key}",
    $content
);

if (file_put_contents($envFile, $content)) {
    echo "<strong>SUCCESS!</strong> jwt.key added to .env<br>";
    echo "Key length: " . strlen($key) . " chars (minimum 32 required)<br><br>";
    echo "Now try to <a href='app.html'>login</a> again.<br><br>";
    echo "<strong style='color:red'>DELETE this file (fix-jwt.php) immediately!</strong>";
} else {
    echo "ERROR: Could not write to .env file.";
}
