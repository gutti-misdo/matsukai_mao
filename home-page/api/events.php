<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$respond = function (int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
};

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    $respond(401, ['error' => 'ログインしてください。']);
    exit;
}

require_once __DIR__ . '/../../db-connect.php';

$method = $_SERVER['REQUEST_METHOD'];
$userId = (int)$_SESSION['user_id'];

if ($userId <= 0) {
    $respond(401, ['error' => 'ユーザー情報を確認してください。']);
    exit;
}

if ($method === 'GET') {
    $month = $_GET['month'] ?? '';
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        $respond(400, ['error' => 'month は YYYY-MM 形式で指定してください。']);
        exit;
    }

    $startDate = $month . '-01';
    $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

    try {
        $stmt = $pdo->prepare('SELECT event_id, title, event_date, start_time, end_time FROM events WHERE user_id = :user_id AND event_date >= :start AND event_date < :end ORDER BY event_date, start_time IS NULL, start_time');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':start', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':end', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        $events = $stmt->fetchAll();
        $respond(200, ['events' => $events]);
    } catch (PDOException $e) {
        error_log('GET /api/events: ' . $e->getMessage());
        $respond(500, ['error' => '予定の取得に失敗しました。']);
    }
    exit;
}

if ($method === 'POST') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        $respond(400, ['error' => '不正なリクエスト形式です。']);
        exit;
    }
    $title = trim($input['title'] ?? '');
    $eventDate = $input['event_date'] ?? '';
    $startTime = trim($input['start_time'] ?? '');
    $endTime = trim($input['end_time'] ?? '');

    if ($title === '' || $eventDate === '' || $startTime === '' || $endTime === '') {
        $respond(400, ['error' => 'タイトル・日付・開始時間・終了時間を入力してください。']);
        exit;
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
        $respond(400, ['error' => '日付は YYYY-MM-DD 形式で指定してください。']);
        exit;
    }

    if (mb_strlen($title) > 255) {
        $respond(400, ['error' => 'タイトルは255文字以内で入力してください。']);
        exit;
    }

    $timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d$/';
    if (!preg_match($timePattern, $startTime) || !preg_match($timePattern, $endTime)) {
        $respond(400, ['error' => '時間は HH:MM 形式で入力してください。']);
        exit;
    }

    $startDateTime = DateTime::createFromFormat('H:i', $startTime);
    $endDateTime = DateTime::createFromFormat('H:i', $endTime);

    if (!$startDateTime || !$endDateTime || $startDateTime >= $endDateTime) {
        $respond(400, ['error' => '終了時間は開始時間より後に設定してください。']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO events (user_id, title, event_date, start_time, end_time) VALUES (:user_id, :title, :event_date, :start_time, :end_time)');
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':event_date', $eventDate, PDO::PARAM_STR);
        $stmt->bindValue(':start_time', $startTime, PDO::PARAM_STR);
        $stmt->bindValue(':end_time', $endTime, PDO::PARAM_STR);
        $stmt->execute();

        $eventId = $pdo->lastInsertId();
        $respond(201, [
            'event_id' => $eventId,
            'title' => $title,
            'event_date' => $eventDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);
    } catch (PDOException $e) {
        error_log('POST /api/events: ' . $e->getMessage());
        $respond(500, ['error' => '予定の保存に失敗しました。']);
    }
    exit;
}

$respond(405, ['error' => '許可されていないメソッドです。']);
