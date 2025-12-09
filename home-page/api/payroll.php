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
$userId = (int) $_SESSION['user_id'];

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
    error_log('COLUMN CHECK /api/payroll: ' . $columnException->getMessage());
}

if ($method !== 'GET') {
    $respond(405, ['error' => '許可されていないメソッドです。']);
    exit;
}

$month = $_GET['month'] ?? '';
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $respond(400, ['error' => 'month は YYYY-MM 形式で指定してください。']);
    exit;
}

$startDate = $month . '-01';
$endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));

try {
    $stmt = $pdo->prepare(
        'SELECT
            p.' . $partsIdColumn . ' AS part_id,
            p.shop_name,
            p.hourly_wage,
            p.travel_expenses,
            COUNT(e.event_id) AS shift_count,
            SUM(CASE
                WHEN e.start_time IS NOT NULL AND e.end_time IS NOT NULL
                    THEN TIMESTAMPDIFF(MINUTE, CONCAT(e.event_date, " ", e.start_time), CONCAT(e.event_date, " ", e.end_time))
                ELSE 0
            END) AS total_minutes
        FROM events e
        INNER JOIN parts p ON e.' . $eventsPartColumn . ' = p.' . $partsIdColumn . '
        WHERE e.user_id = :user_id AND e.event_date >= :start AND e.event_date < :end
        GROUP BY p.' . $partsIdColumn . ', p.shop_name, p.hourly_wage, p.travel_expenses
        ORDER BY p.shop_name'
    );
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':start', $startDate, PDO::PARAM_STR);
    $stmt->bindValue(':end', $endDate, PDO::PARAM_STR);
    $stmt->execute();
    $rows = $stmt->fetchAll();

    $summaries = array_map(function ($row) {
        $totalMinutes = (int) ($row['total_minutes'] ?? 0);
        $totalHours = $totalMinutes / 60;
        $hourlyWage = (int) $row['hourly_wage'];
        $shiftCount = (int) $row['shift_count'];
        $travelExpenses = $row['travel_expenses'] !== null ? (int) $row['travel_expenses'] : null;

        return [
            'part_id' => (int) $row['part_id'],
            'shop_name' => $row['shop_name'],
            'hourly_wage' => $hourlyWage,
            'shift_count' => $shiftCount,
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalHours, 2),
            'total_pay' => (int) floor($totalMinutes * $hourlyWage / 60),
            'travel_expenses' => $travelExpenses,
            'travel_total' => $travelExpenses !== null ? $shiftCount * $travelExpenses : null,
        ];
    }, $rows);

    $respond(200, [
        'month' => $month,
        'summaries' => $summaries,
    ]);
} catch (PDOException $e) {
    error_log('GET /api/payroll: ' . $e->getMessage());
    $respond(500, ['error' => '給与計算の取得に失敗しました。']);
}
