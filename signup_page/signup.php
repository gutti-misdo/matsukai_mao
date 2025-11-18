<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>IIKANJIKANRIHYOU 新規作成</title>
    <link rel="stylesheet" href="./css/signup.css" />
</head>

<body>
    <main class="container">
        <h1 class="title">IIKANJIKANRIHYOU</h1>
        <h2 class="subtitle">新規作成</h2>

        <form class="form" action="welcome.php" method="post">
            <label for="user_name">名前</label>
            <input
                type="text"
                id="user_name"
                name="user_name"
                placeholder="名前を入力してください"
                required />
            <label for="mail">メールアドレス</label>
            <input
                type="email"
                id="mail"
                name="mail"
                placeholder="メールアドレスを入力してください"
                required />
            <label for="pass">パスワード</label>
            <input
                type="password"
                id="pass"
                name="pass"
                placeholder="パスワードを入力してください"
                required />
            <button type="submit" class="signup-button">新規作成</button>
        </form>
        <br>
        <a href="../login-page/login.php" class="link">ログインに戻る</a>
    </main>
</body>

</html>