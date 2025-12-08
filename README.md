# IIKANJIKANRIHYOU

シンプルなカレンダー管理アプリです。ユーザーごとにログインし、自分専用の予定を登録・閲覧できます。

## セットアップ
1. MySQL でデータベースとテーブルを作成します。
   ```sql
   SOURCE database.sql;
   ```
   デフォルトでは `matsukai` データベース、ユーザー表 `user` と予定表 `events` が作成されます。
2. `db-connect.php` の接続設定を環境に合わせて変更します。
3. PHP が動作する環境で `signup_page/signup.php` からアカウントを作成し、`login-page/login.php` からログインしてください。
4. ログイン後の `home-page/home.php` から予定の追加・確認ができます。

## API
- `home-page/api/events.php`
  - `GET ?month=YYYY-MM`: ログイン中のユーザーの指定月の予定を返します。
  - `POST {"title": "タイトル", "event_date": "YYYY-MM-DD"}`: 新しい予定を追加します。
