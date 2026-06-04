<?php
session_start();

if (!isset($_SESSION['user_phone'])) {
    header('Location: TrangChu.php');
    exit;
}

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
    echo "<h1 style='text-align:center; color:red; margin-top:100px;'>⛔ TRUY CẬP BỊ TỪ CHỐI</h1>";
    echo "<p style='text-align:center;'>Bạn không có quyền truy cập trang quản trị.</p>";
    echo "<p style='text-align:center;'><a href='TrangChu.php' style='color:blue;'>← Quay về trang chủ</a></p>";
    echo "</body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔐 Quản Trị - Cinema Group 11</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    <style>
        body {
            background: #1a1a1a !important;
            color: #e0e0e0 !important;
        }

        .navbar,
        nav.navbar,
        .search-container,
        .banner,
        section.banner,
        .banner-slider,
        .vip-badge {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        .header {
            background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%) !important;
            border-bottom: 2px solid #ff6b35 !important;
        }

        .logo {
            color: #ff6b35 !important;
            font-weight: 700 !important;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: rgba(255, 107, 53, 0.1);
            border-radius: 8px;
            font-size: 14px;
        }

        .admin-user-info i {
            color: #ff6b35;
        }

        .login-btn {
            background: #ff6b35;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .login-btn:hover {
            background: #e55a28;
            transform: translateY(-2px);
        }

        .vip-badge {
            display: none;
        }

        .admin-section {
            background: #1a1a1a;
            padding: 40px 0;
        }

        .section-title {
            color: #ff6b35;
            font-size: 32px;
            text-align: center;
            margin-bottom: 10px;
        }

        .admin-intro {
            text-align: center;
            color: #999;
            margin-bottom: 30px;
        }

        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            justify-content: center;
            background: #2c2c2c;
            padding: 15px;
            border-radius: 12px;
        }

        .admin-tab {
            background: transparent;
            color: #999;
            border: 2px solid transparent;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .admin-tab:hover {
            color: #ff6b35;
            background: rgba(255, 107, 53, 0.1);
        }

        .admin-tab.active {
            background: #ff6b35;
            color: white;
            border-color: #ff6b35;
        }

        .admin-panel {
            display: none;
            background: #2c2c2c;
            padding: 30px;
            border-radius: 12px;
            min-height: 400px;
        }

        .admin-panel.active {
            display: block;
        }

        .admin-panel h3 {
            color: #ff6b35;
            font-size: 24px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-loading {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 16px;
        }

        .admin-error {
            text-align: center;
            padding: 40px 20px;
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            border-radius: 8px;
        }

        .admin-table-wrap {
            overflow-x: auto;
            margin-top: 20px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            background: #1a1a1a;
            border-radius: 8px;
            overflow: hidden;
        }

        .admin-table thead {
            background: #333;
        }

        .admin-table th {
            padding: 15px;
            text-align: left;
            color: #ff6b35;
            font-weight: 600;
            border-bottom: 2px solid #ff6b35;
        }

        .admin-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #333;
            color: #e0e0e0;
        }

        .admin-table tbody tr:hover {
            background: rgba(255, 107, 53, 0.05);
        }

        .admin-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .admin-badge.role-admin {
            background: #ff6b35;
            color: white;
        }

        .admin-badge.role-user {
            background: #4a9eff;
            color: white;
        }

        .admin-badge.locked {
            background: #dc3545;
            color: white;
        }

        .admin-badge.active-u {
            background: #28a745;
            color: white;
        }

        .btn-admin-action {
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            background: #ff6b35;
            color: white;
        }

        .btn-admin-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }

        .btn-admin-action.secondary {
            background: #4a9eff;
        }

        .btn-admin-action:disabled {
            background: #555;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .admin-stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .admin-stat-card {
            background: #1a1a1a;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            border: 2px solid #333;
            transition: all 0.3s;
        }

        .admin-stat-card:hover {
            border-color: #ff6b35;
            transform: translateY(-5px);
        }

        .admin-stat-card span {
            display: block;
            color: #999;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .admin-stat-card strong {
            display: block;
            color: #ff6b35;
            font-size: 28px;
            font-weight: 700;
        }

        .footer {
            background: #0f0f0f;
            color: #666;
            border-top: 2px solid #333;
        }

        .footer-logo {
            color: #ff6b35;
        }

        .movie-card {
            background: #1a1a1a;
            border: 1px solid #333;
        }

        .movie-card:hover {
            border-color: #ff6b35;
        }

        .movie-no-image {
            color: #555;
        }

        .revenue-chart-container {
            background: #1a1a1a;
            border: 1px solid #333;
        }

        .modal-content {
            background: #2c2c2c;
            color: #e0e0e0;
        }

        .modal-header h2 {
            color: #ff6b35;
        }

        .admin-form input,
        .admin-form textarea,
        .admin-form select {
            background: #1a1a1a;
            color: #e0e0e0;
            border-color: #444;
        }

        .admin-form input:focus,
        .admin-form textarea:focus,
        .admin-form select:focus {
            border-color: #ff6b35;
            outline: none;
        }

        .btn-submit {
            background: #ff6b35;
        }

        .btn-submit:hover {
            background: #e55a28;
        }

        .date-selects {
            display: flex;
            gap: 8px;
        }
        .date-selects select.date-select {
            flex: 1;
            padding: 10px;
            border: 2px solid #444;
            border-radius: 8px;
            background: #2c2c2c;
            color: #e0e0e0;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
            cursor: pointer;
        }
        .date-selects select.date-select:focus {
            border-color: #ff6b35;
        }
        .date-selects select.date-select option {
            background: #2c2c2c;
            color: #e0e0e0;
        }

        select.form-select {
            width: 100%;
            padding: 10px;
            border: 2px solid #444;
            border-radius: 8px;
            background: #2c2c2c;
            color: #e0e0e0;
            font-size: 14px;
            outline: none;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        select.form-select:focus {
            border-color: #ff6b35;
        }
        select.form-select option {
            background: #2c2c2c;
            color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div id="mainContent" class="main-content">
        <header class="header modern-header admin-header">
            <div class="container">
                <div class="header-top">
                    <div class="logo modern-logo admin-logo">
                        <i class="fas fa-user-shield"></i>
                        <span>Cinema Group 11 - Admin Panel</span>
                    </div>
                    
                    <button class="hamburger-menu" id="hamburgerBtn" aria-label="Toggle Menu">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>

                    <nav class="navbar modern-navbar admin-navbar">
                        <a href="TrangChu.php" class="nav-link modern-nav-link" data-text="Trang chủ">
                            <span class="nav-icon"><i class="fas fa-home"></i></span>
                            <span class="nav-text">Trang chủ</span>
                        </a>
                        <a href="VeDatVe.php" class="nav-link modern-nav-link" data-text="Vé/Đặt vé">
                            <span class="nav-icon"><i class="fas fa-ticket-alt"></i></span>
                            <span class="nav-text">Vé/Đặt vé</span>
                        </a>
                        <a href="HoatDong.php" class="nav-link modern-nav-link" data-text="Hoạt động">
                            <span class="nav-icon"><i class="fas fa-history"></i></span>
                            <span class="nav-text">Hoạt động</span>
                        </a>
                        <a href="QuanTri.php" class="nav-link modern-nav-link active admin-link" data-text="Quản trị">
                            <span class="nav-icon"><i class="fas fa-user-shield"></i></span>
                            <span class="nav-text">Quản trị</span>
                        </a>
                    </nav>

                    <div class="header-right modern-header-right">
                        <div class="admin-user-info">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <button class="login-btn modern-btn" id="logoutBtn" type="button">
                            <span class="btn-icon"><i class="fas fa-sign-out-alt"></i></span>
                            <span class="btn-text">Đăng xuất</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="mobile-overlay" id="mobileOverlay"></div>

        <section class="admin-section">
            <div class="container">
                <h2 class="section-title">Bảng quản trị</h2>
                <p class="admin-intro">
                    Quản lý hệ thống đặt vé xem phim trực tuyến
                </p>

                <div class="admin-tabs" role="tablist">
                    <button type="button" class="admin-tab active" data-tab="movies">
                        <i class="fas fa-film"></i> Quản lý phim
                    </button>
                    <button type="button" class="admin-tab" data-tab="revenue">
                        <i class="fas fa-chart-pie"></i> Doanh thu
                    </button>
                    <button type="button" class="admin-tab" data-tab="activities">
                        <i class="fas fa-history"></i> Hoạt động
                    </button>
                    <button type="button" class="admin-tab" data-tab="users">
                        <i class="fas fa-users"></i> Tài khoản
                    </button>
                </div>

                <div id="panel-movies" class="admin-panel active">
                    <h3><i class="fas fa-film"></i> Quản lý phim</h3>
                    <button type="button" class="btn-admin-add" id="btnAddMovie">
                        <i class="fas fa-plus"></i> Thêm phim mới
                    </button>
                    <div id="moviesContent" class="admin-loading">Đang tải…</div>
                </div>

                <div id="panel-revenue" class="admin-panel">
                    <h3><i class="fas fa-chart-pie"></i> Thống kê doanh thu theo tháng</h3>
                    <div class="revenue-filter">
                        <label>Chọn tháng:
                            <select id="revenueMonth" class="admin-select">
                                <option value="1">Tháng 1</option>
                                <option value="2">Tháng 2</option>
                                <option value="3">Tháng 3</option>
                                <option value="4">Tháng 4</option>
                                <option value="5">Tháng 5</option>
                                <option value="6">Tháng 6</option>
                                <option value="7">Tháng 7</option>
                                <option value="8">Tháng 8</option>
                                <option value="9">Tháng 9</option>
                                <option value="10">Tháng 10</option>
                                <option value="11">Tháng 11</option>
                                <option value="12">Tháng 12</option>
                            </select>
                        </label>
                        <label>Chọn năm:
                            <select id="revenueYear" class="admin-select"></select>
                        </label>
                        <button type="button" class="btn-admin-filter" id="btnFilterRevenue">
                            <i class="fas fa-search"></i> Xem thống kê
                        </button>
                    </div>
                    <div id="revenueContent" class="admin-loading">Chọn tháng/năm để xem thống kê.</div>
                </div>

                <div id="panel-activities" class="admin-panel">
                    <h3><i class="fas fa-history"></i> Lịch sử hoạt động người dùng</h3>
                    <div id="activitiesContent" class="admin-loading">Chọn tab để tải dữ liệu.</div>
                </div>

                <div id="panel-users" class="admin-panel">
                    <h3><i class="fas fa-users"></i> Quản lý tài khoản người dùng</h3>
                    <p class="admin-intro" style="margin-top:-8px; text-align: left;">Khóa/mở khóa tài khoản người dùng. Tài khoản bị khóa không thể đăng nhập.</p>
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

    <div id="movieModal" class="modal">
        <div class="modal-content modal-large">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2 id="movieModalTitle">Thêm phim mới</h2>
            </div>
            <form id="movieForm" class="admin-form">
                <input type="hidden" id="movieId" name="movie_id">
                <div class="form-group">
                    <label for="movieName">Tên phim <span class="required">*</span></label>
                    <input type="text" id="movieName" name="ten_phim" required placeholder="Nhập tên phim">
                </div>
                <div class="form-group">
                    <label for="movieDesc">Mô tả</label>
                    <textarea id="movieDesc" name="mo_ta" rows="4" placeholder="Mô tả nội dung phim"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="movieGenre">Thể loại</label>
                        <input type="text" id="movieGenre" name="the_loai" placeholder="Hành động, Kinh dị, Hài,...">
                    </div>
                    <div class="form-group">
                        <label for="movieDirector">Đạo diễn</label>
                        <input type="text" id="movieDirector" name="dao_dien" placeholder="Tên đạo diễn">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="movieDuration">Thời lượng (phút)</label>
                        <input type="number" id="movieDuration" name="thoi_luong" min="0" placeholder="120">
                    </div>
                    <div class="form-group">
                        <label>Ngày khởi chiếu</label>
                        <div class="date-selects">
                            <select id="movieReleaseDay" class="date-select">
                                <option value="">Ngày</option>
                                <option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>
                            </select>
                            <select id="movieReleaseMonth" class="date-select">
                                <option value="">Tháng</option>
                                <option value="01">1</option><option value="02">2</option><option value="03">3</option><option value="04">4</option><option value="05">5</option><option value="06">6</option><option value="07">7</option><option value="08">8</option><option value="09">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option>
                            </select>
                            <select id="movieReleaseYear" class="date-select">
                                <option value="">Năm</option>
                                <option value="2024">2024</option><option value="2025">2025</option><option value="2026">2026</option><option value="2027">2027</option><option value="2028">2028</option><option value="2029">2029</option><option value="2030">2030</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="movieAgeRating">Giới hạn độ tuổi</label>
                        <select id="movieAgeRating" name="gioi_han_do_tuoi" class="form-select">
                            <option value="K">K (Không giới hạn)</option>
                            <option value="T13">T13 (13+)</option>
                            <option value="T16">T16 (16+)</option>
                            <option value="T18">T18 (18+)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label> </label>
                        <div style="height:1px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="movieImage">Link ảnh phim</label>
                    <input type="url" id="movieImage" name="hinh_anh_url" placeholder="https://example.com/image.jpg">
                    <small class="form-help">Nhập URL hình ảnh từ internet</small>
                </div>
                <div id="movieImagePreview" style="display:none; margin-top:10px;">
                    <img id="movieImagePreviewImg" src="" alt="Preview" style="max-width:200px; border-radius:8px;">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Lưu phim
                </button>
            </form>
        </div>
    </div>

    <!-- modern-decoration.js removed (user requested) -->
    <script src="admin.js"></script>
    <script>
        (function() {
            console.log('%c🔐 TRANG QUẢN TRỊ - Admin Panel Loaded', 'color: #ff6b35; font-size: 16px; font-weight: bold;');
            console.log('User:', '<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?>');
            console.log('Role:', '<?php echo htmlspecialchars($_SESSION['role'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?>');
            
            function removeUnwantedElements() {
                const selectors = [
                    '.navbar',
                    'nav.navbar',
                    '.search-container', 
                    '.banner',
                    'section.banner',
                    '.banner-slider',
                    '.vip-badge'
                ];
                
                selectors.forEach(function(selector) {
                    const elements = document.querySelectorAll(selector);
                    if (elements.length > 0) {
                        console.warn('⚠️ Phát hiện element không mong muốn:', selector, '- Đang xóa...');
                        elements.forEach(function(el) {
                            el.remove();
                        });
                    }
                });
            }
            
            removeUnwantedElements();
            setTimeout(removeUnwantedElements, 100);
            
            const observer = new MutationObserver(function() {
                removeUnwantedElements();
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            console.log('✅ Trang quản trị đã được tải thành công!');
        })();
    </script>
</body>
</html>
