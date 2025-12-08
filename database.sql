-- Database schema for IIKANJIKANRIHYOU calendar app
CREATE DATABASE IF NOT EXISTS matsukai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE matsukai;

CREATE TABLE IF NOT EXISTS user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    mail VARCHAR(255) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS parts (
    part_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    shop_name VARCHAR(255) NOT NULL,
    hourly_wage INT NOT NULL,
    travel_expenses INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_shop (user_id, shop_name),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    part_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_event_date (user_id, title, event_date),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (part_id) REFERENCES parts(part_id) ON DELETE SET NULL,
    INDEX idx_user_date (user_id, event_date)
);

-- サンプルユーザーと予定を投入して動作確認ができるようにする
INSERT INTO user (user_name, mail, pass)
VALUES ('デモユーザー', 'demo@example.com', '$2y$12$MmElHXstly/J6XTo7U.tc.U2MYnU6kdHgt/KaZ8p8jK/kwfL/6WYq')
ON DUPLICATE KEY UPDATE user_id = LAST_INSERT_ID(user_id);

SET @demo_user_id = LAST_INSERT_ID();

INSERT INTO parts (user_id, shop_name, hourly_wage, travel_expenses)
VALUES
    (@demo_user_id, 'セブン', 1200, 520),
    (@demo_user_id, 'ファミマ', 1100, 680)
ON DUPLICATE KEY UPDATE part_id = part_id;

SET @seven_part_id = (SELECT part_id FROM parts WHERE user_id = @demo_user_id AND shop_name = 'セブン' LIMIT 1);

INSERT INTO events (user_id, title, event_date, start_time, end_time)
VALUES
    (@demo_user_id, 'ミーティング', DATE_FORMAT(NOW(), '%Y-%m-15'), '10:00:00', '11:00:00'),
    (@demo_user_id, '請求書締め切り', DATE_FORMAT(NOW(), '%Y-%m-20'), '09:00:00', '09:30:00'),
    (@demo_user_id, '友人と食事', DATE_FORMAT(NOW(), '%Y-%m-25'), '19:00:00', '21:00:00')
ON DUPLICATE KEY UPDATE event_id = event_id;

INSERT INTO events (user_id, title, event_date, start_time, end_time, part_id)
VALUES
    (@demo_user_id, 'アルバイト（セブン）', DATE_FORMAT(NOW(), '%Y-%m-08'), '18:00:00', '22:00:00', @seven_part_id)
ON DUPLICATE KEY UPDATE event_id = event_id;
