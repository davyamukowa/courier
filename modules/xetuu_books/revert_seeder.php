<?php
$host = 'localhost';
$db   = 'perfex_crm';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$prefix = 'tbl';
$config_file = __DIR__ . '/../../application/config/app-config.php';
if (file_exists($config_file)) {
    $config_content = file_get_contents($config_file);
    if (preg_match('/\$db_prefix\s*=\s*[\'"]([^\'"]+)[\'"]/', $config_content, $matches)) {
        $prefix = $matches[1];
    }
}

// Clear the tables we modified
$pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
$pdo->exec("TRUNCATE TABLE {$prefix}acc_move_lines;");
$pdo->exec("TRUNCATE TABLE {$prefix}acc_moves;");
$pdo->exec("TRUNCATE TABLE {$prefix}acc_accounts;");
$pdo->exec("TRUNCATE TABLE {$prefix}acc_journals;");
$pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

echo "Tables truncated. Now running migration to restore defaults...\n";

// Execute migration
$sql_file = __DIR__ . '/xetuu_books_migration.sql';
if (file_exists($sql_file)) {
    $sql = file_get_contents($sql_file);
    $sql = preg_replace('/\bacc_/', $prefix . 'acc_', $sql);
    $queries = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($queries as $query) {
        if (!empty($query)) {
            $pdo->exec($query);
        }
    }
    echo "Defaults restored successfully.\n";
} else {
    echo "Migration file not found.\n";
}
