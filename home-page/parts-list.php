<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-page/login.php');
    exit;
}

$dsn = 'mysql:host=localhost;dbname=matsukai;charset=utf8mb4';
$dbUser = 'root';
$dbPassword = '';

$parts = [];
$errorMessage = '';

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare('SELECT shop_name, hourly_wage, travel_expenses FROM parts WHERE user_id = :user_id ORDER BY shop_name ASC');
    $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $parts = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    $errorMessage = 'アルバイト情報の取得に失敗しました。';
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>アルバイト一覧 | IIIKANJIKANRIHYOU</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="./css/parts-list.css" />
</head>

<body>
    <div class="list-page">
        <header class="list-page__header">
            <a class="list-page__back" href="./home.php">ホームに戻る</a>
            <p class="list-page__brand">IIKANJIKANRIHYOU</p>
        </header>

        <main class="list-card" role="main">
            <h1 class="list-card__title">アルバイト一覧</h1>

            <?php if ($errorMessage !== '') : ?>
                <p class="list-card__message list-card__message--error">
                    <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php elseif (empty($parts)) : ?>
                <p class="list-card__message">登録されているアルバイトはありません。</p>
            <?php else : ?>
                <ul class="parts-list">
                    <?php foreach ($parts as $part) : ?>
                        <li class="parts-list__item">
                            <span class="parts-list__name">
                                <?php echo htmlspecialchars($part['shop_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <span class="parts-list__meta">
                                時給: <?php echo htmlspecialchars(number_format((int) $part['hourly_wage']), ENT_QUOTES, 'UTF-8'); ?>円
                                <?php if ($part['travel_expenses'] !== null) : ?>
                                    ／ 交通費: <?php echo htmlspecialchars(number_format((int) $part['travel_expenses']), ENT_QUOTES, 'UTF-8'); ?>円
                                <?php else : ?>
                                    ／ 交通費: なし
                                <?php endif; ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="list-card__cta" href="./parts-add.php">アルバイト新規登録</a>
        </main>
    </div>
</body>

</html>