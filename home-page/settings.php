<?php
session_start();

if (isset($_POST['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ../login-page/login.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-page/login.php');
    exit;
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
    <link rel="stylesheet" href="./css/settings.css" />
</head>

<body>
    <div class="settings-page">
        <header class="settings-page__header">
            <a class="settings-page__back" href="./home.php">ホームに戻る</a>
            <p class="settings-page__title">IIKANJIKANRIHYOU</p>
        </header>

        <main class="settings-page__main" role="main">
            <div class="settings-menu">
                <button class="settings-menu__item" type="button">アカウント情報</button>
                <button class="settings-menu__item" type="button">アルバイト新規登録</button>
                <button class="settings-menu__item" type="button">アルバイト一覧</button>
                <form method="post" class="settings-menu__logout">
                    <button class="settings-menu__item settings-menu__item--danger" type="submit" name="logout"
                        value="1">ログアウト</button>
                </form>
            </div>
        </main>
    </div>
</body>

</html>