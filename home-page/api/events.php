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

$partsIdColumn = 'part_id';
$eventsPartColumn = 'part_id';

try {
    $partsColumnCheck = $pdo->prepare("SHOW COLUMNS FROM parts LIKE 'parts_id'");
    $partsColumnCheck->execute();
    if ($partsColumnCheck->rowCount() > 0) {
        $partsIdColumn = 'parts_id';
    }

    $eventsColumnCheck = $pdo->prepare("SHOW COLUMNS FROM events LIKE 'parts_id'");
    $eventsColumnCheck->execute();
    if ($eventsColumnCheck->rowCount() > 0) {
        $eventsPartColumn = 'parts_id';
    }
} catch (PDOException $columnException) {
    error_log('COLUMN CHECK /api/events: ' . $columnException->getMessage());
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
        $stmt = $pdo->prepare(
            "SELECT e.event_id, e.title, e.event_date, e.start_time, e.end_time, e.{$eventsPartColumn} AS part_id, p.shop_name AS part_name
             FROM events e LEFT JOIN parts p ON e.{$eventsPartColumn} = p.{$partsIdColumn}
             WHERE e.user_id = :user_id AND e.event_date >= :start AND e.event_date < :end
             ORDER BY e.event_date, e.start_time IS NULL, e.start_time"
        );
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
    $isPartTime = !empty($input['is_part_time']);
    $partIdInput = $isPartTime ? $input['part_id'] ?? null : null;

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

    $partId = null;
    $partName = null;
    if ($isPartTime) {
        if (!is_numeric($partIdInput) || (int) $partIdInput <= 0) {
            $respond(400, ['error' => 'アルバイトの勤務先を選択してください。']);
            exit;
        }

        $partId = (int) $partIdInput;
        $partStmt = $pdo->prepare("SELECT shop_name FROM parts WHERE user_id = :user_id AND {$partsIdColumn} = :part_id LIMIT 1");
        $partStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $partStmt->bindValue(':part_id', $partId, PDO::PARAM_INT);
        $partStmt->execute();
        $partName = $partStmt->fetchColumn();

        if ($partName === false) {
            $respond(400, ['error' => '選択されたアルバイトが見つかりません。']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO events (user_id, title, event_date, start_time, end_time, {$eventsPartColumn}) VALUES (:user_id, :title, :event_date, :start_time, :end_time, :part_id)"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':event_date', $eventDate, PDO::PARAM_STR);
        $stmt->bindValue(':start_time', $startTime, PDO::PARAM_STR);
        $stmt->bindValue(':end_time', $endTime, PDO::PARAM_STR);
        if ($partId === null) {
            $stmt->bindValue(':part_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':part_id', $partId, PDO::PARAM_INT);
        }
        $stmt->execute();

        $eventId = $pdo->lastInsertId();
        $respond(201, [
            'event_id' => $eventId,
            'title' => $title,
            'event_date' => $eventDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'part_id' => $partId,
            'part_name' => $partName,
        ]);
    } catch (PDOException $e) {
        error_log('POST /api/events: ' . $e->getMessage());
        $respond(500, ['error' => '予定の保存に失敗しました。']);
    }
    exit;
}

if ($method === 'PUT') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (!is_array($input)) {
        $respond(400, ['error' => '不正なリクエスト形式です。']);
        exit;
    }

    $eventId = isset($input['event_id']) ? (int) $input['event_id'] : 0;
    $title = trim($input['title'] ?? '');
    $eventDate = $input['event_date'] ?? '';
    $startTime = trim($input['start_time'] ?? '');
    $endTime = trim($input['end_time'] ?? '');
    $isPartTime = !empty($input['is_part_time']);
    $partIdInput = $isPartTime ? $input['part_id'] ?? null : null;

    if ($eventId <= 0) {
        $respond(400, ['error' => 'イベントIDが不正です。']);
        exit;
    }

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
        $existsStmt = $pdo->prepare('SELECT event_id FROM events WHERE event_id = :event_id AND user_id = :user_id LIMIT 1');
        $existsStmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $existsStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $existsStmt->execute();
        if ($existsStmt->rowCount() === 0) {
            $respond(404, ['error' => '予定が見つかりません。']);
            exit;
        }
    } catch (PDOException $existsException) {
        error_log('CHECK PUT /api/events: ' . $existsException->getMessage());
        $respond(500, ['error' => '予定の確認に失敗しました。']);
        exit;
    }

    $partId = null;
    $partName = null;
    if ($isPartTime) {
        if (!is_numeric($partIdInput) || (int) $partIdInput <= 0) {
            $respond(400, ['error' => 'アルバイトの勤務先を選択してください。']);
            exit;
        }

        $partId = (int) $partIdInput;
        $partStmt = $pdo->prepare("SELECT shop_name FROM parts WHERE user_id = :user_id AND {$partsIdColumn} = :part_id LIMIT 1");
        $partStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $partStmt->bindValue(':part_id', $partId, PDO::PARAM_INT);
        $partStmt->execute();
        $partName = $partStmt->fetchColumn();

        if ($partName === false) {
            $respond(400, ['error' => '選択されたアルバイトが見つかりません。']);
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare(
            "UPDATE events SET title = :title, event_date = :event_date, start_time = :start_time, end_time = :end_time, {$eventsPartColumn} = :part_id WHERE event_id = :event_id AND user_id = :user_id"
        );
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':event_date', $eventDate, PDO::PARAM_STR);
        $stmt->bindValue(':start_time', $startTime, PDO::PARAM_STR);
        $stmt->bindValue(':end_time', $endTime, PDO::PARAM_STR);
        $stmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        if ($partId === null) {
            $stmt->bindValue(':part_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':part_id', $partId, PDO::PARAM_INT);
        }
        $stmt->execute();

        $respond(200, [
            'event_id' => $eventId,
            'title' => $title,
            'event_date' => $eventDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'part_id' => $partId,
            'part_name' => $partName,
        ]);
    } catch (PDOException $e) {
        error_log('PUT /api/events: ' . $e->getMessage());
        $respond(500, ['error' => '予定の更新に失敗しました。']);
    }
    exit;
}

if ($method === 'DELETE') {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    $eventIdParam = $_GET['event_id'] ?? ($input['event_id'] ?? null);
    $eventId = is_numeric($eventIdParam) ? (int) $eventIdParam : 0;

    if ($eventId <= 0) {
        $respond(400, ['error' => '削除する予定が指定されていません。']);
        exit;
    }

    try {
        $stmt = $pdo->prepare('DELETE FROM events WHERE event_id = :event_id AND user_id = :user_id');
        $stmt->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            $respond(404, ['error' => '予定が見つかりません。']);
            exit;
        }

        $respond(200, ['message' => '予定を削除しました。']);
    } catch (PDOException $e) {
        error_log('DELETE /api/events: ' . $e->getMessage());
        $respond(500, ['error' => '予定の削除に失敗しました。']);
    }
    exit;
}

$respond(405, ['error' => '許可されていないメソッドです。']);
