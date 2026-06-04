SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_phone` varchar(20) NOT NULL,
  `ten_phim_dat` varchar(255) NOT NULL,
  `ngay_chieu` date DEFAULT NULL,
  `gio_chieu` time DEFAULT NULL,
  `so_ghe` varchar(10) NOT NULL,
  `gia_ve` decimal(10,2) DEFAULT 0.00,
  `ngay_dat_ve` timestamp NOT NULL DEFAULT current_timestamp(),
  `so_lan_sua` int(11) DEFAULT 0 COMMENT 'Số lần user đã chỉnh sửa vé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bookings` (`id`, `user_phone`, `ten_phim_dat`, `ngay_chieu`, `gio_chieu`, `so_ghe`, `gia_ve`, `ngay_dat_ve`, `so_lan_sua`) VALUES
(6, '0123456789', 'Phí Phông: Quỷ Máu Rừng Thiêng', '2026-05-26', '11:30:00', 'C3', 50000.00, '2026-05-25 03:19:46', 0),
(7, '0123456789', 'Phí Phông: Quỷ Máu Rừng Thiêng', '2026-05-25', '11:30:00', 'C4', 50000.00, '2026-05-25 03:20:23', 0),
(11, '0347996049', 'Phí Phông: Quỷ Máu Rừng Thiêng', '2026-06-04', '14:30:00', 'D4, D5', 100000.00, '2026-06-03 02:33:50', 1);

