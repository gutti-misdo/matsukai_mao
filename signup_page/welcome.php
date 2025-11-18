<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit;
}

$name = trim($_POST['user_name'] ?? '');
$mail = trim($_POST['mail'] ?? '');
$password = $_POST['pass'] ?? '';
if ($name === '' || $mail === '' || $password === '') {
    $error = 'すべての項目を入力してください。';
} elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    $error = 'メールアドレスの形式が正しくありません。';
}

require_once __DIR__ . '/../db-connect.php';

if (!isset($error)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare('INSERT INTO user (user_name, mail, pass) VALUES (:name, :mail, :password)');
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->execute();
        $success = true;
    } catch (PDOException $e) {
        $error = '登録に失敗しました: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>新規作成 完了</title>
    <link rel="stylesheet" href="./css/signup.css" />
</head>
<body>
    <main class="container">
        <h1 class="title">IIKANJIKANRIHYOU</h1>
        <h2 class="subtitle">新規作成</h2>
        <?php if (isset($success) && $success): ?>
            <p>アカウントの作成が完了しました。</p>
            <a href="../login-page/login.php" class="link">ログインページへ</a>
        <?php else: ?>
            <p><?php echo htmlspecialchars($error ?? '不明なエラーが発生しました。', ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="signup.php" class="link">戻る</a>
        <?php endif; ?>
    </main>
</body>
</html>