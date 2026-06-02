<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vé Đã Đặt - Cinema Group 11</title>
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
                        <a href="VeDatVe.php" class="nav-link active">Vé/Đặt vé</a>
                        <a href="HoatDong.php" class="nav-link">Hoạt động</a>
                        <a href="QuanTri.php" class="nav-link" id="navAdminLink" style="display:none" title="Chỉ hiện với tài khoản quản trị">Quản trị</a>
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

        <section class="tickets-section">
            <div class="container">
                <h2 class="section-title">Vé Đã Đặt</h2>
                <p class="tickets-notice">
                    <i class="fas fa-info-circle"></i>
                    Trong vòng <strong>1 giờ</strong> sau khi đặt, bạn có thể sửa <strong>ngày và giờ</strong> (và ghế khi còn trong thời hạn).
                    Sơ đồ ghế chỉ hiện khi bấm <strong>Thay đổi ghế</strong> hoặc khi ghế cũ đã có người đặt ở suất mới.
                    Vé sẽ <strong>tự động xóa</strong> sau khi qua ngày và giờ chiếu.
                </p>
                <div id="ticketsList" class="tickets-list">
                    <p class="tickets-loading">Đang tải danh sách vé...</p>
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
                <p>Đăng nhập để xem vé đã đặt!</p>
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
    <script src="seats-common.js"></script>
    <script src="ve-dat-ve.js"></script>
</body>
</html>
