<?php
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
            <form action="#" method="post">
                <div>
                    <label for="workplace">勤務先</label>
                    <input type="text" id="workplace" name="workplace" placeholder="勤務先を入力" required />
                </div>
                <div>
                    <label for="wage">時給</label>
                    <input type="number" id="wage" name="wage" placeholder="例: 1200" min="0" step="50" required />
                </div>
                <div>
                    <label for="transport">交通費</label>
                    <input type="number" id="transport" name="transport" placeholder="1日あたりの交通費" min="0" step="10" />
                </div>
                <button class="submit-button" type="submit">登録</button>
            </form>
        </div>
    </div>
</body>
</html>