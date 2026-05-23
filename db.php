<?php
// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "movie_booking";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Auto-create missing columns if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'ngay_chieu'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN ngay_chieu DATE");
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'gio_chieu'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN gio_chieu TIME");
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_phone VARCHAR(20) NOT NULL,
        loai_hoat_dong VARCHAR(50) NOT NULL,
        noi_dung TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_phone (user_phone),
        INDEX idx_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $activityCount = (int)$pdo->query("SELECT COUNT(*) FROM user_activities")->fetchColumn();
    if ($activityCount === 0) {
        $pdo->exec("INSERT INTO user_activities (user_phone, loai_hoat_dong, noi_dung, created_at)
            SELECT user_phone, 'dat_ve',
                CONCAT('Đặt vé phim \"', ten_phim_dat, '\" - Ghế: ', so_ghe,
                    ' - Suất: ', DATE_FORMAT(ngay_chieu, '%d/%m/%Y'), ' ', TIME_FORMAT(gio_chieu, '%H:%i'),
                    ' - ', FORMAT(gia_ve, 0), ' VND'),
                ngay_dat_ve
            FROM bookings
            WHERE ngay_chieu IS NOT NULL AND gio_chieu IS NOT NULL");
    }
} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()]));
}