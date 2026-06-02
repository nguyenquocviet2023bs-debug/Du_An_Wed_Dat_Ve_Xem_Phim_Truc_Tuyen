<?php
session_start();
if (empty($_SESSION['user_phone']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: TrangChu.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Cinema Group 11</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="mainContent" class="main-content">
        <header class="header">
            <div class="container">
                <div class="header-top">
                    <div class="logo">
                        <i class="fas fa-user-shield"></i>
                        <span>Cinema Group 11</span>
                    </div>
                    <nav class="navbar">
                        <a href="TrangChu.php" class="nav-link">Trang chủ</a>
                        <a href="VeDatVe.php" class="nav-link">Vé/Đặt vé</a>
                        <a href="HoatDong.php" class="nav-link">Hoạt động</a>
                        <a href="QuanTri.php" class="nav-link active">Quản trị</a>
                    </nav>
                    <div class="header-right">
                        <button class="login-btn" id="logoutBtn" type="button">Đăng xuất</button>
                        <div class="vip-badge">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="admin-section">
            <div class="container">
                <h2 class="section-title">Bảng quản trị</h2>
                <p class="admin-intro">
                    Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></strong>.
                    Tài khoản quản trị không dùng để đặt vé trên trang chủ.
                </p>

                <div class="admin-tabs" role="tablist">
                    <button type="button" class="admin-tab active" data-tab="revenue">Thống kê doanh thu</button>
                    <button type="button" class="admin-tab" data-tab="activities">Hoạt động khách hàng</button>
                    <button type="button" class="admin-tab" data-tab="users">Tài khoản người dùng</button>
                </div>

                <div id="panel-revenue" class="admin-panel active">
                    <h3><i class="fas fa-chart-line"></i> Doanh thu từ vé đã đặt</h3>
                    <div id="revenueContent" class="admin-loading">Đang tải…</div>
                </div>

                <div id="panel-activities" class="admin-panel">
                    <h3><i class="fas fa-history"></i> Lịch sử hoạt động (toàn hệ thống)</h3>
                    <div id="activitiesContent" class="admin-loading">Chọn tab để tải dữ liệu.</div>
                </div>

                <div id="panel-users" class="admin-panel">
                    <h3><i class="fas fa-users"></i> Người dùng — khóa / mở khóa</h3>
                    <p class="admin-intro" style="margin-top:-8px">Tài khoản bị khóa không đăng nhập được (kể cả sau bước mật khẩu).</p>
                    <div id="usersContent" class="admin-loading">Chọn tab để tải dữ liệu.</div>
                </div>
            </div>
        </section>

        <footer class="footer">
            <div class="container">
                <div class="footer-bottom">
                    <div class="company-info">
                        <h4>ALL MEMBERS OF GROUP 11</h4>
                        <p>HOTLINE: +84 ******049</p>
                    </div>
                    <div class="footer-logo">
                        <i class="fas fa-film"></i>
                        <span>Cinema group 11</span>
                    </div>
                </div>
                <div class="footer-copyright">
                    <p>&copy; 2026 Cinema group 11. Bảo lưu mọi quyền.</p>
                </div>
            </div>
        </footer>
    </div>

    <script src="admin.js"></script>
</body>
</html>
