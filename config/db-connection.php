<?php
$isDocker = getenv('DB_HOST') !== false;

$dbHost = $isDocker ? getenv('DB_HOST') : 'localhost';
$dbName = $isDocker ? getenv('DB_NAME') : 'chards';
$dbUser = $isDocker ? getenv('DB_USER') : 'your_infinityfree_user';
$dbPass = $isDocker ? getenv('DB_PASS') : 'your_infinityfree_pass';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    $pdo = null;
}
?>