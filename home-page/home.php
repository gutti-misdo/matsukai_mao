<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>IIKANJIKANRIHYOU ホーム</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="./css/home.css" />
</head>

<body>
    <div class="app-shell">
        <header class="header">
            <div class="header__title">
                <span class="header__title-main">IIKANJIKANRIHYOU</span>
                <span class="header__title-sub">iikanjikanrihyou</span>
            </div>
            <button class="settings-button" aria-label="設定">
                <span>設定</span>
            </button>
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
        </main>

        <nav class="bottom-nav" aria-label="アクション">
            <button class="bottom-nav__item">
                <span class="bottom-nav__label">給与計算</span>
            </button>
            <button class="bottom-nav__item bottom-nav__item--primary" aria-label="予定を追加">
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