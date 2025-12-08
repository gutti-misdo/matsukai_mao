<?php
$database = 'matsukai';
$dsn = "mysql:host=localhost;dbname={$database};charset=utf8mb4";
$dbUser = 'root';
$dbPassword = '';

$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPassword, $pdoOptions);
    } catch (PDOException $e) {
        // データベース未作成の場合は作成してから再接続
        if ($e->getCode() === 1049) { // Unknown database
            $bootstrapPdo = new PDO('mysql:host=localhost;charset=utf8mb4', $dbUser, $dbPassword, $pdoOptions);
            $bootstrapPdo->exec(
                "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
            );
            $pdo = new PDO($dsn, $dbUser, $dbPassword, $pdoOptions);
        } else {
            throw $e;
        }
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS user (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            user_name VARCHAR(255) NOT NULL,
            mail VARCHAR(255) NOT NULL UNIQUE,
            pass VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS events (
            event_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            event_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_date (user_id, event_date),
            CONSTRAINT fk_events_user FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo '<p>データベース接続に失敗しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}
