<?php
session_start();
require_once 'db.php';

// ===== HELPER =====

function outputJson($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_phone']);
}

// ===== ACTIONS =====

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        outputJson(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin!']);
    }
    
    try {
        $sql = "SELECT * FROM users WHERE dien_thoai = ? OR email = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Kiểm tra mật khẩu (hỗ trợ cả plain text cũ và hash mới)
            $passwordMatches = password_verify($password, $user['mat_khau']) || $password === $user['mat_khau'];
            
            if ($passwordMatches) {
                // Nếu là plain text, auto-hash nó
                if ($password === $user['mat_khau'] && !password_needs_rehash($user['mat_khau'], PASSWORD_DEFAULT)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET mat_khau = ? WHERE dien_thoai = ?");
                    $stmt->execute([$hashedPassword, $user['dien_thoai']]);
                }
                
                $_SESSION['user_phone'] = $user['dien_thoai'];
                $_SESSION['user_name'] = $user['ho_ten'];
                $_SESSION['user_email'] = $user['email'] ?? '';
                
                outputJson(['success' => true, 'message' => 'Đăng nhập thành công!']);
            } else {
                outputJson(['success' => false, 'message' => 'Mật khẩu sai!']);
            }
        } else {
            outputJson(['success' => false, 'message' => 'Tài khoản không tồn tại!']);
        }
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'signup') {
    $ho_ten = trim($_POST['ho_ten'] ?? '');
    $dien_thoai = trim($_POST['dien_thoai'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($ho_ten) || empty($dien_thoai) || empty($email) || empty($password)) {
        outputJson(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
    }
    
    try {
        // Check duplicate phone
        $stmt = $pdo->prepare("SELECT dien_thoai FROM users WHERE dien_thoai = ?");
        $stmt->execute([$dien_thoai]);
        if ($stmt->fetch()) {
            outputJson(['success' => false, 'message' => 'Số điện thoại đã được đăng ký!']);
        }
        
        // Check duplicate email
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            outputJson(['success' => false, 'message' => 'Email đã được đăng ký!']);
        }
        
        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (ho_ten, dien_thoai, email, mat_khau, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ho_ten, $dien_thoai, $email, $hashedPassword]);
        
        // Auto-login
        $_SESSION['user_phone'] = $dien_thoai;
        $_SESSION['user_name'] = $ho_ten;
        $_SESSION['user_email'] = $email;
        
        outputJson(['success' => true, 'message' => 'Đăng ký thành công!']);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'logout') {
    session_destroy();
    outputJson(['success' => true, 'message' => 'Đăng xuất thành công!']);
}

if ($action === 'booking') {
    if (!isLoggedIn()) {
        outputJson(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    }
    
    $ten_phim = trim($_POST['ten_phim'] ?? '');
    $ngay_chieu = trim($_POST['ngay_chieu'] ?? '');
    $gio_chieu = trim($_POST['gio_chieu'] ?? '');
    $so_ghe = trim($_POST['so_ghe'] ?? '');
    $gia_ve = trim($_POST['gia_ve'] ?? '');
    
    if (empty($ten_phim) || empty($ngay_chieu) || empty($gio_chieu) || empty($so_ghe) || empty($gia_ve)) {
        outputJson(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
    }
    
    try {
        $sql = "INSERT INTO bookings (ten_phim_dat, user_phone, ngay_dat_ve, ngay_chieu, gio_chieu, so_ghe, gia_ve) 
                VALUES (?, ?, NOW(), ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ten_phim, $_SESSION['user_phone'], $ngay_chieu, $gio_chieu, $so_ghe, $gia_ve]);
        
        outputJson(['success' => true, 'message' => 'Đặt vé thành công!', 'booking_id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'getBookings') {
    if (!isLoggedIn()) {
        outputJson(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_phone = ? ORDER BY ngay_dat_ve DESC");
        $stmt->execute([$_SESSION['user_phone']]);
        $bookings = $stmt->fetchAll();
        
        outputJson(['success' => true, 'bookings' => $bookings]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'getMovies') {
    try {
        $stmt = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC");
        $movies = $stmt->fetchAll();
        
        outputJson(['success' => true, 'movies' => $movies]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'checkSession') {
    if (isLoggedIn()) {
        outputJson([
            'isLoggedIn' => true,
            'user' => [
                'phone' => $_SESSION['user_phone'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'] ?? ''
            ]
        ]);
    } else {
        outputJson(['isLoggedIn' => false]);
    }
}

if ($action === 'resetPassword') {
    $email_or_phone = trim($_POST['email_or_phone'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    
    if (empty($email_or_phone) || empty($new_password)) {
        outputJson(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
    }
    
    try {
        $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET mat_khau = ? WHERE email = ? OR dien_thoai = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$hashedPassword, $email_or_phone, $email_or_phone]);
        
        if ($stmt->rowCount() > 0) {
            outputJson(['success' => true, 'message' => 'Đặt lại mật khẩu thành công!']);
        } else {
            outputJson(['success' => false, 'message' => 'Không tìm thấy tài khoản!']);
        }
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}
outputJson(['error' => 'Lỗi: Action không hợp lệ!']);
