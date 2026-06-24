<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();
require_once 'db.php';
require_once 'mailer.php';

define('BOOKING_EDIT_WINDOW_HOURS', 0.5);

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
        'otp_sent' => false,
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

            $passwordMatches = password_verify($password, $user['mat_khau']) || $password === $user['mat_khau'];
            
            if ($passwordMatches) {
                if ($password === $user['mat_khau'] && !password_needs_rehash($user['mat_khau'], PASSWORD_DEFAULT)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET mat_khau = ? WHERE dien_thoai = ?");
                    $stmt->execute([$hashedPassword, $user['dien_thoai']]);
                }

                $role = ($user['vai_tro'] ?? 'user') === 'admin' ? 'admin' : 'user';

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

                $response = [
                    'success' => true,
                    'require_otp' => true,
                    'masked_email' => maskEmail($email),
                    'dev_mode' => false,
                    'expires_in' => 60,
                    'message' => 'Vui lòng nhập mã xác thực đã gửi đến email của bạn.',
                ];

                outputJson($response);
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
    $firstSend = empty($pending['otp_sent']);
    if (!$isExpired && !$firstSend && time() - ($pending['last_sent'] ?? 0) < 60) {
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
        unset($_SESSION['pending_login']);
        outputJson(['success' => false, 'message' => $mailResult['message']]);
    }

    $_SESSION['pending_login']['otp_sent'] = true;

    $response = [
        'success' => true,
        'masked_email' => maskEmail($pending['email']),
        'dev_mode' => !empty($mailResult['dev']),
        'expires_in' => 60,
        'message' => 'Đã gửi mã OTP mới! Mã có hiệu lực trong 60 giây.',
    ];

    if (!empty($mailResult['dev'])) {
        $response['dev_otp'] = $otp;
    }

    outputJson($response);
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
        $stmt = $pdo->prepare("SELECT dien_thoai FROM users WHERE dien_thoai = ?");
        $stmt->execute([$dien_thoai]);
        if ($stmt->fetch()) {
            outputJson(['success' => false, 'message' => 'Số điện thoại đã được đăng ký!']);
        }
        
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            outputJson(['success' => false, 'message' => 'Email đã được đăng ký!']);
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (ho_ten, dien_thoai, email, ngay_sinh, mat_khau, vai_tro, tai_khoan_bi_khoa, created_at) VALUES (?, ?, ?, ?, ?, 'user', 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ho_ten, $dien_thoai, $email, $ngay_sinh, $hashedPassword]);
        
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

    $showtimeDatetime = $ngay_chieu . ' ' . $gio_chieu;
    if (strtotime($showtimeDatetime) < time()) {
        outputJson(['success' => false, 'message' => 'Suất chiếu đã qua. Không thể đặt vé cho suất chiếu trong quá khứ!']);
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
    $bookingTs = strtotime($ngayDatVe);
    return $bookingTs ? (time() - $bookingTs) / 3600 : 999;
}

