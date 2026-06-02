<?php
session_start();
require_once 'db.php';
require_once 'mailer.php';

/** Số giờ sau khi đặt vé mà khách vẫn được sửa ngày/giờ/ghế (cùng luật một cửa sổ). */
define('BOOKING_EDIT_WINDOW_HOURS', 1);

// ===== HELPER =====

function outputJson($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_phone']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function userRowIsLocked(array $user) {
    return !empty($user['tai_khoan_bi_khoa']);
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
    $role = isset($user['vai_tro']) && $user['vai_tro'] === 'admin' ? 'admin' : 'user';
    $_SESSION['pending_login'] = [
        'phone' => $user['dien_thoai'],
        'name' => $user['ho_ten'],
        'email' => $user['email'] ?? '',
        'role' => $role,
        'otp_hash' => password_hash($otp, PASSWORD_DEFAULT),
        'expires' => time() + 60,
        'attempts' => 0,
        'last_sent' => time(),
    ];
}

function completeLogin($pdo, $pending, $otpDescription = 'Đăng nhập thành công (đã xác thực OTP qua email).') {
    $_SESSION['user_phone'] = $pending['phone'];
    $_SESSION['user_name'] = $pending['name'];
    $_SESSION['user_email'] = $pending['email'];
    $_SESSION['user_role'] = $pending['role'] ?? 'user';
    unset($_SESSION['pending_login']);
    logActivity($pdo, $pending['phone'], 'dang_nhap', $otpDescription);
}

function parseSeatList($soGhe) {
    $seats = [];
    foreach (explode(',', (string)$soGhe) as $part) {
        $seat = strtoupper(trim($part));
        if ($seat !== '') {
            $seats[] = $seat;
        }
    }
    return array_values(array_unique($seats));
}

function normalizeGioChieuCompare($gioChieu) {
    $gio = trim((string)$gioChieu);
    if (preg_match('/^(\d{1,2}):(\d{2})/', $gio, $m)) {
        return sprintf('%02d:%02d', (int)$m[1], (int)$m[2]);
    }
    return substr($gio, 0, 5);
}

