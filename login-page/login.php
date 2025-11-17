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

        <form class="form" action="home.php" method="post">
            <label for="email">メールアドレス</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="メールアドレスを入力してください"
                required />

            <label for="password">パスワード</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="パスワードを入力してください"
                required />

            <button type="submit" class="login-button">ログイン</button>
        </form>

        <a href="#signup" class="link">アカウント新規作成</a>
    </main>
</body>

</html>