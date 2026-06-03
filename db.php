<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "movie_booking";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'ngay_chieu'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN ngay_chieu DATE");
    }
    
    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'gio_chieu'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN gio_chieu TIME");
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM bookings LIKE 'so_lan_sua'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE bookings ADD COLUMN so_lan_sua INT DEFAULT 0 COMMENT 'Số lần user đã chỉnh sửa vé'");
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS movies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ten_phim VARCHAR(255) NOT NULL,
        mo_ta TEXT,
        the_loai VARCHAR(100),
        dao_dien VARCHAR(100),
        thoi_luong INT COMMENT 'Phút',
        hinh_anh_url VARCHAR(500),
        ngay_khoi_chieu DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_ten_phim (ten_phim)
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

    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'vai_tro'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN vai_tro VARCHAR(20) NOT NULL DEFAULT 'user'");
    }
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'tai_khoan_bi_khoa'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN tai_khoan_bi_khoa TINYINT(1) NOT NULL DEFAULT 0");
    }

    $adminEmail = 'admin1@admin.com';
    $adminPhone = '0999999001';
    $chk = $pdo->prepare("SELECT dien_thoai FROM users WHERE email = ? LIMIT 1");
    $chk->execute([$adminEmail]);
    if (!$chk->fetch()) {
        $hash = password_hash('1122334455', PASSWORD_DEFAULT);
        $ins = $pdo->prepare(
            "INSERT INTO users (ho_ten, dien_thoai, email, ngay_sinh, mat_khau, vai_tro, tai_khoan_bi_khoa, created_at)
             VALUES (?, ?, ?, '2000-01-01', ?, 'admin', 0, NOW())"
        );
        $ins->execute(['Quản trị viên', $adminPhone, $adminEmail, $hash]);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()]));
}
