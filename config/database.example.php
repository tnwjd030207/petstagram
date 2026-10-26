<?php
// ===== 데이터베이스 연결 설정 =====
// 본인의 환경에 맞게 값을 수정하세요.

define('DB_HOST', 'localhost');
define('DB_NAME', 'dogstagram');
define('DB_USER', 'sj');
define('DB_PASS', 'your_password');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die('DB 연결 실패: ' . $e->getMessage());
        }
    }
    return $pdo;
}
