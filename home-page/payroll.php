<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-page/login.php');
    exit;
}

$currentMonth = date('Y-m');
$cssPath = __DIR__ . '/css/app.css';
$jsPath = __DIR__ . '/js/payroll.js';
$cssVersion = is_file($cssPath) ? filemtime($cssPath) : time();
$jsVersion = is_file($jsPath) ? filemtime($jsPath) : time();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>給与計算 | IIIKANJIKANRIHYOU</title>
    <link rel="stylesheet" href="./css/app.css?v=<?php echo $cssVersion; ?>" />
</head>

<body>
    <div class="payroll">
        <header class="payroll__header">
            <a class="payroll__back" href="./home.php">ホームに戻る</a>
            <h1 class="payroll__title">給与計算</h1>
        </header>

        <main>
            <section class="payroll__controls" aria-label="給与計算の条件">
                <label for="payrollMonth">集計する月</label>
                <input type="month" id="payrollMonth" value="<?php echo htmlspecialchars($currentMonth, ENT_QUOTES, 'UTF-8'); ?>" />
                <p class="payroll__message payroll__message--info" id="payrollInfo">月ごとにアルバイトの勤務時間と給与を確認できます。</p>
            </section>

            <div id="payrollMessage" class="payroll__message" role="status" aria-live="polite" style="display:none;"></div>
            <div id="payrollList" class="payroll__list" aria-live="polite"></div>
        </main>
    </div>

    <script src="./js/payroll.js?v=<?php echo $jsVersion; ?>"></script>
</body>

</html>
