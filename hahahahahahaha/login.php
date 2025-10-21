  <!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ログイン</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container" style="text-align: center;">
    <h1 class="title" >IIKANJIKANRIHYOU</h1><br>
    <h1 class="login-text">ログイン</h1><br>
   
    <form class="login-form">
    <label for="email">メールアドレス</label><br>
      <input type="email" id="email" placeholder="メールアドレスを入力してください"><br><br>
     
      <label for="password">パスワード</label><br>
      <input type="password" id="password" placeholder="パスワードを入力してください"><br><br>
     
      <button type="submit" class="login-button"><form action="home.php" method="post"></form>ログイン</button><br><br>
    </form>
 
    <form action="shinkisakusei.html" method="post"><a href="#" class="signup-link">アカウント新規作成</a></form>
  </div>
</body>
</html>
 
