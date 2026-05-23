<?php
session_start();
require_once 'db.php';
require_once 'mailer.php';

// ===== HELPER =====

function outputJson($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_phone']);
}

function logActivity($pdo, $userPhone, $loai, $noiDung) {
    if (empty($userPhone) || empty($noiDung)) {
        return;
    }
    $stmt = $pdo->prepare("INSERT INTO user_activities (user_phone, loai_hoat_dong, noi_dung) VALUES (?, ?, ?)");
    $stmt->execute([$userPhone, $loai, $noiDung]);
}

function generateLoginOtp() {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function createPendingLogin($user, $otp) {
    $_SESSION['pending_login'] = [
        'phone' => $user['dien_thoai'],
        'name' => $user['ho_ten'],
        'email' => $user['email'] ?? '',
        'otp_hash' => password_hash($otp, PASSWORD_DEFAULT),
        'expires' => time() + 60,
        'attempts' => 0,
        'last_sent' => time(),
    ];
}

function completeLogin($pdo, $pending) {
    $_SESSION['user_phone'] = $pending['phone'];
    $_SESSION['user_name'] = $pending['name'];
    $_SESSION['user_email'] = $pending['email'];
    unset($_SESSION['pending_login']);
    logActivity($pdo, $pending['phone'], 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).');
}

function cleanupExpiredBookings($pdo, $userPhone) {
    $stmt = $pdo->prepare("SELECT * FROM bookings
        WHERE user_phone = ?
        AND ngay_chieu IS NOT NULL AND gio_chieu IS NOT NULL
        AND TIMESTAMP(ngay_chieu, gio_chieu) < NOW()");
    $stmt->execute([$userPhone]);
    $expired = $stmt->fetchAll();

    foreach ($expired as $booking) {
        $suat = date('d/m/Y', strtotime($booking['ngay_chieu'])) . ' ' . substr($booking['gio_chieu'], 0, 5);
        logActivity(
            $pdo,
            $userPhone,
            'het_han',
            'Vé phim "' . $booking['ten_phim_dat'] . '" (suất ' . $suat . ', ghế ' . $booking['so_ghe'] . ') đã qua giờ chiếu và được xóa khỏi danh sách vé.'
        );
    }

    if (count($expired) > 0) {
        $del = $pdo->prepare("DELETE FROM bookings
            WHERE user_phone = ?
            AND ngay_chieu IS NOT NULL AND gio_chieu IS NOT NULL
            AND TIMESTAMP(ngay_chieu, gio_chieu) < NOW()");
        $del->execute([$userPhone]);
    }
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
                if ($password === $user['mat_khau'] && !password_needs_rehash($user['mat_khau'], PASSWORD_DEFAULT)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET mat_khau = ? WHERE dien_thoai = ?");
                    $stmt->execute([$hashedPassword, $user['dien_thoai']]);
                }

                $email = trim($user['email'] ?? '');
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    outputJson(['success' => false, 'message' => 'Tài khoản chưa có email hợp lệ để nhận mã xác thực!']);
                }

                $otp = generateLoginOtp();
                createPendingLogin($user, $otp);

                $mailResult = sendOtpEmail($email, $user['ho_ten'], $otp);
                if (!$mailResult['success']) {
                    unset($_SESSION['pending_login']);
                    outputJson(['success' => false, 'message' => $mailResult['message']]);
                }

                outputJson([
                    'success' => true,
                    'require_otp' => true,
                    'masked_email' => maskEmail($email),
                    'dev_mode' => !empty($mailResult['dev']),
                    'expires_in' => 60,
                    'message' => 'Mã xác thực đã được gửi đến email của bạn.',
                ]);
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

if ($action === 'verifyLoginOtp') {
    $otp = trim($_POST['otp'] ?? '');

    if (!preg_match('/^\d{6}$/', $otp)) {
        outputJson(['success' => false, 'message' => 'Mã xác thực phải gồm 6 chữ số!']);
    }

    if (empty($_SESSION['pending_login'])) {
        outputJson(['success' => false, 'message' => 'Phiên xác thực hết hạn. Vui lòng đăng nhập lại!']);
    }

    $pending = $_SESSION['pending_login'];

    if (time() > ($pending['expires'] ?? 0)) {
        outputJson([
            'success' => false,
            'otp_expired' => true,
            'message' => 'Mã xác thực đã hết hạn. Vui lòng bấm "Gửi lại mã" để nhận OTP mới.',
        ]);
    }

    $_SESSION['pending_login']['attempts'] = ($pending['attempts'] ?? 0) + 1;
    if ($_SESSION['pending_login']['attempts'] > 5) {
        unset($_SESSION['pending_login']);
        outputJson(['success' => false, 'message' => 'Nhập sai quá 5 lần. Vui lòng đăng nhập lại!']);
    }

    if (!password_verify($otp, $pending['otp_hash'])) {
        outputJson(['success' => false, 'message' => 'Mã xác thực không đúng!']);
    }

    completeLogin($pdo, $pending);
    outputJson(['success' => true, 'message' => 'Xác thực thành công! Đăng nhập hoàn tất.']);
}

if ($action === 'resendLoginOtp') {
    if (empty($_SESSION['pending_login'])) {
        outputJson(['success' => false, 'message' => 'Phiên xác thực hết hạn. Vui lòng đăng nhập lại!']);
    }

    $pending = $_SESSION['pending_login'];
    $isExpired = time() > ($pending['expires'] ?? 0);
    if (!$isExpired && time() - ($pending['last_sent'] ?? 0) < 60) {
        $wait = 60 - (time() - ($pending['last_sent'] ?? 0));
        outputJson(['success' => false, 'message' => 'Vui lòng đợi ' . $wait . ' giây trước khi gửi lại mã!']);
    }

    $otp = generateLoginOtp();
    $user = [
        'dien_thoai' => $pending['phone'],
        'ho_ten' => $pending['name'],
        'email' => $pending['email'],
    ];
    createPendingLogin($user, $otp);

    $mailResult = sendOtpEmail($pending['email'], $pending['name'], $otp);
    if (!$mailResult['success']) {
        outputJson(['success' => false, 'message' => $mailResult['message']]);
    }

    outputJson([
        'success' => true,
        'masked_email' => maskEmail($pending['email']),
        'dev_mode' => !empty($mailResult['dev']),
        'expires_in' => 60,
        'message' => 'Đã gửi mã OTP mới! Mã có hiệu lực trong 60 giây.',
    ]);
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

        logActivity($pdo, $dien_thoai, 'dang_ky', 'Đăng ký tài khoản mới: ' . $ho_ten . ' (' . $email . ').');
        
        outputJson(['success' => true, 'message' => 'Đăng ký thành công!']);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'logout') {
    $userPhone = $_SESSION['user_phone'] ?? '';
    if ($userPhone) {
        logActivity($pdo, $userPhone, 'dang_xuat', 'Đăng xuất khỏi hệ thống.');
    }
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

        $suat = date('d/m/Y', strtotime($ngay_chieu)) . ' ' . substr($gio_chieu, 0, 5);
        logActivity(
            $pdo,
            $_SESSION['user_phone'],
            'dat_ve',
            'Đặt vé phim "' . $ten_phim . '" - Ghế: ' . $so_ghe . ' - Suất: ' . $suat . ' - ' . number_format((float)$gia_ve, 0, ',', '.') . ' VND.'
        );
        
        outputJson(['success' => true, 'message' => 'Đặt vé thành công!', 'booking_id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

function getHoursSinceBooking($ngayDatVe) {
    $bookingTime = new DateTime($ngayDatVe);
    $now = new DateTime();
    return ($now->getTimestamp() - $bookingTime->getTimestamp()) / 3600;
}

function enrichBookingRow($booking) {
    $hoursSince = getHoursSinceBooking($booking['ngay_dat_ve']);
    $withinFiveHours = $hoursSince <= 5;
    $booking['can_edit_datetime'] = $withinFiveHours;
    $booking['can_edit_seats'] = false;
    $booking['hours_since_booking'] = round($hoursSince, 2);
    $booking['hours_remaining_edit'] = $withinFiveHours ? max(0, round(5 - $hoursSince, 2)) : 0;
    return $booking;
}

if ($action === 'getBookings') {
    if (!isLoggedIn()) {
        outputJson(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    }
    
    try {
        cleanupExpiredBookings($pdo, $_SESSION['user_phone']);

        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_phone = ? ORDER BY ngay_dat_ve DESC");
        $stmt->execute([$_SESSION['user_phone']]);
        $bookings = array_map('enrichBookingRow', $stmt->fetchAll());
        
        outputJson(['success' => true, 'bookings' => $bookings]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'updateBooking') {
    if (!isLoggedIn()) {
        outputJson(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    }
    
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $ngayChieu = trim($_POST['ngay_chieu'] ?? '');
    $gioChieu = trim($_POST['gio_chieu'] ?? '');
    $soGhe = trim($_POST['so_ghe'] ?? '');
    
    if ($bookingId <= 0) {
        outputJson(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin!']);
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_phone = ? LIMIT 1");
        $stmt->execute([$bookingId, $_SESSION['user_phone']]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            outputJson(['success' => false, 'message' => 'Không tìm thấy vé!']);
        }
        
        if (!empty($soGhe) && $soGhe !== ($booking['so_ghe'] ?? '')) {
            outputJson(['success' => false, 'message' => 'Số ghế đã cố định, không thể thay đổi!']);
        }
        
        $withinFiveHours = getHoursSinceBooking($booking['ngay_dat_ve']) <= 5;
        
        if (!$withinFiveHours) {
            outputJson(['success' => false, 'message' => 'Đã quá 5 giờ kể từ khi đặt vé. Không thể thay đổi ngày và giờ chiếu!']);
        }
        
        if (empty($ngayChieu) || empty($gioChieu)) {
            outputJson(['success' => false, 'message' => 'Vui lòng chọn ngày và giờ chiếu!']);
        }
        
        $sql = "UPDATE bookings SET ngay_chieu = ?, gio_chieu = ? WHERE id = ? AND user_phone = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ngayChieu, $gioChieu, $bookingId, $_SESSION['user_phone']]);

        $suat = date('d/m/Y', strtotime($ngayChieu)) . ' ' . substr($gioChieu, 0, 5);
        logActivity(
            $pdo,
            $_SESSION['user_phone'],
            'sua_ve',
            'Cập nhật vé phim "' . $booking['ten_phim_dat'] . '" - Suất mới: ' . $suat . ' - Ghế: ' . $booking['so_ghe'] . '.'
        );
        
        outputJson(['success' => true, 'message' => 'Cập nhật vé thành công!']);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'getActivities') {
    if (!isLoggedIn()) {
        outputJson(['success' => false, 'message' => 'Vui lòng đăng nhập!']);
    }

    try {
        cleanupExpiredBookings($pdo, $_SESSION['user_phone']);

        $stmt = $pdo->prepare("SELECT * FROM user_activities WHERE user_phone = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_phone']]);
        $activities = $stmt->fetchAll();

        outputJson(['success' => true, 'activities' => $activities]);
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
