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
} catch (PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()]));
}