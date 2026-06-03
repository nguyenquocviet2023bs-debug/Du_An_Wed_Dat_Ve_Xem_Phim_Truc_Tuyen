<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoạt Động - Cinema Group 11</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div id="mainContent" class="main-content" style="display: none;">
        <header class="header">
            <div class="container">
                <div class="header-top">
                    <div class="logo">
                        <i class="fas fa-film"></i>
                        <span>Cinema Group 11</span>
                    </div>
                    <nav class="navbar">
                        <a href="TrangChu.php" class="nav-link">Trang chủ</a>
                        <a href="VeDatVe.php" class="nav-link">Vé/Đặt vé</a>
                        <a href="HoatDong.php" class="nav-link active">Hoạt động</a>
                        <a href="QuanTri.php" class="nav-link" id="navAdminLink" style="display:none">Quản trị</a>
                    </nav>
                    <div class="header-right">
                        <div class="search-container">
                            <input type="text" class="search-input" placeholder="Tìm tên phim...">
                            <button class="search-btn" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <button class="login-btn" id="logoutBtn">Đăng Xuất</button>
                        <div class="vip-badge">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <section class="activity-section">
            <div class="container">
                <h2 class="section-title">Hoạt Động Của Bạn</h2>
                <p class="activity-notice">
                    <i class="fas fa-bell"></i>
                    Lịch sử đăng nhập, đặt vé, sửa vé và các thông báo liên quan tài khoản của bạn.
                </p>
                <div id="activityList" class="activity-list">
                    <p class="activity-loading">Đang tải hoạt động...</p>
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

    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2>Đăng Nhập</h2>
                <p>Đăng nhập để xem hoạt động!</p>
            </div>
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="loginEmail">Email / Tên đăng nhập</label>
                    <input type="text" id="loginEmail" placeholder="Email hoặc số điện thoại" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Mật khẩu</label>
                    <input type="password" id="loginPassword" placeholder="Mật khẩu" required>
                </div>
                <button type="submit" class="btn-submit">Đăng Nhập</button>
                <p class="signup-link">Chưa có tài khoản? <a href="TrangChu.php">Đăng ký tại trang chủ</a></p>
            </form>
        </div>
    </div>
    <?php include 'otp-modal.php'; ?>

    <script src="auth-login.js"></script>
    <script src="hoat-dong.js"></script>
</body>
</html>
