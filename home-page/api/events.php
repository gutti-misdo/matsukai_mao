<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'ログインしてください。']);
    exit;
}

require_once __DIR__ . '/../../db-connect.php';

$method = $_SERVER['REQUEST_METHOD'];
$userId = (int)($_SESSION['user_id'] ?? 0);

if ($method === 'GET') {
    $month = $_GET['month'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        http_response_code(400);
        echo json_encode(['error' => 'month は YYYY-MM 形式で指定してください。']);
        exit;
    }

    $startDate = $month . '-01';
    $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

    try {
        $stmt = $pdo->prepare('SELECT event_id, title, event_date FROM events WHERE user_id = :user_id AND event_date >= :start AND event_date < :end ORDER BY event_date');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':start', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        $events = $stmt->fetchAll();
        echo json_encode(['events' => $events]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => '予定の取得に失敗しました。']);
    }
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? '');
    $eventDate = $input['event_date'] ?? '';

    if ($title === '' || $eventDate === '') {
        http_response_code(400);
        echo json_encode(['error' => 'タイトルと日付は必須です。']);
        exit;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
        http_response_code(400);
        echo json_encode(['error' => '日付は YYYY-MM-DD 形式で指定してください。']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO events (user_id, title, event_date) VALUES (:user_id, :title, :event_date)');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':event_date', $eventDate, PDO::PARAM_STR);
        $stmt->execute();

        $eventId = $pdo->lastInsertId();
        echo json_encode([
            'event_id' => $eventId,
            'title' => $title,
            'event_date' => $eventDate,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => '予定の保存に失敗しました。']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => '許可されていないメソッドです。']);
