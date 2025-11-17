<?php
session_start();

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
                <button class="settings-menu__item settings-menu__item--danger" type="button">ログアウト</button>
            </div>
        </main>
    </div>
</body>

</html>