function getOccupiedSeatsForShow($pdo, $tenPhim, $ngayChieu, $gioChieu, $excludeBookingId = null) {
    $gioCompare = normalizeGioChieuCompare($gioChieu);
    $sql = "SELECT id, so_ghe FROM bookings
            WHERE ten_phim_dat = ?
            AND ngay_chieu = ?
            AND TIME_FORMAT(gio_chieu, '%H:%i') = ?
            AND ngay_chieu IS NOT NULL AND gio_chieu IS NOT NULL
            AND TIMESTAMP(ngay_chieu, gio_chieu) >= NOW()";
    $params = [$tenPhim, $ngayChieu, $gioCompare];

    if ($excludeBookingId) {
        $sql .= " AND id != ?";
        $params[] = (int)$excludeBookingId;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $occupied = [];
    while ($row = $stmt->fetch()) {
        foreach (parseSeatList($row['so_ghe']) as $seat) {
            $occupied[$seat] = true;
        }
    }
    return array_keys($occupied);
}

function validateSeatsAvailable($pdo, $tenPhim, $ngayChieu, $gioChieu, $soGhe, $excludeBookingId = null) {
    $requested = parseSeatList($soGhe);
    if (empty($requested)) {
        return 'Danh sách ghế không hợp lệ!';
    }

    $occupied = getOccupiedSeatsForShow($pdo, $tenPhim, $ngayChieu, $gioChieu, $excludeBookingId);
    $conflicts = array_values(array_intersect($requested, $occupied));

    if (!empty($conflicts)) {
        return 'Ghế ' . implode(', ', $conflicts) . ' đã có người đặt cho suất chiếu này. Vui lòng chọn ngày, giờ hoặc ghế khác.';
    }

    return null;
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
            if (userRowIsLocked($user)) {
                outputJson(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.']);
            }

            // Kiểm tra mật khẩu (hỗ trợ cả plain text cũ và hash mới)
            $passwordMatches = password_verify($password, $user['mat_khau']) || $password === $user['mat_khau'];
            
            if ($passwordMatches) {
                if ($password === $user['mat_khau'] && !password_needs_rehash($user['mat_khau'], PASSWORD_DEFAULT)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET mat_khau = ? WHERE dien_thoai = ?");
                    $stmt->execute([$hashedPassword, $user['dien_thoai']]);
                }

                $role = ($user['vai_tro'] ?? 'user') === 'admin' ? 'admin' : 'user';

                // Quản trị: đăng nhập trực tiếp, không OTP
                if ($role === 'admin') {
                    completeLogin($pdo, [
                        'phone' => $user['dien_thoai'],
                        'name' => $user['ho_ten'],
                        'email' => trim($user['email'] ?? ''),
                        'role' => 'admin',
                    ], 'Đăng nhập quản trị thành công.');
                    outputJson([
                        'success' => true,
                        'require_otp' => false,
                        'is_admin' => true,
                        'message' => 'Đăng nhập quản trị thành công!',
                    ]);
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

    $chkLock = $pdo->prepare("SELECT tai_khoan_bi_khoa FROM users WHERE dien_thoai = ? LIMIT 1");
    $chkLock->execute([$pending['phone'] ?? '']);
    $lockRow = $chkLock->fetch();
    if ($lockRow && !empty($lockRow['tai_khoan_bi_khoa'])) {
        unset($_SESSION['pending_login']);
        outputJson(['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.']);
    }

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
        'vai_tro' => ($pending['role'] ?? 'user') === 'admin' ? 'admin' : 'user',
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
    $ngay_sinh = trim($_POST['ngay_sinh'] ?? '');
    if (empty($ngay_sinh)) {
        $ngay_sinh = '2000-01-01';
    }
    
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
        $sql = "INSERT INTO users (ho_ten, dien_thoai, email, ngay_sinh, mat_khau, vai_tro, tai_khoan_bi_khoa, created_at) VALUES (?, ?, ?, ?, ?, 'user', 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ho_ten, $dien_thoai, $email, $ngay_sinh, $hashedPassword]);
        
        // Auto-login
        $_SESSION['user_phone'] = $dien_thoai;
        $_SESSION['user_name'] = $ho_ten;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'user';

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
    if (isAdmin()) {
        outputJson(['success' => false, 'message' => 'Tài khoản quản trị không thể đặt vé. Vui lòng dùng tài khoản khách hàng.']);
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
        $seatError = validateSeatsAvailable($pdo, $ten_phim, $ngay_chieu, $gio_chieu, $so_ghe);
        if ($seatError) {
            outputJson(['success' => false, 'message' => $seatError]);
        }

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
    $win = BOOKING_EDIT_WINDOW_HOURS;
    $withinWindow = $hoursSince <= $win;
    $booking['can_edit_datetime'] = $withinWindow;
    $booking['can_edit_seats'] = false;
    $booking['hours_since_booking'] = round($hoursSince, 2);
    $booking['hours_remaining_edit'] = $withinWindow ? max(0, round($win - $hoursSince, 2)) : 0;
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
        
        $withinWindow = getHoursSinceBooking($booking['ngay_dat_ve']) <= BOOKING_EDIT_WINDOW_HOURS;
        
        if (!$withinWindow) {
            $h = (int)BOOKING_EDIT_WINDOW_HOURS;
            outputJson(['success' => false, 'message' => "Đã quá {$h} giờ kể từ khi đặt vé. Không thể thay đổi ngày và giờ chiếu!"]);
        }
        
        if (empty($ngayChieu) || empty($gioChieu)) {
            outputJson(['success' => false, 'message' => 'Vui lòng chọn ngày và giờ chiếu!']);
        }

        $newSeats = !empty($soGhe) ? $soGhe : ($booking['so_ghe'] ?? '');
        $seatList = parseSeatList($newSeats);
        if (empty($seatList)) {
            outputJson(['success' => false, 'message' => 'Vui lòng chọn ít nhất 1 ghế trên sơ đồ!', 'need_seat_pick' => true]);
        }

        $seatError = validateSeatsAvailable(
            $pdo,
            $booking['ten_phim_dat'],
            $ngayChieu,
            $gioChieu,
            implode(', ', $seatList),
            $bookingId
        );
        if ($seatError) {
            outputJson(['success' => false, 'message' => $seatError, 'need_seat_pick' => true]);
        }

        $soGheFormatted = implode(', ', $seatList);
        $giaVeMoi = count($seatList) * 50000;
        
        $sql = "UPDATE bookings SET ngay_chieu = ?, gio_chieu = ?, so_ghe = ?, gia_ve = ? WHERE id = ? AND user_phone = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ngayChieu, $gioChieu, $soGheFormatted, $giaVeMoi, $bookingId, $_SESSION['user_phone']]);

        $suat = date('d/m/Y', strtotime($ngayChieu)) . ' ' . substr($gioChieu, 0, 5);
        logActivity(
            $pdo,
            $_SESSION['user_phone'],
            'sua_ve',
            'Cập nhật vé phim "' . $booking['ten_phim_dat'] . '" - Suất mới: ' . $suat . ' - Ghế: ' . $soGheFormatted . '.'
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

if ($action === 'getBookedSeats') {
    $ten_phim = trim($_POST['ten_phim'] ?? '');
    $ngay_chieu = trim($_POST['ngay_chieu'] ?? '');
    $gio_chieu = trim($_POST['gio_chieu'] ?? '');

    if (empty($ten_phim) || empty($ngay_chieu) || empty($gio_chieu)) {
        outputJson(['success' => false, 'message' => 'Thiếu thông tin suất chiếu!', 'seats' => []]);
    }

    try {
        $seats = getOccupiedSeatsForShow($pdo, $ten_phim, $ngay_chieu, $gio_chieu);
        outputJson(['success' => true, 'seats' => $seats]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage(), 'seats' => []]);
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
        $role = $_SESSION['user_role'] ?? 'user';
        outputJson([
            'isLoggedIn' => true,
            'user' => [
                'phone' => $_SESSION['user_phone'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'] ?? '',
                'role' => $role,
                'is_admin' => $role === 'admin',
            ]
        ]);
    } else {
        outputJson(['isLoggedIn' => false]);
    }
}

// ----- Quản trị (chỉ admin) -----
if ($action === 'adminGetRevenue') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    try {
        $tot = $pdo->query("SELECT COUNT(*) AS so_ve, COALESCE(SUM(gia_ve), 0) AS tong_tien FROM bookings")->fetch();
        $stmt = $pdo->query(
            "SELECT ten_phim_dat, COUNT(*) AS so_ve, COALESCE(SUM(gia_ve), 0) AS tong_tien
             FROM bookings GROUP BY ten_phim_dat ORDER BY tong_tien DESC"
        );
        $byMovie = $stmt->fetchAll();
        outputJson([
            'success' => true,
            'total_tickets' => (int)($tot['so_ve'] ?? 0),
            'total_revenue' => (float)($tot['tong_tien'] ?? 0),
            'by_movie' => $byMovie,
        ]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'adminGetActivities') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    try {
        $limit = min(500, max(50, (int)($_POST['limit'] ?? 200)));
        $stmt = $pdo->prepare(
            "SELECT id, user_phone, loai_hoat_dong, noi_dung, created_at
             FROM user_activities ORDER BY created_at DESC LIMIT " . (int)$limit
        );
        $stmt->execute();
        outputJson(['success' => true, 'activities' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'adminGetUsers') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    try {
        $stmt = $pdo->query(
            "SELECT dien_thoai, ho_ten, email, vai_tro, tai_khoan_bi_khoa, created_at
             FROM users ORDER BY created_at DESC"
        );
        outputJson(['success' => true, 'users' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'adminSetUserLock') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    $targetPhone = trim($_POST['dien_thoai'] ?? '');
    $lock = isset($_POST['locked']) ? (int)$_POST['locked'] : -1;
    if ($targetPhone === '' || ($lock !== 0 && $lock !== 1)) {
        outputJson(['success' => false, 'message' => 'Thiếu hoặc sai thông tin!']);
    }
    if ($targetPhone === $_SESSION['user_phone']) {
        outputJson(['success' => false, 'message' => 'Không thể khóa/mở khóa chính tài khoản quản trị đang đăng nhập!']);
    }
    try {
        $stmt = $pdo->prepare("SELECT vai_tro FROM users WHERE dien_thoai = ? LIMIT 1");
        $stmt->execute([$targetPhone]);
        $row = $stmt->fetch();
        if (!$row) {
            outputJson(['success' => false, 'message' => 'Không tìm thấy người dùng!']);
        }
        if (($row['vai_tro'] ?? '') === 'admin' && $lock === 1) {
            outputJson(['success' => false, 'message' => 'Không thể khóa tài khoản quản trị viên!']);
        }
        $upd = $pdo->prepare("UPDATE users SET tai_khoan_bi_khoa = ? WHERE dien_thoai = ?");
        $upd->execute([$lock, $targetPhone]);
        logActivity(
            $pdo,
            $_SESSION['user_phone'],
            'quan_tri',
            ($lock ? 'Khóa' : 'Mở khóa') . ' tài khoản SĐT ' . $targetPhone . ' bởi quản trị viên.'
        );
        outputJson(['success' => true, 'message' => $lock ? 'Đã khóa tài khoản.' : 'Đã mở khóa tài khoản.']);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
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
