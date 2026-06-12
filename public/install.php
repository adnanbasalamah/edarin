<?php
/**
 * Edarin Installer — Single-file universal installer
 * Supports Apache & Nginx, shared hosting friendly.
 * Auto-deletes itself after successful installation.
 */

// Auto-fix: if .env is in public/ but should be in project root, move it
if (file_exists(__DIR__ . '/.env') && !file_exists(__DIR__ . '/../.env')) {
    @rename(__DIR__ . '/.env', __DIR__ . '/../.env');
}

// If already installed (app.html exists and .env exists), redirect
if (file_exists(__DIR__ . '/../.env') && file_exists(__DIR__ . '/app.html')) {
    header('Location: app.html');
    exit;
}

// Handle AJAX: test database connection
if (isset($_GET['action']) && $_GET['action'] === 'test-db') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    try {
        $mysqli = @new mysqli($input['host'], $input['username'], $input['password'], $input['database'], (int)$input['port'] ?? 3306);
        if ($mysqli->connect_errno) {
            echo json_encode(['success' => false, 'error' => $mysqli->connect_error]);
        } else {
            $mysqli->close();
            echo json_encode(['success' => true, 'message' => 'Koneksi database berhasil.']);
        }
    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle AJAX: install
if (isset($_GET['action']) && $_GET['action'] === 'install') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);

    $db = $input['db'];
    $admin = $input['admin'];

    try {
        $mysqli = @new mysqli($db['host'], $db['username'], $db['password'], $db['database'], (int)($db['port'] ?? 3306));
        if ($mysqli->connect_errno) {
            echo json_encode(['success' => false, 'error' => 'Database: ' . $mysqli->connect_error]);
            exit;
        }

        // Create tables
        $tables = getTableSchemas();
        foreach ($tables as $sql) {
            if (!$mysqli->query($sql)) {
                echo json_encode(['success' => false, 'error' => 'Gagal membuat tabel: ' . $mysqli->error]);
                exit;
            }
        }

        // Insert admin user
        $passwordHash = password_hash($admin['password'], PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, role, status, created_at, updated_at) VALUES (?, ?, ?, 'admin', 'active', NOW(), NOW())");
        $stmt->bind_param('sss', $admin['username'], $admin['email'], $passwordHash);
        $stmt->execute();

        if ($stmt->affected_rows === 0 && $mysqli->errno !== 0) {
            echo json_encode(['success' => false, 'error' => 'Gagal membuat admin: ' . $mysqli->error]);
            exit;
        }

        $mysqli->close();

        // Generate .env
        $envContent = "# Environment\nCI_ENVIRONMENT = production\n\n# Database\n";
        $envContent .= "database.default.hostname = {$db['host']}\n";
        $envContent .= "database.default.database = {$db['database']}\n";
        $envContent .= "database.default.username = {$db['username']}\n";
        $envContent .= "database.default.password = {$db['password']}\n";
        $envContent .= "database.default.DBDriver = MySQLi\n";
        $envContent .= "database.default.port = " . ((int)($db['port'] ?? 3306)) . "\n";
        $envContent .= "\n# Test Database (untuk phpunit)\n";
        $envContent .= "database.tests.hostname = {$db['host']}\n";
        $envContent .= "database.tests.database = {$db['database']}\n";
        $envContent .= "database.tests.username = {$db['username']}\n";
        $envContent .= "database.tests.password = {$db['password']}\n";
        $envContent .= "database.tests.DBDriver = MySQLi\n";
        $envContent .= "database.tests.port = " . ((int)($db['port'] ?? 3306)) . "\n";

        // Write .env — try project root first, then public/ as fallback
        $envPaths = [__DIR__ . '/../.env', __DIR__ . '/.env'];
        $envWritten = false;
        foreach ($envPaths as $envPath) {
            $envDir = dirname($envPath);
            if (!is_dir($envDir)) {
                @mkdir($envDir, 0755, true);
            }
            if (file_put_contents($envPath, $envContent) !== false) {
                chmod($envPath, 0644);
                $envWritten = true;
            }
        }
        if (!$envWritten) {
            echo json_encode(['success' => false, 'error' => 'Gagal menulis file .env. Periksa permission direktori.']);
            exit;
        }

        // Create & chmod writable directories
        $dirs = [
            __DIR__ . '/../writable',
            __DIR__ . '/../writable/cache',
            __DIR__ . '/../writable/logs',
            __DIR__ . '/../writable/session',
            __DIR__ . '/../writable/debugbar',
            __DIR__ . '/../writable/uploads',
            __DIR__ . '/../writable/uploads/stores',
        ];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @chmod($dir, 0755);
        }

        // Delete installer
        @unlink(__FILE__);

        echo json_encode(['success' => true, 'message' => 'Instalasi berhasil! Redirecting...']);

    } catch (\Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Server detection
$isApache = stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'apache') !== false;
$isNginx  = stripos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false;
$serverType = $isApache ? 'Apache' : ($isNginx ? 'Nginx' : $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown');

// Requirements check
$reqs = [
    'php_version'  => ['label' => 'PHP >= 8.2',      'pass' => version_compare(PHP_VERSION, '8.2', '>='), 'value' => PHP_VERSION],
    'ext_mysqli'   => ['label' => 'MySQLi Extension',  'pass' => extension_loaded('mysqli'), 'value' => extension_loaded('mysqli') ? 'OK' : 'Missing'],
    'ext_gd'       => ['label' => 'GD Extension',     'pass' => extension_loaded('gd'), 'value' => extension_loaded('gd') ? 'OK' : 'Missing'],
    'ext_json'     => ['label' => 'JSON Extension',    'pass' => extension_loaded('json'), 'value' => extension_loaded('json') ? 'OK' : 'Missing'],
    'ext_mbstring' => ['label' => 'MBString Extension','pass' => extension_loaded('mbstring'), 'value' => extension_loaded('mbstring') ? 'OK' : 'Missing'],
    'ext_fileinfo' => ['label' => 'Fileinfo Extension','pass' => extension_loaded('fileinfo'), 'value' => extension_loaded('fileinfo') ? 'OK' : 'Missing'],
    'writable'     => ['label' => 'Writable Permission','pass' => is_writable(__DIR__ . '/../writable'), 'value' => is_writable(__DIR__ . '/../writable') ? 'OK' : 'Not Writable'],
];

$allPass = array_reduce($reqs, fn($carry, $r) => $carry && $r['pass'], true);

// Table schemas
function getTableSchemas(): array {
    return [
        // Users
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(100) NOT NULL UNIQUE,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` ENUM('admin','distributor') NOT NULL DEFAULT 'distributor',
            `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Products
        "CREATE TABLE IF NOT EXISTS `products` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `price` DECIMAL(15,2) NOT NULL,
            `unit` VARCHAR(50) NOT NULL,
            `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Stores
        "CREATE TABLE IF NOT EXISTS `stores` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `distributor_id` INT UNSIGNED NULL,
            `name` VARCHAR(255) NOT NULL,
            `owner` VARCHAR(255) NOT NULL,
            `address` TEXT NOT NULL,
            `phone` VARCHAR(20) NOT NULL,
            `image` VARCHAR(255) NULL,
            `latitude` DECIMAL(10,7) NULL,
            `longitude` DECIMAL(10,7) NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            FOREIGN KEY (`distributor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Sales
        "CREATE TABLE IF NOT EXISTS `sales` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `client_id` VARCHAR(255) NOT NULL,
            `distributor_id` INT UNSIGNED NOT NULL,
            `store_id` INT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NOT NULL,
            `quantity` INT DEFAULT 0,
            `return_qty` INT DEFAULT 0,
            `sale_date` DATE NOT NULL,
            `sync_status` ENUM('pending','synced','failed') DEFAULT 'pending',
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            INDEX `client_id` (`client_id`),
            FOREIGN KEY (`distributor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`store_id`) REFERENCES `stores`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Audit Log
        "CREATE TABLE IF NOT EXISTS `audit_log` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NULL,
            `action` VARCHAR(50) NOT NULL,
            `entity_type` VARCHAR(50) NOT NULL,
            `entity_id` INT NULL,
            `details` TEXT NULL,
            `created_at` DATETIME NULL,
            INDEX `user_id` (`user_id`),
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Notas
        "CREATE TABLE IF NOT EXISTS `notas` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `client_id` VARCHAR(255) NOT NULL,
            `distributor_id` INT UNSIGNED NOT NULL,
            `store_id` INT UNSIGNED NOT NULL,
            `note_date` DATE NOT NULL,
            `total_value` DECIMAL(15,2) DEFAULT 0.00,
            `sync_status` ENUM('pending','synced','failed') DEFAULT 'pending',
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            INDEX `client_id` (`client_id`),
            FOREIGN KEY (`distributor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`store_id`) REFERENCES `stores`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // Nota Items
        "CREATE TABLE IF NOT EXISTS `nota_items` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `nota_id` INT UNSIGNED NOT NULL,
            `product_id` INT UNSIGNED NOT NULL,
            `quantity` INT DEFAULT 0,
            `return_qty` INT DEFAULT 0,
            `price` DECIMAL(15,2) DEFAULT 0.00,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            INDEX `nota_id` (`nota_id`),
            FOREIGN KEY (`nota_id`) REFERENCES `notas`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];
}

// If already installed, hide form
$alreadyInstalled = file_exists(__DIR__ . '/../.env');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalasi Edarin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#00468c','primary-dark':'#003063',success:'#46b450',danger:'#ba1a1a',surface:{DEFAULT:'#f9f9fc'},'on-surface':'#1a1c1e','on-surface-variant':'#424751',outline:'#c2c6d2'}}}}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}h1,h2{font-family:'IBM Plex Sans',sans-serif}.step-hidden{display:none}.spinner{border-top-color:transparent!important}</style>
</head>
<body class="bg-[#f9f9fc] min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-lg">
    <div class="bg-white rounded-xl shadow-sm border border-[#e2e2e5] p-6 md:p-8">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-primary">Instalasi Edarin</h1>
            <p class="text-sm text-[#424751] mt-1">Sistem Distribusi — Setup Awal</p>
            <p class="text-xs text-[#737782] mt-2">Server: <?= $serverType ?> | PHP <?= PHP_VERSION ?></p>
        </div>

        <!-- Requirements -->
        <div class="mb-6 p-4 bg-[#f3f3f6] rounded-lg">
            <h2 class="text-sm font-bold mb-3">System Requirements</h2>
            <div class="space-y-1.5">
                <?php foreach ($reqs as $r): ?>
                <div class="flex items-center justify-between text-xs">
                    <span class="<?= $r['pass'] ? 'text-[#006e1f]' : 'text-[#ba1a1a]' ?>">
                        <?= $r['pass'] ? '✅' : '❌' ?> <?= $r['label'] ?>
                    </span>
                    <span class="font-mono text-[#737782]"><?= $r['value'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (!$allPass): ?>
            <div class="mt-3 text-xs text-danger bg-[#ffdad6] rounded px-3 py-2">Beberapa persyaratan tidak terpenuhi. Instalasi mungkin gagal.</div>
            <?php endif; ?>
        </div>

        <?php if ($alreadyInstalled): ?>
            <!-- Already installed -->
            <div class="text-center p-4 bg-[#e8f5e9] rounded-lg">
                <p class="text-sm text-[#006e1f] font-medium">✅ Edarin sudah terinstal.</p>
                <a href="app.html" class="inline-block mt-3 rounded bg-primary px-6 py-2 text-sm font-medium text-white hover:bg-primary-dark">Buka Aplikasi</a>
            </div>
        <?php else: ?>
            <!-- Install Form -->
            <form id="installForm" class="space-y-5">
                <!-- Database -->
                <div>
                    <h2 class="text-sm font-bold mb-3 text-[#1a1c1e]">Database</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-[#424751] mb-1">Host</label>
                            <input type="text" name="db_host" value="localhost" required class="w-full min-h-[44px] rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#424751] mb-1">Port</label>
                            <input type="number" name="db_port" value="3306" required class="w-full min-h-[44px] rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#424751] mb-1">Username</label>
                            <input type="text" name="db_username" required class="w-full min-h-[44px] rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#424751] mb-1">Password</label>
                            <input type="password" name="db_password" class="w-full min-h-[44px] rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-[#424751] mb-1">Nama Database</label>
                            <input type="text" name="db_database" required class="w-full min-h-[44px] rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                    </div>
                    <button type="button" onclick="testDb()" class="mt-3 w-full min-h-[44px] rounded border border-primary text-sm font-medium text-primary hover:bg-[#d6e3ff]">Test Koneksi Database</button>
                    <div id="dbResult" class="mt-2 text-xs"></div>
                </div>

                <hr class="border-[#e2e2e5]">

                <!-- Admin Account -->
                <div>
                    <h2 class="text-sm font-bold mb-3 text-[#1a1c1e]">Akun Admin</h2>
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-[#424751] mb-1">Username</label>
                            <input type="text" name="admin_username" value="admin" required class="w-full min-h-[44px] rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#424751] mb-1">Email</label>
                            <input type="email" name="admin_email" value="admin@edarin.com" required class="w-full min-h-[44px] rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-[#424751] mb-1">Password</label>
                            <input type="password" name="admin_password" required minlength="6" class="w-full min-h-[44px] rounded border border-[#c2c6d2] px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20">
                        </div>
                    </div>
                </div>

                <div id="installError" class="hidden rounded bg-[#ffdad6] px-3 py-2 text-xs text-[#93000a]"></div>
                <div id="installSuccess" class="hidden rounded bg-[#e8f5e9] px-3 py-2 text-xs text-[#006e1f]"></div>

                <button type="button" onclick="doInstall()" id="installBtn" class="flex min-h-[44px] w-full items-center justify-center rounded bg-primary text-sm font-medium text-white hover:bg-primary-dark disabled:opacity-50">
                    <span id="btnText">Install</span>
                    <span id="btnSpinner" class="hidden items-center gap-2">
                        <span class="h-4 w-4 animate-spin rounded-full border-2 border-white spinner border-t-transparent"></span>
                        Menginstal...
                    </span>
                </button>
            </form>
        <?php endif; ?>
    </div>
    <p class="text-center text-xs text-[#737782] mt-4">Edarin v1.0 — Distribution Management System</p>
</div>

<script>
function getFormData() {
    const f = document.getElementById('installForm');
    return {
        db: {
            host: f.db_host.value,
            port: f.db_port.value,
            username: f.db_username.value,
            password: f.db_password.value,
            database: f.db_database.value,
        },
        admin: {
            username: f.admin_username.value,
            email: f.admin_email.value,
            password: f.admin_password.value,
        }
    };
}

async function testDb() {
    const result = document.getElementById('dbResult');
    result.innerHTML = '<span class="text-[#424751]">Menguji koneksi...</span>';

    try {
        const res = await fetch('?action=test-db', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(getFormData().db),
        });
        const data = await res.json();
        if (data.success) {
            result.innerHTML = '<span class="text-[#006e1f]">✅ ' + data.message + '</span>';
        } else {
            result.innerHTML = '<span class="text-[#ba1a1a]">❌ ' + data.error + '</span>';
        }
    } catch (e) {
        result.innerHTML = '<span class="text-[#ba1a1a]">❌ Gagal terhubung ke server.</span>';
    }
}

async function doInstall() {
    const btn = document.getElementById('installBtn');
    const btnText = document.getElementById('btnText');
    const spinner = document.getElementById('btnSpinner');
    const errorDiv = document.getElementById('installError');
    const successDiv = document.getElementById('installSuccess');

    btn.disabled = true;
    btnText.classList.add('hidden');
    spinner.classList.remove('hidden');
    spinner.classList.add('flex');
    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    try {
        const res = await fetch('?action=install', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(getFormData()),
        });
        const data = await res.json();

        if (data.success) {
            successDiv.classList.remove('hidden');
            successDiv.innerHTML = '✅ ' + data.message;
            // Redirect after short delay
            setTimeout(() => { window.location.href = 'app.html'; }, 2000);
        } else {
            errorDiv.classList.remove('hidden');
            errorDiv.innerHTML = '❌ ' + data.error;
            btn.disabled = false;
            btnText.classList.remove('hidden');
            spinner.classList.remove('flex');
            spinner.classList.add('hidden');
        }
    } catch (e) {
        errorDiv.classList.remove('hidden');
        errorDiv.innerHTML = '❌ Gagal terhubung ke server.';
        btn.disabled = false;
        btnText.classList.remove('hidden');
        spinner.classList.remove('flex');
        spinner.classList.add('hidden');
    }
}
</script>
</body>
</html>