function enrichBookingRow($booking) {
    $hoursSince = getHoursSinceBooking($booking['ngay_dat_ve']);
    $win = BOOKING_EDIT_WINDOW_HOURS;

    $showtimeStr = ($booking['ngay_chieu'] ?? '') . ' ' . ($booking['gio_chieu'] ?? '');
    $totalHoursFromBookingToShow = 999;
    if ($showtimeStr) {
        $showTs = strtotime($showtimeStr);
        $bookingTs = strtotime($booking['ngay_dat_ve']);
        if ($showTs && $bookingTs && $showTs > $bookingTs) {
            $totalHoursFromBookingToShow = max(0, ($showTs - $bookingTs) / 3600);
        } else {
            $totalHoursFromBookingToShow = 0;
        }
    }
    $effectiveWin = min($win, $totalHoursFromBookingToShow);

    $withinWindow = $hoursSince <= $effectiveWin;
    $soLanSua = (int)($booking['so_lan_sua'] ?? 0);
    $daHetQuyen = $soLanSua >= 1;

    $booking['can_edit_datetime'] = $withinWindow && !$daHetQuyen;
    $booking['can_edit_seats'] = false;
    $booking['hours_since_booking'] = round($hoursSince, 2);
    $booking['hours_remaining_edit'] = $withinWindow ? max(0, round($effectiveWin - $hoursSince, 2)) : 0;
    $booking['so_lan_sua'] = $soLanSua;
    $booking['da_het_quyen_sua'] = $daHetQuyen;
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
        
        $soLanSua = (int)($booking['so_lan_sua'] ?? 0);
        if ($soLanSua >= 1) {
            outputJson(['success' => false, 'message' => 'Bạn đã sử dụng quyền chỉnh sửa vé này rồi. Mỗi vé chỉ được sửa 1 lần duy nhất!']);
        }
        
        $withinWindow = getHoursSinceBooking($booking['ngay_dat_ve']) <= BOOKING_EDIT_WINDOW_HOURS;
        if ($withinWindow) {
            $showtimeStr = ($booking['ngay_chieu'] ?? '') . ' ' . ($booking['gio_chieu'] ?? '');
            $totalHoursFromBookingToShow = 999;
            if ($showtimeStr) {
                $showTs = strtotime($showtimeStr);
                $bookingTs = strtotime($booking['ngay_dat_ve']);
                if ($showTs && $bookingTs && $showTs > $bookingTs) {
                    $totalHoursFromBookingToShow = max(0, ($showTs - $bookingTs) / 3600);
                } else {
                    $totalHoursFromBookingToShow = 0;
                }
            }
            $effectiveWin = min(BOOKING_EDIT_WINDOW_HOURS, $totalHoursFromBookingToShow);
            $withinWindow = getHoursSinceBooking($booking['ngay_dat_ve']) <= $effectiveWin;
        }
        
        if (!$withinWindow) {
            outputJson(['success' => false, 'message' => "Đã hết thời gian cho phép chỉnh sửa vé!"]);
        }
        
        if (empty($ngayChieu) || empty($gioChieu)) {
            outputJson(['success' => false, 'message' => 'Vui lòng chọn ngày và giờ chiếu!']);
        }

        $showtimeDatetime = $ngayChieu . ' ' . $gioChieu;
        if (strtotime($showtimeDatetime) < time()) {
            outputJson(['success' => false, 'message' => 'Suất chiếu đã qua. Không thể đặt vé cho suất chiếu trong quá khứ!']);
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
        
        $sql = "UPDATE bookings SET ngay_chieu = ?, gio_chieu = ?, so_ghe = ?, gia_ve = ?, so_lan_sua = so_lan_sua + 1 WHERE id = ? AND user_phone = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ngayChieu, $gioChieu, $soGheFormatted, $giaVeMoi, $bookingId, $_SESSION['user_phone']]);

        $suat = date('d/m/Y', strtotime($ngayChieu)) . ' ' . substr($gioChieu, 0, 5);
        logActivity(
            $pdo,
            $_SESSION['user_phone'],
            'sua_ve',
            'Cập nhật vé phim "' . $booking['ten_phim_dat'] . '" - Suất mới: ' . $suat . ' - Ghế: ' . $soGheFormatted . ' (Lần sửa thứ ' . ($soLanSua + 1) . ').'
        );
        
        outputJson(['success' => true, 'message' => 'Cập nhật vé thành công! Lưu ý: Mỗi vé chỉ được sửa 1 lần duy nhất.']);
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

function cleanupExpiredMovies($pdo) {
    $stmt = $pdo->prepare("DELETE FROM movies WHERE ngay_khoi_chieu IS NOT NULL AND ngay_khoi_chieu < DATE_SUB(CURDATE(), INTERVAL 2 MONTH)");
    $stmt->execute();
}

if ($action === 'getMovies') {
    try {
        cleanupExpiredMovies($pdo);
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

if ($action === 'adminGetRevenue') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    $month = isset($_POST['month']) ? (int)$_POST['month'] : null;
    $year = isset($_POST['year']) ? (int)$_POST['year'] : null;
    
    try {
        $whereClause = '';
        $params = [];
        
        if ($month && $year) {
            $whereClause = ' WHERE MONTH(ngay_chieu) = ? AND YEAR(ngay_chieu) = ?';
            $params = [$month, $year];
        }
        
        $totSql = "SELECT COUNT(*) AS so_ve, COALESCE(SUM(gia_ve), 0) AS tong_tien FROM bookings" . $whereClause;
        $stmt = $pdo->prepare($totSql);
        $stmt->execute($params);
        $tot = $stmt->fetch();
        
        $bySql = "SELECT ten_phim_dat, COUNT(*) AS so_ve, COALESCE(SUM(gia_ve), 0) AS tong_tien
                  FROM bookings" . $whereClause . " GROUP BY ten_phim_dat ORDER BY tong_tien DESC";
        $stmt = $pdo->prepare($bySql);
        $stmt->execute($params);
        $byMovie = $stmt->fetchAll();
        
        outputJson([
            'success' => true,
            'total_tickets' => (int)($tot['so_ve'] ?? 0),
            'total_revenue' => (float)($tot['tong_tien'] ?? 0),
            'by_movie' => $byMovie,
            'month' => $month,
            'year' => $year,
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

if ($action === 'adminGetMovies') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    try {
        cleanupExpiredMovies($pdo);
        $stmt = $pdo->query("SELECT * FROM movies ORDER BY created_at DESC");
        outputJson(['success' => true, 'movies' => $stmt->fetchAll()]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'adminAddMovie') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    $tenPhim = trim($_POST['ten_phim'] ?? '');
    $moTa = trim($_POST['mo_ta'] ?? '');
    $theLoai = trim($_POST['the_loai'] ?? '');
    $daoDien = trim($_POST['dao_dien'] ?? '');
    $thoiLuong = (int)($_POST['thoi_luong'] ?? 0);
    $hinhAnhUrl = trim($_POST['hinh_anh_url'] ?? '');
    $ngayKhoiChieu = trim($_POST['ngay_khoi_chieu'] ?? '');
    $gioiHanTuoi = trim($_POST['gioi_han_do_tuoi'] ?? 'K');
    
    if (empty($tenPhim)) {
        outputJson(['success' => false, 'message' => 'Vui lòng nhập tên phim!']);
    }
    
    try {
        $sql = "INSERT INTO movies (ten_phim, mo_ta, the_loai, dao_dien, thoi_luong, hinh_anh_url, ngay_khoi_chieu, gioi_han_do_tuoi) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tenPhim, $moTa, $theLoai, $daoDien, $thoiLuong, $hinhAnhUrl, $ngayKhoiChieu ?: null, $gioiHanTuoi]);
        
        logActivity($pdo, $_SESSION['user_phone'], 'quan_tri', 'Thêm phim mới: ' . $tenPhim);
        outputJson(['success' => true, 'message' => 'Thêm phim thành công!', 'movie_id' => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'adminUpdateMovie') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    $movieId = (int)($_POST['movie_id'] ?? 0);
    $tenPhim = trim($_POST['ten_phim'] ?? '');
    $moTa = trim($_POST['mo_ta'] ?? '');
    $theLoai = trim($_POST['the_loai'] ?? '');
    $daoDien = trim($_POST['dao_dien'] ?? '');
    $thoiLuong = (int)($_POST['thoi_luong'] ?? 0);
    $hinhAnhUrl = trim($_POST['hinh_anh_url'] ?? '');
    $ngayKhoiChieu = trim($_POST['ngay_khoi_chieu'] ?? '');
    $gioiHanTuoi = trim($_POST['gioi_han_do_tuoi'] ?? 'K');
    
    if ($movieId <= 0 || empty($tenPhim)) {
        outputJson(['success' => false, 'message' => 'Thông tin không hợp lệ!']);
    }
    
    try {
        $sql = "UPDATE movies SET ten_phim = ?, mo_ta = ?, the_loai = ?, dao_dien = ?, thoi_luong = ?, 
                hinh_anh_url = ?, ngay_khoi_chieu = ?, gioi_han_do_tuoi = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tenPhim, $moTa, $theLoai, $daoDien, $thoiLuong, $hinhAnhUrl, $ngayKhoiChieu ?: null, $gioiHanTuoi, $movieId]);
        
        logActivity($pdo, $_SESSION['user_phone'], 'quan_tri', 'Cập nhật thông tin phim: ' . $tenPhim);
        outputJson(['success' => true, 'message' => 'Cập nhật phim thành công!']);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

if ($action === 'adminDeleteMovie') {
    if (!isAdmin()) {
        outputJson(['success' => false, 'message' => 'Không có quyền truy cập!']);
    }
    $movieId = (int)($_POST['movie_id'] ?? 0);
    if ($movieId <= 0) {
        outputJson(['success' => false, 'message' => 'ID phim không hợp lệ!']);
    }
    
    try {
        $stmt = $pdo->prepare("SELECT ten_phim FROM movies WHERE id = ? LIMIT 1");
        $stmt->execute([$movieId]);
        $movie = $stmt->fetch();
        if (!$movie) {
            outputJson(['success' => false, 'message' => 'Không tìm thấy phim!']);
        }
        
        $chk = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE ten_phim_dat = ?");
        $chk->execute([$movie['ten_phim']]);
        $count = (int)$chk->fetchColumn();
        
        if ($count > 0) {
            outputJson(['success' => false, 'message' => "Không thể xóa phim này vì đã có {$count} vé được đặt!"]);
        }
        
        $del = $pdo->prepare("DELETE FROM movies WHERE id = ?");
        $del->execute([$movieId]);
        
        logActivity($pdo, $_SESSION['user_phone'], 'quan_tri', 'Xóa phim: ' . $movie['ten_phim']);
        outputJson(['success' => true, 'message' => 'Xóa phim thành công!']);
    } catch (Exception $e) {
        outputJson(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
    }
}

outputJson(['error' => 'Lỗi: Action không hợp lệ!']);
