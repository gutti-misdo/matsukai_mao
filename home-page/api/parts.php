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

if ($method !== 'GET') {
    $respond(405, ['error' => '許可されていないメソッドです。']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT part_id, shop_name, hourly_wage, travel_expenses FROM parts WHERE user_id = :user_id ORDER BY shop_name ASC');
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $parts = $stmt->fetchAll();
    $respond(200, ['parts' => $parts]);
} catch (PDOException $e) {
    error_log('GET /api/parts: ' . $e->getMessage());
    $respond(500, ['error' => 'アルバイト情報の取得に失敗しました。']);
}
