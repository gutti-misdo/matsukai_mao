<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-page/login.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? 'ユーザー';
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>IIKANJIKANRIHYOU ホーム</title>
    <link rel="stylesheet" href="./css/app.css" />
</head>

<body>
    <div class="app-shell">
        <header class="header">
            <div class="header__title">
                <span class="header__title-main">IIKANJIKANRIHYOU</span>
                <span class="header__title-sub">iikanjikanrihyou</span>
            </div>
            <div class="header__user">
                ようこそ、<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>さん
            </div>
            <a class="settings-button" href="./settings.php" aria-label="設定ページへ">
                <span>設定</span>
            </a>
        </header>


        <main class="main">
            <section class="calendar">
                <div class="calendar__toolbar">
                    <button class="calendar__nav" data-direction="prev" aria-label="前の月">
                        <span aria-hidden="true">&#9664;</span>
                    </button>
                    <div class="calendar__current">
                        <span class="calendar__year" id="calendarYear">2026</span>年
                        <span class="calendar__month" id="calendarMonth">1</span>月
                    </div>
                    <button class="calendar__nav" data-direction="next" aria-label="次の月">
                        <span aria-hidden="true">&#9654;</span>
                    </button>
                    <button class="calendar__today" id="goToday">今日</button>
                </div>

                <div class="calendar__weekdays">
                    <span>日</span>
                    <span>月</span>
                    <span>火</span>
                    <span>水</span>
                    <span>木</span>
                    <span>金</span>
                    <span>土</span>
                </div>

                <div class="calendar__grid" id="calendarGrid" aria-live="polite"></div>
            </section>

            <section class="planner" aria-label="予定の追加">
                <h2 class="planner__title">あなたの予定</h2>
                <p class="planner__description">カレンダーで日付を選択して、予定を追加・確認できます。</p>

                <div class="planner__selected" id="selectedDatePanel">
                    <div class="planner__selected-header">
                        <div class="planner__selected-label">選択中の日付</div>
                        <div class="planner__selected-date" id="selectedDateDisplay"></div>
                    </div>
                    <div class="planner__selected-events" id="selectedDateEvents" aria-live="polite"></div>
                </div>

                <div id="eventMessage" class="planner__message" role="status" aria-live="polite"></div>
                <form id="eventForm" class="planner__form">
                    <label class="planner__label" for="eventTitle">タイトル</label>
                    <input
                        type="text"
                        id="eventTitle"
                        name="title"
                        class="planner__input"
                        placeholder="例：10:00 ミーティング"
                        required
                    />

                    <label class="planner__label" for="eventDate">日付</label>
                    <input
                        type="date"
                        id="eventDate"
                        name="event_date"
                        class="planner__input"
                        value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"
                        required
                    />

                    <button type="submit" class="planner__submit">予定を追加</button>
                </form>
            </section>
        </main>

        <nav class="bottom-nav" aria-label="アクション">
            <button class="bottom-nav__item">
                <span class="bottom-nav__label">給与計算</span>
            </button>
            <button class="bottom-nav__item bottom-nav__item--primary" id="openAddForm" aria-label="予定を追加">
                <span class="bottom-nav__plus">＋</span>
                <span class="bottom-nav__label">追加</span>
            </button>
            <button class="bottom-nav__item">
                <span class="bottom-nav__label">予定変更</span>
            </button>
        </nav>
    </div>

    <script src="./js/home.js"></script>
</body>

</html>