CREATE TABLE `movies` (
  `id` int(11) NOT NULL,
  `ten_phim` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `the_loai` varchar(100) NOT NULL,
  `dao_dien` varchar(100) DEFAULT NULL,
  `thoi_luong` int(11) DEFAULT NULL,
  `hinh_anh_url` varchar(500) DEFAULT NULL,
  `ngay_khoi_chieu` date DEFAULT NULL,
  `diem_danh_gia` decimal(3,1) DEFAULT NULL,
  `gioi_han_do_tuoi` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `movies` (`id`, `ten_phim`, `mo_ta`, `the_loai`, `dao_dien`, `thoi_luong`, `hinh_anh_url`, `ngay_khoi_chieu`, `diem_danh_gia`, `gioi_han_do_tuoi`, `created_at`, `updated_at`) VALUES
(1, 'Anh Hùng', NULL, 'Tâm lý', NULL, 122, 'anhhung.jpg', NULL, 8.7, 'T13', '2026-05-14 01:36:49', '2026-06-03 07:59:41'),
(2, 'Gấu Boonie: Kungfu Ân Sĩ', NULL, 'Phiêu lưu - Hài', NULL, 125, 'gauboonie.jpg', NULL, 8.3, 'T13', '2026-05-14 01:36:49', '2026-06-03 07:59:41'),
(3, 'Heo 5 Móng', NULL, 'Kinh dị', NULL, 119, 'heo5mong.jpg', NULL, 8.3, 'T18', '2026-05-14 01:36:49', '2026-06-03 07:59:41'),
(4, 'Phí Phông: Quỷ Máu Rừng Thiêng', NULL, 'Kinh dị', NULL, 128, 'hq720.jpg', NULL, 8.7, 'T16', '2026-05-14 01:36:49', '2026-06-03 07:59:41'),
(5, 'Shin Cậu Bé Búp Chì: Quả Trứng Vương Quốc', NULL, 'Hài - Phiêu lưu', NULL, 110, 'shincaubebutchi.jpg', NULL, 8.8, 'K', '2026-05-14 01:36:49', '2026-06-03 07:59:41'),
(6, 'Super Mario Thiên Hà', NULL, 'Hoạt hình - Phiêu lưu', NULL, 99, 'mariothienha.jpg', NULL, 8.1, 'K', '2026-05-14 01:36:49', '2026-06-03 07:59:41'),
(7, 'Trùm Sò', NULL, 'Hài', NULL, 105, 'trumso.webp', NULL, 8.6, 'K', '2026-05-14 01:36:49', '2026-06-03 07:59:41'),
(8, 'Đại Tiệc Trăng Máu 8', NULL, 'Hành động', NULL, 135, 'daitiectrangmau8.jpg', NULL, 8.2, 'T16', '2026-05-14 01:36:49', '2026-06-03 07:59:41'),
(9, 'Ốc Mượn Hồn', 'Một người chồng đau khổ khi vợ qua đời trong tai nạn. Hạnh phúc tưởng chừng được hồi sinh khi linh hồn vợ anh trở về trong thân xác người khác...', 'Tâm lý - Kinh dị', 'Đinh Tuấn Vũ', 120, 'ocmuonhon.jpg', '2026-06-05', 8.5, 'T16', '2026-06-04 00:00:00', '2026-06-04 00:00:00');

CREATE TABLE `users` (
  `dien_thoai` varchar(20) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `ngay_sinh` date NOT NULL,
  `gioi_tinh` enum('Nam','Nữ','Khác') DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `vai_tro` varchar(20) NOT NULL DEFAULT 'user' COMMENT 'user | admin',
  `tai_khoan_bi_khoa` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=hoat dong, 1=bi khoa',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`dien_thoai`, `ho_ten`, `email`, `dia_chi`, `ngay_sinh`, `gioi_tinh`, `mat_khau`, `vai_tro`, `tai_khoan_bi_khoa`, `created_at`) VALUES
('0123456789', 'user1', 'user1@gmail.com', '170 An Dương Vương, Quy Nhơn', '2000-01-01', '', '$2y$10$945J922Ft0Cm4yeGsHKmkeZZs4lNCVsHW6JvBbDZLoibjbI0uzGu.', 'user', 1, '2026-05-14 01:40:54'),
('0347996049', 'Nguyễn Quốc Việt', 'nguyenquocviet2023bs@gmail.com', NULL, '0000-00-00', NULL, '$2y$10$wo5i/aQP9Mm578g2hViWNOO8qcDKwtgVfrqhVbwxNipOlTzpT5jCC', 'user', 0, '2026-05-19 02:48:35'),
('0999999001', 'Quản trị viên', 'admin1@admin.com', NULL, '0000-00-00', NULL, '$2y$10$zoRbHWFb2EGWoW6DbQGG1Oinl/y6Wlf.UMkixQXPnTFDfwkArNDgi', 'admin', 0, '2026-05-26 23:06:09');

CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL,
  `user_phone` varchar(20) NOT NULL,
  `loai_hoat_dong` varchar(50) NOT NULL,
  `noi_dung` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `user_activities` (`id`, `user_phone`, `loai_hoat_dong`, `noi_dung`, `created_at`) VALUES
(1, '0347996049', 'dat_ve', 'Đặt vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Ghế: C4, C6 - Suất: 23/05/2026 09:00 - 100,000 VND', '2026-05-22 09:31:57'),
(2, '0347996049', 'het_han', 'Vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" (suất 23/05/2026 09:00, ghế C4, C6) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-05-23 11:47:06'),
(3, '0347996049', 'dat_ve', 'Đặt vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Ghế: E5 - Suất: 24/05/2026 14:30 - 50.000 VND.', '2026-05-23 11:51:38'),
(4, '0347996049', 'dat_ve', 'Đặt vé phim \"Heo 5 Móng\" - Ghế: A3 - Suất: 24/05/2026 11:30 - 50.000 VND.', '2026-05-23 11:52:15'),
(5, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-23 12:05:05'),
(6, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-25 03:18:07'),
(7, '0347996049', 'het_han', 'Vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" (suất 24/05/2026 14:30, ghế E5) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-05-25 03:18:10'),
(8, '0347996049', 'het_han', 'Vé phim \"Heo 5 Móng\" (suất 24/05/2026 11:30, ghế A3) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-05-25 03:18:10'),
(9, '0347996049', 'dat_ve', 'Đặt vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Ghế: C4 - Suất: 26/05/2026 11:30 - 50.000 VND.', '2026-05-25 03:18:28'),
(10, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-05-25 03:18:35'),
(11, '0123456789', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-25 03:19:14'),
(12, '0123456789', 'dat_ve', 'Đặt vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Ghế: C3 - Suất: 25/05/2026 11:30 - 50.000 VND.', '2026-05-25 03:19:46'),
(13, '0123456789', 'sua_ve', 'Cập nhật vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Suất mới: 26/05/2026 11:30 - Ghế: C3.', '2026-05-25 03:19:56'),
(14, '0123456789', 'dat_ve', 'Đặt vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Ghế: C4 - Suất: 25/05/2026 11:30 - 50.000 VND.', '2026-05-25 03:20:23'),
(15, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-25 03:27:01'),
(16, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-25 03:30:18'),
(17, '0347996049', 'sua_ve', 'Cập nhật vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Suất mới: 26/05/2026 11:30 - Ghế: C5.', '2026-05-25 03:31:03'),
(18, '0347996049', 'sua_ve', 'Cập nhật vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Suất mới: 26/05/2026 11:30 - Ghế: C4.', '2026-05-25 03:31:16'),
(19, '0347996049', 'sua_ve', 'Cập nhật vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Suất mới: 26/05/2026 11:30 - Ghế: C4.', '2026-05-25 03:31:19'),
(20, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-25 03:33:27'),
(21, '0347996049', 'sua_ve', 'Cập nhật vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Suất mới: 26/05/2026 11:30 - Ghế: C4.', '2026-05-25 03:33:51'),
(22, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-26 03:42:53'),
(23, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-05-26 23:20:15'),
(24, '0999999001', 'quan_tri', 'Khóa tài khoản SĐT 0123456789 bởi quản trị viên.', '2026-05-26 23:21:39'),
(25, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-05-26 23:21:48'),
(26, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-26 23:22:51'),
(27, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-05-26 23:23:07'),
(28, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-05-26 23:23:25'),
(29, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-27 02:54:10'),
(30, '0347996049', 'dat_ve', 'Đặt vé phim \"Heo 5 Móng\" - Ghế: D4, D5 - Suất: 28/05/2026 11:30 - 100.000 VND.', '2026-05-27 02:54:58'),
(31, '0347996049', 'het_han', 'Vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" (suất 26/05/2026 11:30, ghế C4) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-05-27 02:55:01'),
(32, '0347996049', 'dat_ve', 'Đặt vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Ghế: D4, D5 - Suất: 28/05/2026 14:30 - 100.000 VND.', '2026-05-27 02:55:51'),
(33, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-05-27 03:01:11'),
(34, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-05-27 03:03:01'),
(35, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-05-27 03:04:05'),
(36, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-02 02:13:52'),
(37, '0347996049', 'het_han', 'Vé phim \"Heo 5 Móng\" (suất 28/05/2026 11:30, ghế D4, D5) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-06-02 02:13:58'),
(38, '0347996049', 'het_han', 'Vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" (suất 28/05/2026 14:30, ghế D4, D5) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-06-02 02:13:58'),
(39, '0347996049', 'dat_ve', 'Đặt vé phim \"Heo 5 Móng\" - Ghế: D5 - Suất: 03/06/2026 11:30 - 50.000 VND.', '2026-06-02 02:14:14'),
(40, '0347996049', 'sua_ve', 'Cập nhật vé phim \"Heo 5 Móng\" - Suất mới: 03/06/2026 11:30 - Ghế: D5.', '2026-06-02 02:14:28'),
(41, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-02 02:14:39'),
(42, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-02 02:28:11'),
(43, '0999999001', 'quan_tri', 'Mở khóa tài khoản SĐT 0123456789 bởi quản trị viên.', '2026-06-02 02:28:45'),
(44, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-02 02:29:10'),
(45, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-03 02:33:15'),
(46, '0347996049', 'dat_ve', 'Đặt vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Ghế: D4, D5 - Suất: 04/06/2026 14:30 - 100.000 VND.', '2026-06-03 02:33:50'),
(47, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 02:34:03'),
(48, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-03 02:34:16'),
(49, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-03 07:01:23'),
(50, '0347996049', 'het_han', 'Vé phim \"Heo 5 Móng\" (suất 03/06/2026 11:30, ghế D5) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-06-03 07:01:48'),
(51, '0347996049', 'sua_ve', 'Cập nhật vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" - Suất mới: 04/06/2026 14:30 - Ghế: D4, D5 (Lần sửa thứ 1).', '2026-06-03 07:01:58'),
(52, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 07:02:18'),
(53, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-03 07:02:31'),
(54, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 07:21:02'),
(55, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-03 07:21:36'),
(56, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 07:23:25'),
(57, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-03 07:23:39'),
(58, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 07:26:35'),
(59, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-03 07:26:48'),
(60, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-03 07:35:06'),
(61, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 07:38:25'),
(62, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-03 07:38:47'),
(63, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 08:15:57'),
(64, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-03 08:16:27'),
(65, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 08:17:04'),
(66, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-03 08:17:16'),
(67, '0999999001', 'quan_tri', 'Khóa tài khoản SĐT 0123456789 bởi quản trị viên.', '2026-06-03 08:18:27'),
(68, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-03 08:18:50'),
(69, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-03 08:19:33');

ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_phone` (`user_phone`),
  ADD KEY `fk_movie_name` (`ten_phim_dat`);

ALTER TABLE `movies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ten_phim` (`ten_phim`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`dien_thoai`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_vai_tro` (`vai_tro`),
  ADD KEY `idx_tai_khoan_bi_khoa` (`tai_khoan_bi_khoa`);

ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_phone` (`user_phone`),
  ADD KEY `idx_created_at` (`created_at`);

ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_movie_name` FOREIGN KEY (`ten_phim_dat`) REFERENCES `movies` (`ten_phim`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_phone` FOREIGN KEY (`user_phone`) REFERENCES `users` (`dien_thoai`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
