<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-page/login.php');
    exit;
}

$userName = $_SESSION['user_name'] ?? 'ユーザー';
$today = date('Y-m-d');
$cssPath = __DIR__ . '/css/app.css';
$homeJsPath = __DIR__ . '/js/home.js';
$cssVersion = is_file($cssPath) ? filemtime($cssPath) : time();
$homeJsVersion = is_file($homeJsPath) ? filemtime($homeJsPath) : time();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>IIKANJIKANRIHYOU ホーム</title>
    <link rel="stylesheet" href="./css/app.css?v=<?php echo $cssVersion; ?>" />
</head>

<body>
    <div class="app-shell">
        <header class="header">
            <div class="header__title">
                <span class="header__title-main">IIKANJIKANRIHYOU</span>
                <span class="header__title-sub">iikanjikanrihyou</span>
                <span class="header__welcome">ようこそ、<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>さん</span>
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
                <p class="planner__note">入力した予定はログイン中のアカウントに紐づいて保存され、再読み込みしてもカレンダーに反映されます。</p>

                <div class="planner__selected" id="selectedDatePanel">
                    <div class="planner__selected-header">
                        <div class="planner__selected-label">選択中の日付</div>
                        <div class="planner__selected-date" id="selectedDateDisplay"></div>
                    </div>
                    <div class="planner__selected-events" id="selectedDateEvents" aria-live="polite"></div>
                </div>

                <div id="eventMessage" class="planner__message" role="status" aria-live="polite"></div>
                <form id="eventForm" class="planner__form">
                    <input type="hidden" id="eventId" name="event_id" value="" />
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

                    <div class="planner__time-row">
                        <div class="planner__time-field">
                            <label class="planner__label" for="startTime">開始時間</label>
                            <input
                                type="time"
                                id="startTime"
                                name="start_time"
                                class="planner__input"
                                required
                            />
                        </div>
                        <div class="planner__time-field">
                            <label class="planner__label" for="endTime">終了時間</label>
                            <input
                                type="time"
                                id="endTime"
                                name="end_time"
                                class="planner__input"
                                required
                            />
                        </div>
                    </div>

                    <div class="planner__parttime">
                        <label class="planner__checkbox">
                            <input type="checkbox" id="isPartTime" name="is_part_time" />
                            この予定はアルバイト
                        </label>

                        <label class="planner__label" for="partSelect">勤務先</label>
                        <select id="partSelect" name="part_id" class="planner__input" disabled>
                            <option value="">勤務先を選択してください</option>
                        </select>
                        <p class="planner__hint" id="partSelectHint">アルバイトを登録すると選択できます。</p>
                    </div>

                    <div class="planner__actions">
                        <button type="submit" class="planner__submit" id="addEventButton">予定を追加</button>
                        <button type="submit" class="planner__submit planner__submit--secondary" id="updateEventButton" hidden>
                            予定を変更
                        </button>
                        <button type="button" class="planner__delete" id="deleteEventButton" hidden>予定を削除</button>
                        <button type="button" class="planner__secondary" id="cancelEditButton" hidden>編集をやめる</button>
                    </div>
                </form>
            </section>
        </main>

        <nav class="bottom-nav" aria-label="アクション">
            <a class="bottom-nav__item" href="./payroll.php">
                <span class="bottom-nav__label">給与計算</span>
            </a>
            <button class="bottom-nav__item bottom-nav__item--primary" id="openAddForm" aria-label="予定を追加">
                <span class="bottom-nav__plus">＋</span>
                <span class="bottom-nav__label">追加</span>
            </button>
            <button class="bottom-nav__item" id="scrollToCalendarTop" aria-label="カレンダーへ移動">
                <span class="bottom-nav__label">カレンダー</span>
            </button>
        </nav>
    </div>

    <script src="./js/home.js?v=<?php echo $homeJsVersion; ?>"></script>
</body>

</html>
