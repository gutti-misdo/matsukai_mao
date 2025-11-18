<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-page/login.php');
    exit;
}

$dsn = 'mysql:host=localhost;dbname=matsukai;charset=utf8mb4';
$dbUser = 'root';
$dbPassword = '';

$workplace = '';
$wage = '';
$transport = '';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workplace = trim($_POST['workplace'] ?? '');
    $wageInput = trim($_POST['wage'] ?? '');
    $transportInput = trim($_POST['transport'] ?? '');
    $wage = $wageInput;
    $transport = $transportInput;

    if ($workplace === '' || $wageInput === '') {
        $errorMessage = '勤務先と時給を入力してください。';
    } else {
        $wageValue = filter_var($wageInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $transportValue = null;

        if ($wageValue === false) {
            $errorMessage = '時給は0以上の数値で入力してください。';
        } elseif ($transportInput !== '') {
            $transportValue = filter_var($transportInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($transportValue === false) {
                $errorMessage = '交通費は0以上の数値で入力してください。';
            }
        }
    }

    if ($errorMessage === '') {
        try {
            $pdo = new PDO($dsn, $dbUser, $dbPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $stmt = $pdo->prepare('INSERT INTO parts (user_id, workplace, wage, transport) VALUES (:user_id, :workplace, :wage, :transport)');
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':workplace', $workplace, PDO::PARAM_STR);
            $stmt->bindValue(':wage', $wageValue, PDO::PARAM_INT);

            if ($transportValue === null) {
                $stmt->bindValue(':transport', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':transport', $transportValue, PDO::PARAM_INT);
            }

            $stmt->execute();

            $successMessage = 'アルバイト情報を登録しました。';
            $workplace = '';
            $wage = '';
            $transport = '';
        } catch (PDOException $e) {
            http_response_code(500);
            $errorMessage = '登録に失敗しました。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>アルバイト登録 | IIIKANJIKANRIHYOU</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./css/parts-add.css" />
</head>
<body>
    <div class="screen">
        <a class="back-link" href="./home.php">ホームに戻る</a>
        <div class="brand">IIIKANJIKANRIHYOU</div>
        <div class="form-card">
            <h1>アルバイト登録</h1>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div>
                    <label for="workplace">勤務先</label>
                    <input type="text" id="workplace" name="workplace" placeholder="勤務先を入力" value="<?php echo htmlspecialchars($workplace, ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>
                <div>
                    <label for="wage">時給</label>
                    <input type="number" id="wage" name="wage" placeholder="例: 1200" min="0" step="50" value="<?php echo htmlspecialchars($wage, ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>
                <div>
                    <label for="transport">交通費</label>
                    <input type="number" id="transport" name="transport" placeholder="1日あたりの交通費" min="0" step="10" value="<?php echo htmlspecialchars($transport, ENT_QUOTES, 'UTF-8'); ?>" />
                </div>
                <button class="submit-button" type="submit">登録</button>
                <?php if ($successMessage !== '') : ?>
                    <p class="feedback feedback--success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if ($errorMessage !== '') : ?>
                    <p class="feedback feedback--error"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>