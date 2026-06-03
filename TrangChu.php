<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Vé Xem Phim Trực Tuyến</title>
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
                        <a href="TrangChu.php" class="nav-link active">Trang chủ</a>
                        <a href="VeDatVe.php" class="nav-link">Vé/Đặt vé</a>
                        <a href="HoatDong.php" class="nav-link">Hoạt động</a>
                        <a href="QuanTri.php" class="nav-link" id="navAdminLink" style="display:none">Quản trị</a>
                    </nav>
                <div class="header-right">
                    <div class="search-container">
                        <input type="text" class="search-input" placeholder="Tìm tên phim...">
                        <button class="search-btn">
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
    <section class="banner">
        <div class="banner-slider">
        </div>
        <div class="banner-dots"></div>
        <button class="banner-nav banner-prev"><i class="fas fa-chevron-left"></i></button>
        <button class="banner-nav banner-next"><i class="fas fa-chevron-right"></i></button>
    </section>
    <section class="movies-section">
        <div class="container">
            <h2 class="section-title">Phim Đang Chiếu</h2>        
            <div class="movies-grid">
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="heo5mong.jpg" alt="Heo 5 Móng">
                        <div class="movie-overlay">
                            <button class="btn-trailer">
                                <i class="fas fa-play"></i> Trailer
                            </button>
                        </div>
                        <span class="movie-rating">8.3</span>
                        <span class="movie-age">T18</span>
                    </div>
                    <h3 class="movie-title">Heo 5 Móng</h3>
                    <p class="movie-info">Kinh dị | 119 phút</p>
                    <button class="btn-book-movie">Mua vé</button>
                </div>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="trumso.webp" alt="Trùm Sò">
                        <div class="movie-overlay">
                            <button class="btn-trailer">
                                <i class="fas fa-play"></i> Trailer
                            </button>
                        </div>
                        <span class="movie-rating">8.6</span>
                        <span class="movie-age">K</span>
                    </div>
                    <h3 class="movie-title">Trùm Sò</h3>
                    <p class="movie-info">Hài | 105 phút</p>
                    <button class="btn-book-movie">Mua vé</button>
                </div>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="hq720.jpg" alt="Phí Phông: Quỷ Máu Rừng Thiêng">
                        <div class="movie-overlay">
                            <button class="btn-trailer">
                                <i class="fas fa-play"></i> Trailer
                            </button>
                        </div>
                        <span class="movie-rating">8.7</span>
                        <span class="movie-age">T16</span>
                    </div>
                    <h3 class="movie-title">Phí Phông: Quỷ Máu Rừng Thiêng</h3>
                    <p class="movie-info">Kinh dị | 128 phút</p>
                    <button class="btn-book-movie">Mua vé</button>
                </div>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="daitiectrangmau8.jpg" alt="Đại Tiệc Trăng Máu 8">
                        <div class="movie-overlay">
                            <button class="btn-trailer">
                                <i class="fas fa-play"></i> Trailer
                            </button>
                        </div>
                        <span class="movie-rating">8.2</span>
                        <span class="movie-age">T16</span>
                    </div>
                    <h3 class="movie-title">Đại Tiệc Trăng Máu 8</h3>
                    <p class="movie-info">Hành động | 135 phút</p>
                    <button class="btn-book-movie">Mua vé</button>
                </div>

                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="anhhung.jpg" alt="Anh Hùng">
                        <div class="movie-overlay">
                            <button class="btn-trailer">
                                <i class="fas fa-play"></i> Trailer
                            </button>
                        </div>
                        <span class="movie-rating">8.7</span>
                        <span class="movie-age">T13</span>
                    </div>
                    <h3 class="movie-title">Anh Hùng</h3>
                    <p class="movie-info">Tâm lý | 122 phút</p>
                    <button class="btn-book-movie">Mua vé</button>
                </div>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="mariothienha.jpg" alt="Super Mario Thiên Hà">
                        <div class="movie-overlay">
                            <button class="btn-trailer">
                                <i class="fas fa-play"></i> Trailer
                            </button>
                        </div>
                        <span class="movie-rating">8.1</span>
                        <span class="movie-age">K</span>
                    </div>
                    <h3 class="movie-title">Super Mario Thiên Hà</h3>
                    <p class="movie-info">Hoạt hình - Phiêu lưu | 99 phút</p>
                    <button class="btn-book-movie">Mua vé</button>
                </div>

                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="shincaubebutchi.jpg" alt="Shin Cậu Bé Búp Chì: Quả Trứng Vương Quốc">
                        <div class="movie-overlay">
                            <button class="btn-trailer">
                                <i class="fas fa-play"></i> Trailer
                            </button>
                        </div>
                        <span class="movie-rating">8.8</span>
                        <span class="movie-age">K</span>
                    </div>
                    <h3 class="movie-title">Shin Cậu Bé Búp Chì: Quả Trứng Vương Quốc</h3>
                    <p class="movie-info">Hài - Phiêu lưu | 110 phút</p>
                    <button class="btn-book-movie">Mua vé</button>
                </div>
                <div class="movie-card">
                    <div class="movie-poster">
                        <img src="gauboonie.jpg" alt="Gấu Boonie: Kungfu Ân Sĩ">
                        <div class="movie-overlay">
                            <button class="btn-trailer">
                                <i class="fas fa-play"></i> Trailer
                            </button>
                        </div>
                        <span class="movie-rating">8.3</span>
                        <span class="movie-age">T13</span>
                    </div>
                    <h3 class="movie-title">Gấu Boonie: Kungfu Ân Sĩ</h3>
                    <p class="movie-info">Phiêu lưu - Hài | 125 phút</p>
                    <button class="btn-book-movie">Mua vé</button>
                </div>
            </div>
        </div>
    </section>
    <section class="promotions-section">
        <div class="container">
            <h2 class="section-title">Ưu Đãi Đặc Biệt</h2>
            <div class="promotions-grid">
                <div class="promotion-card">
                    <i class="fas fa-gift"></i>
                    <h3>Khuyến Mãi Hàng Tuần</h3>
                    <p>Giảm giá vé vào các ngày tư, năm</p>
                </div>
                <div class="promotion-card">
                    <i class="fas fa-users"></i>
                    <h3>Combo Gia Đình</h3>
                    <p>Mua 4 vé, tiết kiệm đến 20%</p>
                </div>
                <div class="promotion-card">
                    <i class="fas fa-star"></i>
                    <h3>Hội Viên VIP</h3>
                    <p>Tích điểm mỗi lần mua vé</p>
                </div>
                <div class="promotion-card">
                    <i class="fas fa-heart"></i>
                    <h3>Ưu Đãi Sinh Nhật</h3>
                    <p>Nhận vé miễn phí nhân dịp sinh nhật</p>
                </div>
            </div>
        </div>
    </section>
    <section class="intro-section">
        <div class="container">
            <h2 class="section-title">Tại Sao Chọn Cinema Group 11?</h2>
            <div class="intro-grid">
                <div class="intro-card">
                    <div class="intro-icon">
                        <i class="fas fa-film"></i>
                    </div>
                    <h3>Phim Đa Dạng</h3>
                    <p>Cập nhật liên tục những bộ phim mới nhất, từ các tác phẩm kinh điển đến những bom tấn mới ra mắt.</p>
                </div>
                <div class="intro-card">
                    <div class="intro-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3>Đặt Vé Dễ Dàng</h3>
                    <p>Giao diện thân thiện, chỉ cần vài bước đơn giản để đặt vé xem phim yêu thích của bạn.</p>
                </div>
                <div class="intro-card">
                    <div class="intro-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Thanh Toán An Toàn</h3>
                    <p>Hỗ trợ nhiều phương thức thanh toán với bảo mật cao, đảm bảo an toàn cho mọi giao dịch.</p>
                </div>
                <div class="intro-card">
                    <div class="intro-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3>Ưu Đãi Thành Viên</h3>
                    <p>Thành viên VIP được hưởng nhiều ưu đãi độc quyền, giảm giá vé và khuyến mãi hấp dẫn.</p>
                </div>
                <div class="intro-card">
                    <div class="intro-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Chất Lượng Tuyệt Vời</h3>
                    <p>Công nghệ chiếu phim hiện đại, hệ thống âm thanh cao cấp mang đến trải nghiệm tuyệt vời.</p>
                </div>
                <div class="intro-card">
                    <div class="intro-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Hỗ Trợ 24/7</h3>
                    <p>Đội ngũ hỗ trợ khách hàng sẵn sàng giúp đỡ bạn bất kỳ lúc nào với mọi thắc mắc.</p>
                </div>
            </div>
        </div>
    </section>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>GIỚI THIỆU</h4>
                    <ul>
                        <li><a href="#">Về Chúng Tôi</a></li>
                        <li><a href="#">Thoả Thuận Sử Dụng</a></li>
                        <li><a href="#">Quy Chế Hoạt Động</a></li>
                        <li><a href="#">Chính Sách Bảo Mật</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>GÓC ĐIỆN ẢNH</h4>
                    <ul>
                        <li><a href="#">Thể Loại Phim</a></li>
                        <li><a href="#">Bình Luận Phim</a></li>
                        <li><a href="#">Blog Điện Ảnh</a></li>
                        <li><a href="#">Phim Hay Tháng</a></li>
                        <li><a href="#">Phim IMAX</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>HỖ TRỢ</h4>
                    <ul>
                        <li><a href="#">Góp Ý</a></li>
                        <li><a href="#">Sale & Services</a></li>
                        <li><a href="#">Rap / Giá Vé</a></li>
                        <li><a href="#">Tuyển Dụng</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>KẾT NỐI VỚI CHÚNG TÔI</h4>
                    <div class="social-links">
                        <a href="#" class="social-icon">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="#" class="social-icon">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="company-info">
                    <h4>ALL MEMBERS OF GROUP 11</h4>
                    <p>HOTLINE: +84 ******049 </p>
                    <p>Trường Đại học Quy Nhơn - 170 An Dương Vương, Quy Nhơn Nam, Gia Lai</p>
                    <p>Email: group11@itqnu.com</p>
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
                <p>Đăng nhập với tài khoản của bạn! Sau khi nhập mật khẩu, mã xác thực 6 số sẽ gửi qua Gmail.</p>
            </div>
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="loginEmail">Email / Tên đăng nhập</label>
                    <input type="text" id="loginEmail" name="loginEmail" placeholder="Email hoặc tên đăng nhập" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Mật khẩu</label>
                    <input type="password" id="loginPassword" name="loginPassword" placeholder="Mật khẩu" required>
                </div>
                <div class="form-footer">
                    <a href="#" class="forgot-password" id="openForgotPasswordBtn">Quên mật khẩu?</a>
                </div>
                <button type="submit" class="btn-submit">Đăng Nhập</button>
                <div class="social-login">
                    <p>Hoặc đăng nhập với</p>
                    <div class="social-buttons">
                        <button type="button" class="btn-social fb">
                            <i class="fab fa-facebook-f"></i>
                        </button>
                        <button type="button" class="btn-social gg">
                            <i class="fab fa-google"></i>
                        </button>
                    </div>
                </div>
                <p class="signup-link">Chưa có tài khoản? <a href="#" id="openSignupBtn">Đăng ký ngay</a></p>
            </form>
        </div>
    </div>
    <?php include 'otp-modal.php'; ?>
    <div id="signupModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2>Đăng Ký Tài Khoản</h2>
                <p>Đăng ký tài khoản thành viên và nhận ngay ưu đãi!</p>
            </div>
            <form class="signup-form" id="signupForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="signupName">Họ & tên</label>
                        <input type="text" id="signupName" name="signupName" placeholder="Họ và tên của bạn" required>
                    </div>
                    <div class="form-group">
                        <label for="signupEmail">Email</label>
                        <input type="email" id="signupEmail" name="signupEmail" placeholder="Email của bạn" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="signupAddress">Địa chỉ</label>
                        <input type="text" id="signupAddress" name="signupAddress" placeholder="Địa chỉ" required>
                    </div>
                    <div class="form-group">
                        <label for="signupUsername">Email / Tên đăng nhập</label>
                        <input type="text" id="signupUsername" name="signupUsername" placeholder="Tên đăng nhập" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="signupCCCD">Số CCCD</label>
                        <input type="text" id="signupCCCD" name="signupCCCD" placeholder="Số CCCD" required>
                    </div>
                    <div class="form-group">
                        <label for="signupPassword">Mật khẩu</label>
                        <input type="password" id="signupPassword" name="signupPassword" placeholder="Mật khẩu" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="signupBirthday">Ngày sinh</label>
                        <input type="date" id="signupBirthday" name="signupBirthday" required>
                    </div>
                    <div class="form-group">
                        <label for="signupConfirmPassword">Mật khẩu nhập lại</label>
                        <input type="password" id="signupConfirmPassword" name="signupConfirmPassword" placeholder="Nhập lại mật khẩu" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="signupPhone">Điện thoại</label>
                        <input type="tel" id="signupPhone" name="signupPhone" placeholder="Số điện thoại" required>
                    </div>
                    <div class="form-group">
                        <label>Giới tính</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="signupGender" value="male" checked>
                                Nam
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="signupGender" value="female">
                                Nữ
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Đăng Ký</button>
                <p class="login-link">Đã có tài khoản? <a href="#" id="openLoginBtn2">Đăng nhập</a></p>
            </form>
        </div>
    </div>
    <div id="searchResultsModal" class="modal">
        <div class="modal-content search-modal">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2>Kết Quả Tìm Kiếm</h2>
            </div>
            <div id="searchResults" class="search-results">
            </div>
        </div>
    </div>
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2>Quên Mật Khẩu</h2>
                <p>Nhập email của bạn để đặt lại mật khẩu</p>
            </div>
            <form class="forgot-password-form" id="forgotPasswordForm">
                <div class="form-group">
                    <label for="forgotEmail">Email / Tên đăng nhập</label>
                    <input type="text" id="forgotEmail" name="forgotEmail" placeholder="Email hoặc tên đăng nhập" required>
                </div>
                <button type="submit" class="btn-submit">Tiếp Tục</button>
                <p class="login-link">Quay lại <a href="#" id="openLoginBtn3">Đăng nhập</a></p>
            </form>
        </div>
    </div>
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2>Đặt Lại Mật Khẩu</h2>
                <p>Nhập mật khẩu mới của bạn</p>
            </div>
            <form class="reset-password-form" id="resetPasswordForm">
                <div class="form-group">
                    <label for="newPassword">Mật khẩu mới</label>
                    <input type="password" id="newPassword" name="newPassword" placeholder="Nhập mật khẩu mới" required>
                </div>
                <div class="form-group">
                    <label for="confirmNewPassword">Xác nhận mật khẩu</label>
                    <input type="password" id="confirmNewPassword" name="confirmNewPassword" placeholder="Nhập lại mật khẩu mới" required>
                </div>
                <button type="submit" class="btn-submit">Xác Nhận</button>
                <p class="login-link">Quay lại <a href="#" id="openLoginBtn4">Đăng nhập</a></p>
            </form>
        </div>
    </div>

    <div id="bookingModal" class="modal">
        <div class="modal-content booking-modal">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2>Chọn Ghế - <span id="bookingMovieName">Phim</span></h2>
            </div>
            <div class="booking-container">
                <div class="booking-section">
                    <h3>Chọn Ngày Chiếu</h3>
                    <div class="date-picker">
                        <input type="date" id="showDate" min="" class="date-input">
                    </div>
                </div>
                <div class="booking-section">
                    <h3>Chọn Giờ Chiếu</h3>
                    <div id="showtimeList" class="showtime-list">
                        <p style="color: #999;">Vui lòng chọn ngày để xem giờ chiếu</p>
                    </div>
                </div>
                <div class="booking-section">
                    <h3>Chọn Ghế</h3>
                    <div class="screen-label">MÀN HÌNH</div>
                    <div id="seatMap" class="seat-map">
                    </div>
                    <div class="seat-legend">
                        <div class="legend-item">
                            <div class="seat seat-available"></div>
                            <span>Ghế thường</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat seat-selected"></div>
                            <span>Ghế đã chọn</span>
                        </div>
                        <div class="legend-item">
                            <div class="seat seat-booked"></div>
                            <span>Ghế đã bán</span>
                        </div>
                    </div>
                </div>
                <div class="booking-info">
                    <div class="info-row">
                        <span>Số lượng ghế:</span>
                        <span id="selectedCount">0</span>
                    </div>
                    <div class="info-row">
                        <span>Đơn giá:</span>
                        <span>50.000 VND</span>
                    </div>
                    <div class="info-row total">
                        <span>Tổng tiền:</span>
                        <span id="totalPrice">0 VND</span>
                    </div>
                </div>
                <button class="btn-submit" id="confirmBooking" style="width: 100%; margin-top: 20px;">
                    Xác Nhận Đặt Vé
                </button>
            </div>
        </div>
    </div>
    <script src="auth-login.js"></script>
    <script src="script.js"></script>
    <script src="load-movies.js"></script>
    <script src="banner-slider.js"></script>
</body>
</html>
