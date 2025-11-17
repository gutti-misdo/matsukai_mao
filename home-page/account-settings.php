<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-page/login.php');
    exit;
}

$dsn = 'mysql:host=localhost;dbname=matsukai;charset=utf8mb4';
$dbUser = 'root';
$dbPassword = '';

$userId = $_SESSION['user_id'];
$name = '';
$email = '';
$successMessage = '';
$errorMessage = '';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['user_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($name === '' || $email === '') {
            $errorMessage = '名前とメールアドレスを入力してください。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'メールアドレスの形式が正しくありません。';
        } else {
            if ($password !== '') {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateSql = 'UPDATE user SET user_name = :name, mail = :mail, pass = :pass WHERE user_id = :id';
                $stmt = $pdo->prepare($updateSql);
                $stmt->bindValue(':pass', $hashedPassword, PDO::PARAM_STR);
            } else {
                $updateSql = 'UPDATE user SET user_name = :name, mail = :mail WHERE user_id = :id';
                $stmt = $pdo->prepare($updateSql);
            }

            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':mail', $email, PDO::PARAM_STR);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($password !== '') {
                $successMessage = 'アカウント情報とパスワードを更新しました。';
            } else {
                $successMessage = 'アカウント情報を更新しました。';
            }

            $_SESSION['user_name'] = $name;
        }
    }

    if ($name === '' || $email === '') {
        $stmt = $pdo->prepare('SELECT user_name, mail FROM user WHERE user_id = :id LIMIT 1');
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            $name = $user['user_name'];
            $email = $user['mail'];
        } else {
            $errorMessage = 'ユーザー情報を取得できませんでした。';
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    $errorMessage = 'データベース接続に失敗しました。';
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>IIKANJIKANRIHYOU 設定</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="./css/account-settings.css" />
</head>

<body>
    <div class="settings-page">
        <header class="settings-header">
            <a class="settings-header__back" href="./settings.php">設定に戻る</a>
            <p class="settings-header__logo">IIKANJIKANRIHYOU</p>
        </header>

        <main class="settings-main" role="main">
            <section class="account-panel">
                <h1 class="account-panel__title">アカウント情報</h1>

                <form class="account-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <label class="account-form__label" for="user_name">名前</label>
                    <input
                        class="account-form__input"
                        type="text"
                        id="user_name"
                        name="user_name"
                        value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                        required />

                    <label class="account-form__label" for="email">メールアドレス</label>
                    <input
                        class="account-form__input"
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                        required />

                    <label class="account-form__label" for="password">パスワード変更</label>
                    <input
                        class="account-form__input"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="新規パスワードを入力してください" />

                    <button class="account-form__submit" type="submit">保存する</button>

                    <?php if ($successMessage !== '') : ?>
                        <p class="account-form__feedback account-form__feedback--success">
                            <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($errorMessage !== '') : ?>
                        <p class="account-form__feedback account-form__feedback--error">
                            <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    <?php endif; ?>
                </form>
            </section>
        </main>
    </div>
</body>

</html>