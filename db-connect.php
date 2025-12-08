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
            start_time TIME NULL,
            end_time TIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_event_date (user_id, title, event_date),
            INDEX idx_user_date (user_id, event_date),
            CONSTRAINT fk_events_user FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
    );

    $columnCheckStmt = $pdo->prepare('SHOW COLUMNS FROM events LIKE :column');
    $columnCheckStmt->execute([':column' => 'start_time']);
    if ($columnCheckStmt->rowCount() === 0) {
        $pdo->exec('ALTER TABLE events ADD COLUMN start_time TIME NULL AFTER event_date');
    }

    $columnCheckStmt->execute([':column' => 'end_time']);
    if ($columnCheckStmt->rowCount() === 0) {
        $pdo->exec('ALTER TABLE events ADD COLUMN end_time TIME NULL AFTER start_time');
    }

    // 最低限のサンプルデータを投入（デモユーザーと予定）
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare(
            'SELECT user_id FROM user WHERE mail = :mail LIMIT 1'
        );
        $stmt->bindValue(':mail', 'demo@example.com', PDO::PARAM_STR);
        $stmt->execute();
        $demoUserId = $stmt->fetchColumn();

        if (!$demoUserId) {
            $hashedPassword = '$2y$12$MmElHXstly/J6XTo7U.tc.U2MYnU6kdHgt/KaZ8p8jK/kwfL/6WYq'; // password: demo1234
            $stmt = $pdo->prepare('INSERT INTO user (user_name, mail, pass) VALUES (:name, :mail, :password)');
            $stmt->execute([
                ':name' => 'デモユーザー',
                ':mail' => 'demo@example.com',
                ':password' => $hashedPassword,
            ]);
            $demoUserId = (int)$pdo->lastInsertId();
        }

        $baseDate = new DateTime('first day of this month');
        $events = [
            ['title' => 'ミーティング', 'offsetDays' => 14, 'startTime' => '10:00', 'endTime' => '11:00'],
            ['title' => '請求書締め切り', 'offsetDays' => 19, 'startTime' => '09:00', 'endTime' => '09:30'],
            ['title' => '友人と食事', 'offsetDays' => 24, 'startTime' => '19:00', 'endTime' => '21:00'],
        ];

        $insertEventStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM events WHERE user_id = :user_id AND title = :title AND event_date = :event_date'
        );
        $createEventStmt = $pdo->prepare(
            'INSERT INTO events (user_id, title, event_date, start_time, end_time)
             VALUES (:user_id, :title, :event_date, :start_time, :end_time)'
        );

        foreach ($events as $event) {
            $eventDate = clone $baseDate;
            $eventDate->modify('+' . $event['offsetDays'] . ' days');
            $eventDateString = $eventDate->format('Y-m-d');

            $insertEventStmt->execute([
                ':user_id' => $demoUserId,
                ':title' => $event['title'],
                ':event_date' => $eventDateString,
            ]);

            if ($insertEventStmt->fetchColumn() == 0) {
                $createEventStmt->execute([
                    ':user_id' => $demoUserId,
                    ':title' => $event['title'],
                    ':event_date' => $eventDateString,
                    ':start_time' => $event['startTime'],
                    ':end_time' => $event['endTime'],
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $bootstrapException) {
        $pdo->rollBack();
        throw $bootstrapException;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo '<p>データベース接続に失敗しました: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}
