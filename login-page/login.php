<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../home-page/home.php');
    exit;
}

$mail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = trim($_POST['mail'] ?? '');
    $password = $_POST['pass'] ?? '';

    if ($mail === '' || $password === '') {
        $error = 'メールアドレスとパスワードを入力してください。';
    } elseif (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
        $error = 'メールアドレスの形式が正しくありません。';
    } else {
        $dsn = 'mysql:host=localhost;dbname=matsukai;charset=utf8mb4';
        $dbUser = 'root';
        $dbPassword = '';

        try {
            $pdo = new PDO($dsn, $dbUser, $dbPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $stmt = $pdo->prepare('SELECT user_id, user_name, pass FROM user WHERE mail = :mail LIMIT 1');
            $stmt->bindValue(':mail', $mail, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['pass'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                header('Location: ../home-page/home.php');
                exit;
            } else {
                $error = 'メールアドレスまたはパスワードが正しくありません。';
            }
        } catch (PDOException $e) {
            http_response_code(500);
            $error = 'データベース接続に失敗しました。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>IIKANJIKANRIHYOU ログイン</title>
    <link rel="stylesheet" href="./css/login.css" />
</head>

<body>
    <main class="container">
        <h1 class="title">IIKANJIKANRIHYOU</h1>
        <h2 class="subtitle">ログイン</h2>

        <?php if (isset($error)) : ?>
            <p class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form class="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <label for="mail">メールアドレス</label>
            <input␊
                type="email"␊
                id="mail"
                name="mail"
                placeholder="メールアドレスを入力してください"␊
                value="<?php echo htmlspecialchars($mail ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                required />␊
␊
            <label for="pass">パスワード</label>
            <input␊
                type="password"␊
                id="pass"
                name="pass"
                placeholder="パスワードを入力してください"␊
                required />

            <button type="submit" class="login-button">ログイン</button>
        </form>

        <a href="../signup_page/signup.php" class="link">アカウント新規作成</a>
    </main>
</body>

</html>