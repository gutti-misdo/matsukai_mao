<?php
// signup.php — 単一ファイルで「新規作成」画面と登録処理を行うデモ
// そのままローカルの PHP サーバーで動かせます（例: php -S localhost:8000）

session_start();

// CSRF トークン生成
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = false;

// 送信処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF チェック
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $errors[] = '不正なリクエストです。再読み込みしてやり直してください。';
    } else {
        // 値取得＆サニタイズ
        $name     = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
        $emailRaw = trim($_POST['email'] ?? '');
        $email    = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : '';
        $password = $_POST['password'] ?? '';

        // バリデーション
        if ($name === '') {
            $errors[] = '名前を入力してください。';
        } elseif (mb_strlen($name) > 50) {
            $errors[] = '名前は50文字以内で入力してください。';
        }

        if ($email === '') {
            $errors[] = '正しいメールアドレスを入力してください。';
        }

        if ($password === '' || mb_strlen($password) < 6) {
            $errors[] = 'パスワードは6文字以上で入力してください。';
        }

        // 保存（デモ: data/users.json へ追記）
        if (!$errors) {
            $dir = __DIR__ . '/data';
            $file = $dir . '/users.json';
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            $list = [];
            if (file_exists($file)) {
                $json = file_get_contents($file);
                $list = json_decode($json, true) ?: [];
            }

            // 既存メール重複チェック（簡易）
            foreach ($list as $item) {
                if (isset($item['email']) && strtolower($item['email']) === strtolower($email)) {
                    $errors[] = 'このメールアドレスは既に登録されています。';
                    break;
                }
            }

            if (!$errors) {
                $list[] = [
                    'id'       => bin2hex(random_bytes(8)),
                    'name'     => $name,
                    'email'    => $email,
                    // 生パスワードは保存しない
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'created_at' => date('c'),
                ];

                $ok = file_put_contents($file, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                if ($ok === false) {
                    $errors[] = 'ファイルへの保存に失敗しました。権限を確認してください。';
                } else {
                    $success = true;
                    // 一度成功したらフォームの再送対策でトークン更新
                    $_SESSION['token'] = bin2hex(random_bytes(32));
                }
            }
        }
    }
}
?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>IIIKANJIKANRIHYOU | 新規作成</title>
    <style>
        :root {
            --bg: #5dd9f5;
            --panel: #60daf6;
            --accent: #ff6b6b;
            --text: #0f172a;
            --muted: #64748b;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(15, 23, 42, 0.15);
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            margin: 0;
            font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", Meiryo, sans-serif;
            color: var(--text);
            background: linear-gradient(180deg, var(--bg), #7ae5fb 60%, #a6f0ff);
            display: grid; place-items: center;
        }
        .phone-frame {
            width: min(360px, 92vw);
            min-height: 620px;
            background: linear-gradient(180deg, var(--panel), #7ae6fb);
            border-radius: 18px; box-shadow: var(--shadow);
            padding: 28px 18px 24px; position: relative; overflow: hidden;
            border: 2px solid rgba(255,255,255,.65);
        }
        .brand {
            font-weight: 800; font-size: 22px; letter-spacing: 2px; color: var(--accent);
            text-shadow: 0 2px 0 rgba(255,255,255,.55), 0 5px 14px rgba(0,0,0,.15);
            transform: skewX(-8deg); user-select: none;
        }
        h1 { margin: 18px 0 8px; text-align: center; font-size: 26px; letter-spacing: 2px; }

        form { display: grid; gap: 14px; margin-top: 10px; }
        .field { display: grid; gap: 6px; }
        .label { font-size: 13px; font-weight: 700; }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 10px 14px; border: 2px solid rgba(0,0,0,.25);
            border-radius: 999px; background: var(--white); outline: none;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.9), 0 3px 10px rgba(2, 132, 199, 0.08);
            font-size: 14px;
        }
        input::placeholder { color: var(--muted); }
        input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,.25); }

        .submit {
            margin-top: 6px; justify-self: center; padding: 8px 22px; font-weight: 800;
            border: 2px solid rgba(0,0,0,.35); border-radius: 10px;
            background: linear-gradient(#fff,#eaeaea); cursor: pointer;
            transition: transform .04s ease, box-shadow .15s ease;
            box-shadow: 0 2px 0 rgba(0,0,0,.25), 0 8px 16px rgba(0,0,0,.08);
        }
        .submit:hover { filter: brightness(1.03); }
        .submit:active { transform: translateY(1px); box-shadow: 0 1px 0 rgba(0,0,0,.25); }

        .bubbles { position: absolute; inset: auto -40% 0 -40%; height: 45%; pointer-events: none;
            background: radial-gradient(120px 80px at 20% 60%, rgba(255,255,255,.35), transparent 60%),
                                    radial-gradient(160px 100px at 80% 70%, rgba(255,255,255,.28), transparent 60%);
            opacity: .55; }

        .alerts { margin-top: 12px; }
        .error { background: #ffe6e6; border: 1px solid #ff9ca1; color: #9b1c1c; padding: 8px 12px; border-radius: 10px; }
        .ok    { background: #ecfeff; border: 1px solid #6ee7f9; color: #0e7490; padding: 8px 12px; border-radius: 10px; }
    </style>
</head>
<body>
    <main class="phone-frame" role="main">
        <div class="brand" aria-label="アプリ名">IIKANJIKANRIHYOU</div>
        <h1>新規作成</h1>

        <?php if ($errors): ?>
            <div class="alerts">
                <?php foreach ($errors as $e): ?>
                    <div class="error"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endforeach; ?>
            </div>
        <?php elseif ($success): ?>
            <div class="alerts"><div class="ok">登録が完了しました！ログイン処理やリダイレクトは実装先に合わせて追加してください。</div></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="field">
                <label for="name" class="label">名前</label>
                <input id="name" name="name" type="text" placeholder="お名前を入力してください" value="<?= isset($name) ? htmlspecialchars($name, ENT_QUOTES, 'UTF-8') : '' ?>" required>
            </div>

            <div class="field">
                <label for="email" class="label">メールアドレス</label>
                <input id="email" name="email" type="email" placeholder="メールアドレスを入力してください" value="<?= isset($emailRaw) ? htmlspecialchars($emailRaw, ENT_QUOTES, 'UTF-8') : '' ?>" required>
            </div>

            <div class="field">
                <label for="password" class="label">パスワード</label>
                <input id="password" name="password" type="password" placeholder="パスワードを入力してください" minlength="6" required>
            </div>

            <button type="submit" class="submit">新規作成</button>
        </form>

        <div class="bubbles" aria-hidden="true"></div>
    </main>
</body>
</html>