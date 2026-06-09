<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

$step = (int) ($_GET['step'] ?? 1);
$error = '';

if ($step === 1 && file_exists(__DIR__ . '/../.env') && file_exists(__DIR__ . '/../.installed')) {
    header('Location: ./');
    exit;
}

$requiredExtensions = ['mysqli', 'pdo_mysql', 'mbstring', 'json', 'curl', 'intl'];
$writablePaths = [__DIR__ . '/../writable', __DIR__ . '/../.env'];

function checkExtension(string $ext): bool { return extension_loaded($ext); }
function checkWritable(string $path): bool { return file_exists($path) && is_writable($path); }

function testDBConnection(string $host, string $port, string $user, string $pass): bool {
    try {
        new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        return true;
    } catch (PDOException) { return false; }
}

function getTablesSQL(): array {
    return [
        "CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin','distributor') NOT NULL DEFAULT 'distributor',
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            price DECIMAL(12,2) NOT NULL DEFAULT 0,
            unit VARCHAR(50) NOT NULL DEFAULT 'pcs',
            status ENUM('active','inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS stores (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            owner VARCHAR(255) NOT NULL,
            address TEXT NOT NULL,
            phone VARCHAR(20) NOT NULL,
            latitude DECIMAL(10,7) NULL,
            longitude DECIMAL(10,7) NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS sales (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            client_id VARCHAR(255) NOT NULL,
            distributor_id INT UNSIGNED NOT NULL,
            store_id INT UNSIGNED NOT NULL,
            product_id INT UNSIGNED NOT NULL,
            quantity INT NOT NULL DEFAULT 0,
            return_qty INT NOT NULL DEFAULT 0,
            sale_date DATE NOT NULL,
            sync_status ENUM('pending','synced','failed') NOT NULL DEFAULT 'pending',
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            INDEX idx_client_id (client_id),
            CONSTRAINT fk_sales_distributor FOREIGN KEY (distributor_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_sales_store FOREIGN KEY (store_id) REFERENCES stores(id) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_sales_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS audit_log (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            action VARCHAR(255) NOT NULL,
            entity_type VARCHAR(100) NOT NULL,
            entity_id INT UNSIGNED NULL,
            details TEXT NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
}

function generateKey(): string { return 'base64:' . base64_encode(random_bytes(32)); }

if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $dbName = $_POST['db_name'] ?? 'edarin_db';
    $adminUser = $_POST['admin_user'] ?? 'admin';
    $adminEmail = $_POST['admin_email'] ?? 'admin@edarin.com';
    $adminPass = $_POST['admin_pass'] ?? '';
    $baseUrl = rtrim($_POST['base_url'] ?? '', '/');

    if (!$dbUser || !$dbName || !$adminUser || !$adminEmail || !$adminPass || !$baseUrl) {
        $error = 'Semua field wajib diisi.';
    } elseif (strlen($adminPass) < 6) {
        $error = 'Password admin minimal 6 karakter.';
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email admin tidak valid.';
    } elseif (!preg_match('#^https?://#', $baseUrl)) {
        $error = 'Base URL harus diawali http:// atau https://';
    } elseif (!testDBConnection($dbHost, $dbPort, $dbUser, $dbPass)) {
        $error = 'Gagal koneksi ke database. Periksa host, user, dan password.';
    } else {
        try {
            $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo->exec("USE `{$dbName}`");

            $encryptionKey = generateKey();
            $jwtKey = generateKey();

            $envContent = "# Edarin Environment\n";
            $envContent .= "CI_ENVIRONMENT = production\n";
            $envContent .= "app.baseURL = '{$baseUrl}/'\n";
            $envContent .= "app.indexPage = ''\n";
            $envContent .= "\n# Database\n";
            $envContent .= "database.default.hostname = {$dbHost}\n";
            $envContent .= "database.default.port = {$dbPort}\n";
            $envContent .= "database.default.database = {$dbName}\n";
            $envContent .= "database.default.username = {$dbUser}\n";
            $envContent .= "database.default.password = {$dbPass}\n";
            $envContent .= "database.default.DBDriver = MySQLi\n";
            $envContent .= "\n# Encryption\n";
            $envContent .= "encryption.key = {$encryptionKey}\n";
            $envContent .= "jwt.key = {$jwtKey}\n";
            $envContent .= "\n# Test Database\n";
            $envContent .= "database.tests.hostname = {$dbHost}\n";
            $envContent .= "database.tests.port = {$dbPort}\n";
            $envContent .= "database.tests.database = edarin_test\n";
            $envContent .= "database.tests.username = {$dbUser}\n";
            $envContent .= "database.tests.password = {$dbPass}\n";
            $envContent .= "database.tests.DBDriver = MySQLi\n";
            file_put_contents(__DIR__ . '/../.env', $envContent);

            foreach (getTablesSQL() as $sql) {
                try { $pdo->exec($sql); } catch (PDOException) {}
            }

            $hash = password_hash($adminPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$adminUser]);
            if (!$stmt->fetch()) {
                $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status, created_at, updated_at) VALUES (?, ?, ?, 'admin', 'active', NOW(), NOW())")
                    ->execute([$adminUser, $adminEmail, $hash]);
            }

            file_put_contents(__DIR__ . '/../.installed', date('Y-m-d H:i:s'));
            @rename(__FILE__, __FILE__ . '.done');
            $successMsg = "Instalasi berhasil! Silakan login dengan username <strong>{$adminUser}</strong> dan password yang Anda buat. File installer otomatis dinonaktifkan.";
            $step = 3;
        } catch (PDOException $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Edarin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#00468c','primary-dark':'#003063'},fontFamily:{heading:['IBM Plex Sans','sans-serif'],body:['Inter','sans-serif']}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="bg-[#f9f9fc] font-body text-[#1a1c1e] min-h-screen">
<div class="mx-auto max-w-2xl px-4 py-12">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold font-heading text-primary">Edarin</h1>
        <p class="mt-2 text-[#424751]">Instalasi Sistem Distribusi</p>
    </div>

    <?php if ($step === 1): ?>
        <div class="rounded-lg bg-white border border-[#e2e2e5] p-6">
            <h2 class="text-lg font-bold font-heading mb-4">Pemeriksaan Sistem</h2>
            <?php $allOk = true; $v = phpversion(); $phpOk = version_compare($v, '8.0', '>='); if (!$phpOk) $allOk = false; ?>
            <div class="space-y-2 mb-6">
                <div class="flex justify-between py-2 border-b border-[#f3f3f6]">
                    <span>PHP <?= $v ?></span>
                    <span class="font-medium <?= $phpOk ? 'text-[#006e1f]' : 'text-[#ba1a1a]' ?>"><?= $phpOk ? 'OK' : 'Need 8.0+' ?></span>
                </div>
                <?php foreach ($requiredExtensions as $ext): ?>
                    <?php $ok = checkExtension($ext); if (!$ok) $allOk = false; ?>
                    <div class="flex justify-between py-2 border-b border-[#f3f3f6]">
                        <span><?= $ext ?></span>
                        <span class="font-medium <?= $ok ? 'text-[#006e1f]' : 'text-[#ba1a1a]' ?>"><?= $ok ? 'OK' : 'Missing' ?></span>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($writablePaths as $p): ?>
                    <?php $ok = checkWritable($p); if (!$ok) $allOk = false; ?>
                    <div class="flex justify-between py-2 border-b border-[#f3f3f6]">
                        <span class="text-sm">Writable: <?= str_replace(__DIR__ . '/../', '', $p) ?></span>
                        <span class="font-medium <?= $ok ? 'text-[#006e1f]' : 'text-[#ba1a1a]' ?>"><?= $ok ? 'OK' : 'Not Writable' ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($allOk): ?>
                <a href="?step=2" class="flex min-h-[44px] w-full items-center justify-center rounded bg-primary font-medium text-white hover:bg-primary-dark">Lanjut</a>
            <?php else: ?>
                <div class="rounded bg-[#ffdad6] px-4 py-3 text-sm text-[#93000a] mb-4">Beberapa requirement belum terpenuhi. Perbaiki lalu refresh halaman.</div>
                <button disabled class="flex min-h-[44px] w-full items-center justify-center rounded bg-[#c2c6d2] font-medium text-white cursor-not-allowed">Lanjut</button>
            <?php endif; ?>
        </div>

    <?php elseif ($step === 2): ?>
        <div class="rounded-lg bg-white border border-[#e2e2e5] p-6">
            <h2 class="text-lg font-bold font-heading mb-4">Konfigurasi</h2>
            <?php if ($error): ?>
                <div class="rounded bg-[#ffdad6] px-4 py-3 text-sm text-[#93000a] mb-4"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm text-[#424751]">Base URL</label>
                    <input type="text" name="base_url" value="<?= ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') ?>" required class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20" placeholder="https://domain-anda.com">
                </div>
                <div class="border-t border-[#e2e2e5] pt-4">
                    <h3 class="text-sm font-bold text-[#424751] mb-3">Database MySQL</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="mb-1 block text-sm text-[#424751]">Host</label><input type="text" name="db_host" value="localhost" required class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></div>
                        <div><label class="mb-1 block text-sm text-[#424751]">Port</label><input type="text" name="db_port" value="3306" required class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></div>
                    </div>
                    <div class="mt-3"><label class="mb-1 block text-sm text-[#424751]">Nama Database</label><input type="text" name="db_name" value="edarin_db" required class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div><label class="mb-1 block text-sm text-[#424751]">Username</label><input type="text" name="db_user" required class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></div>
                        <div><label class="mb-1 block text-sm text-[#424751]">Password</label><input type="password" name="db_pass" class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></div>
                    </div>
                </div>
                <div class="border-t border-[#e2e2e5] pt-4">
                    <h3 class="text-sm font-bold text-[#424751] mb-3">Akun Admin</h3>
                    <div><label class="mb-1 block text-sm text-[#424751]">Username</label><input type="text" name="admin_user" value="admin" required class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></div>
                    <div class="mt-3"><label class="mb-1 block text-sm text-[#424751]">Email</label><input type="email" name="admin_email" value="admin@edarin.com" required class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></div>
                    <div class="mt-3"><label class="mb-1 block text-sm text-[#424751]">Password (min. 6 karakter)</label><input type="password" name="admin_pass" required minlength="6" class="min-h-[44px] w-full rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20"></div>
                </div>
                <button type="submit" class="flex min-h-[44px] w-full items-center justify-center rounded bg-primary font-medium text-white hover:bg-primary-dark">Install</button>
            </form>
        </div>

    <?php elseif ($step === 3): ?>
        <div class="rounded-lg bg-white border border-[#e2e2e5] p-6 text-center">
            <div class="mb-4 text-4xl">✅</div>
            <h2 class="text-lg font-bold font-heading mb-2">Instalasi Selesai!</h2>
            <div class="rounded bg-[#e8f5e9] px-4 py-3 text-sm text-[#006e1f] mb-4"><?= $successMsg ?></div>
            <div class="rounded bg-[#fff3cd] px-4 py-3 text-sm text-[#856404] mb-6 text-left">
                <p class="font-medium mb-1">Penting:</p>
                <ol class="list-decimal ml-4 space-y-1">
                    <li>Hapus file <code class="bg-[#ffeeba] px-1 rounded">public/install.php</code> untuk keamanan.</li>
                    <li>Login di <a href="./app.html" class="text-primary underline">./app.html</a></li>
                </ol>
            </div>
            <a href="./app.html" class="inline-flex min-h-[44px] items-center justify-center rounded bg-primary px-8 font-medium text-white hover:bg-primary-dark">Buka Aplikasi</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
