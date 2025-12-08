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

CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    event_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_event_date (user_id, title, event_date),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, event_date)
);

-- サンプルユーザーと予定を投入して動作確認ができるようにする
INSERT INTO user (user_name, mail, pass)
VALUES ('デモユーザー', 'demo@example.com', '$2y$12$MmElHXstly/J6XTo7U.tc.U2MYnU6kdHgt/KaZ8p8jK/kwfL/6WYq')
ON DUPLICATE KEY UPDATE user_id = LAST_INSERT_ID(user_id);

SET @demo_user_id = LAST_INSERT_ID();

INSERT INTO events (user_id, title, event_date)
VALUES
    (@demo_user_id, 'ミーティング', DATE_FORMAT(NOW(), '%Y-%m-15')),
    (@demo_user_id, '請求書締め切り', DATE_FORMAT(NOW(), '%Y-%m-20')),
    (@demo_user_id, '友人と食事', DATE_FORMAT(NOW(), '%Y-%m-25'))
ON DUPLICATE KEY UPDATE event_id = event_id;
