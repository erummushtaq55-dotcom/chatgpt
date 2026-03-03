<?php
$host = 'localhost';
$db = 'rait_raiti117_5';
$user = 'rait_raiti117_5';
$pass = '123456';
$charset = 'utf8mb4';

// Disable error reporting for cleaner JSON responses in API files
// But keep log_errors on for debugging
error_reporting(0);
ini_set('display_errors', 0);

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Auto-create tables if they don't exist (Self-healing)
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql_users);

    $sql_chats = "CREATE TABLE IF NOT EXISTS chats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        response TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql_chats);

} catch (\PDOException $e) {
    // If connection fails, stop script and show error (or handle gracefully)
    // For API calls, this might break JSON, but for initial load it's fine.
    // simpler to just die with a clear message.
    die("Database Connection Failed: " . $e->getMessage());
}
?>
