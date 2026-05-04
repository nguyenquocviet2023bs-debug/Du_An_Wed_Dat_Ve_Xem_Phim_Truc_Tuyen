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
    <header class="header">
        <div class="container">
            <div class="header-top">
                <div class="logo">
                    <i class="fas fa-film"></i>
                    <span>Cinema Group 11</span>
                </div>
                <nav class="navbar">
                    <a href="#" class="nav-link active">Mua Vé</a>
                    <a href="#" class="nav-link">Phim</a>
                    <a href="#" class="nav-link">Rạp</a>
                    <a href="#" class="nav-link">Rap/Giá Vé</a>
                </nav>
                <div class="header-right">
                    <button class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="login-btn">Đăng Nhập</button>
                    <div class="vip-badge">
                        <i class="fas fa-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="banner">
        <div class="banner-content">
            <div class="banner-image">
                <img src="./hq720.jpg" alt="Movie Banner" style="width: 100%; height: 100%;">
            </div>
            <div class="banner-overlay"></div>
            <div class="banner-text">
                <h2>PHÍ PHÔNG</h2>
                <p>QUỶ MÁU RỪNG THIÊNG</p>
                <p class="banner-date">SUẤT CHIẾU ĐẶC BIỆT từ 18H ngày 16.04.2026</p>
                <button class="btn-book-now">ĐẶT VÉ NGAY</button>
            </div>
        </div>
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
                    <p class="movie-info">Hài | 119 phút</p>
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
    <script src="script.js"></script>
</body>
</html>
