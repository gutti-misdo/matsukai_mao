<?php
session_start();


function redirect_with($params) {
$base = 'signup.html';
$q = http_build_query($params);
header('Location: ' . $base . ($q ? ('?' . $q) : ''));
exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
redirect_with(['error' => rawurlencode('不正なアクセスです。')]);
}


// CSRF チェック
if (!isset($_POST['token']) || empty($_SESSION['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
redirect_with(['error' => rawurlencode('不正なリクエスト（CSRF）です。再度お試しください。')]);
}


$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
$emailRaw = trim($_POST['email'] ?? '');
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : '';
$password = $_POST['password'] ?? '';


$errors = [];
if ($name === '') { $errors[] = '名前を入力してください。'; }
if ($email === '') { $errors[] = '正しいメールアドレスを入力してください。'; }
if ($password === '' || mb_strlen($password) < 6) { $errors[] = 'パスワードは6文字以上で入力してください。'; }


if ($errors) {
redirect_with(['error' => rawurlencode(implode(' / ', $errors))]);
}


$dir = __DIR__ . '/data';
$file = $dir . '/users.json';
if (!is_dir($dir)) { mkdir($dir, 0777, true); }


$list = [];
if (file_exists($file)) {
$json = file_get_contents($file);
$list = json_decode($json, true) ?: [];
}


// 重複メールチェック
foreach ($list as $item) {
if (isset($item['email']) && strtolower($item['email']) === strtolower($email)) {
redirect_with(['error' => rawurlencode('このメールアドレスは既に登録されています。')]);
}
}


$list[] = [
'id' => bin2hex(random_bytes(8)),
'name' => $name,
'email' => $email,
'password' => password_hash($password, PASSWORD_DEFAULT),
'created_at' => date('c'),
];


if (file_put_contents($file, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
redirect_with(['error' => rawurlencode('保存に失敗しました。権限を確認してください。')]);
}


// 成功
// フォーム再送信対策：トークン更新
$_SESSION['token'] = bin2hex(random_bytes(32));
redirect_with(['ok' => 1]);
?>