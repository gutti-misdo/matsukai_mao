<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-page/login.php');
    exit;
}

require_once __DIR__ . '/../db-connect.php';

$shopName = '';
$hourlyWage = '';
$travelExpenses = '';
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shopName = trim($_POST['shop_name'] ?? '');
    $hourlyWageInput = trim($_POST['hourly_wage'] ?? '');
    $travelExpensesInput = trim($_POST['travel_expenses'] ?? '');
    $hourlyWage = $hourlyWageInput;
    $travelExpenses = $travelExpensesInput;

    if ($shopName === '' || $hourlyWageInput === '') {
        $errorMessage = '勤務先と時給を入力してください。';
    } else {
        $hourlyWageValue = filter_var($hourlyWageInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        $travelExpensesValue = null;
        if ($hourlyWageValue === false) {
            $errorMessage = '時給は0以上の数値で入力してください。';
        } elseif ($travelExpensesInput !== '') {
            $travelExpensesValue = filter_var($travelExpensesInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            if ($travelExpensesValue === false) {
                $errorMessage = '交通費は0以上の数値で入力してください。';
            }
        }
    }
    if ($errorMessage === '') {
try {
            $stmt = $pdo->prepare('INSERT INTO parts (user_id, shop_name, hourly_wage, travel_expenses) VALUES (:user_id, :shop_name, :hourly_wage, :travel_expenses)');
            $stmt->bindValue(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->bindValue(':shop_name', $shopName, PDO::PARAM_STR);
            $stmt->bindValue(':hourly_wage', $hourlyWageValue, PDO::PARAM_INT);
            if ($travelExpensesValue === null) {
                $stmt->bindValue(':travel_expenses', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':travel_expenses', $travelExpensesValue, PDO::PARAM_INT);
            }
            $stmt->execute();
            $successMessage = 'アルバイト情報を登録しました。';
            $shopName = '';
            $hourlyWage = '';
            $travelExpenses = '';
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
                    <label for="shop_name">勤務先</label>
                    <input type="text" id="shop_name" name="shop_name" placeholder="勤務先を入力" value="<?php echo htmlspecialchars($shopName, ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>
                <div>
                    <label for="hourly_wage">時給</label>
                    <input type="number" id="hourly_wage" name="hourly_wage" placeholder="例: 1200" min="0" step="50" value="<?php echo htmlspecialchars($hourlyWage, ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>
                <div>
                    <label for="travel_expenses">交通費</label>
                    <input type="number" id="travel_expenses" name="travel_expenses" placeholder="1日あたりの交通費" min="0" step="10" value="<?php echo htmlspecialchars($travelExpenses, ENT_QUOTES, 'UTF-8'); ?>" />
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