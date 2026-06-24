SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `movie_booking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `movie_booking`;

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_phone` varchar(20) NOT NULL,
  `ten_phim_dat` varchar(255) NOT NULL,
  `ngay_chieu` date DEFAULT NULL,
  `gio_chieu` time DEFAULT NULL,
  `so_ghe` varchar(10) NOT NULL,
  `gia_ve` decimal(10,2) DEFAULT 0.00,
  `ngay_dat_ve` timestamp NOT NULL DEFAULT current_timestamp(),
  `so_lan_sua` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bookings` (`id`, `user_phone`, `ten_phim_dat`, `ngay_chieu`, `gio_chieu`, `so_ghe`, `gia_ve`, `ngay_dat_ve`, `so_lan_sua`) VALUES
(6, '0123456789', 'Phí Phông: Quỷ Máu Rừng Thiêng', '2026-05-26', '11:30:00', 'C3', 50000.00, '2026-05-25 03:19:46', 0),
(7, '0123456789', 'Phí Phông: Quỷ Máu Rừng Thiêng', '2026-05-25', '11:30:00', 'C4', 50000.00, '2026-05-25 03:20:23', 0),
(13, '0347996049', 'Ốc Mượn Hồn', '2026-06-24', '17:10:00', 'C4, C5', 100000.00, '2026-06-24 09:37:39', 1),
(14, '0347996049', 'Ốc Mượn Hồn', '2026-06-24', '17:10:00', 'B4', 50000.00, '2026-06-24 09:54:50', 1);

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
(4, 'Phí Phông: Quỷ Máu Rừng Thiêng', '', 'Kinh dị', '', 128, 'https://cinestar.com.vn/_next/image/?url=https:%2F%2Fapi-website.cinestar.com.vn%2Fmedia%2Fwysiwyg%2FPosters%2F04-2026%2Fphi-phong-teaser.jpg&w=3840&q=75', '2026-04-24', 8.7, 'T16', '2026-05-14 01:36:49', '2026-06-24 10:30:45'),
(9, 'Tạm biệt Gohan (K)', 'Suốt mười năm đằng đẵng, chú chó hoang lông trắng với chiếc mũi đỏ mang tên \'GOHAN\' cứ thế phiêu dạt giữa cuộc đời, ôm trọn những ký ức chẳng thể phai...', 'Tình cảm', 'Chayanop Boonprakob - Baz Poonpiriya - Atta Hemwadee', 140, 'https://cinestar.com.vn/_next/image/?url=https%3A%2F%2Fapi-website.cinestar.com.vn%2Fmedia%2Fwysiwyg%2FPosters%2F05-2026%2Ftam-biet-gohan.jpg&w=1920&q=75', '2026-05-15', NULL, NULL, '2026-06-04 13:17:07', '2026-06-04 13:25:31'),
(10, 'Ma xó', 'Trong cái nghèo cùng cực và nỗi sợ mất con sau một lần sảy thai, cuộc sống của vợ chồng Phú và Thảo (đang mang thai) trở nên tăm tối hơn bao giờ hết k...', 'Kinh dị', 'Phan Bá Hỷ', 102, 'https://asset-cdn.yeah1.com/images/MX_TEASER_POSTER_SOCIAL_be27d9cf05.jpg', '2026-06-05', NULL, 'T16', '2026-06-04 13:33:09', '2026-06-04 15:01:21'),
(12, 'Ngôi đền kì quái 5', 'Thương hiệu Kinh dị - Hài Thái Lan ăn khách nhất đã trở lại. Một năm sau khi đánh bại hồn ma Nak Tinn, nhóm bạn của Balloon và First chưa kịp tận hưởn...', 'Kinh dị', 'Phontharis Chotkijsadarsopon', 118, 'https://cinestar.com.vn/_next/image/?url=https%3A%2F%2Fapi-website.cinestar.com.vn%2Fmedia%2Fwysiwyg%2FPosters%2F05-2026%2Fpeenak.jpg&w=1920&q=75', '2026-05-29', NULL, 'T16', '2026-06-04 13:52:12', '2026-06-04 13:52:12'),
(13, 'Doraemon: Nobita và lâu đài dưới đáy biển', 'Bước vào kì nghỉ hè, Nobita và các bạn tranh cãi chí chóe về địa điểm cắm trại. Theo đề xuất của Doraemon, cả nhóm quyết định cắm trại giữa lòng đại dương! Sử dụng bảo bối thần kì “xe Buggy chạy dưới nước” và “đèn pin thích nghi”, 5 bạn nhỏ tận hưởng chuyến cắm trại dưới đáy biển, gặp gỡ vô vàn sinh vật lí thú trên đường đi. Sau khi phát hiện một chiếc tàu đắm, nhóm bạn đã gặp chàng thanh niên bí ẩn El. Thật bất ngờ, anh ta lại là cư dân đáy biển, sống tại “liên bang Mu”, một vùng biển rộng lớn! Vốn căm ghét người mặt đất, cư dân đáy biển không thể nào tin tưởng Nobita và các bạn. Đúng lúc đó, lời thông báo “lâu đài quỷ... đã bắt đầu phục sinh!!” được truyền tới. “Lâu đài quỷ” khiến cư dân đáy biển khiếp sợ, rốt cuộc là gì? Đặt trọn niềm tin vào bè bạn trong lồng ngực, chuyến phiêu lưu vĩ đại quyết định số phận của trái đất, bắt đầu!', 'Hoạt hình - 2D', '', 101, 'https://starlight.vn/Areas/Admin/Content/Fileuploads/images/POSTER2026/Layer%207.jpg', '2026-05-22', NULL, 'K', '2026-06-04 13:56:23', '2026-06-04 13:56:23'),
(14, 'Bài trùng phá án', 'Cựu cảnh sát trọng án Jae-hyuk \"bị ép buộc\" phải hợp tác cùng tân binh Joong-ho để điều tra một vụ trộm nhỏ trong thị thấn, nhưng lại mở ra hàng loạt ...', 'Hài, Hành Động, Phiêu Lưu', 'PARK Chul-hwan', 97, 'https://starlight.vn/Areas/Admin/Content/Fileuploads/images/POSTER2026/Bai-trung-pha-an.jpg', '2026-05-22', NULL, 'T16', '2026-06-04 15:01:05', '2026-06-04 15:01:05'),
(15, 'Làng trùng tang', 'Siêu phẩm kinh dị tiếp theo từ nhà sản xuất Ác Linh Trong Xác Mẹ, Lọ Lem Chơi Ngải và Con Nít Quỷ Làng Trùng Tang theo chân Fitri (Wavi Zihan) – một nữ tư vấn viên quyết tâm làm sáng tỏ vụ bạo lực học đường liên quan đến cậu học sinh nghèo Jaya (Ali Fikry), giữa bối cảnh những lời kêu cứu bị phớt lờ và sự thật dần chìm trong im lặng. Thế nhưng, càng đào sâu, Fitri càng đối mặt với những hiện tượng rùng rợn: những người liên quan lần lượt gặp kết cục bí ẩn, rồi lại xuất hiện như chưa từng có chuyện gì xảy ra. Một thế lực siêu nhiên đang thao túng dân làng, khiến họ đối diện với “bản thể bóng tối” của chính mình – Qorin.', 'Kinh dị - 2D', 'Ginanti Rona', 107, 'https://starlight.vn/Areas/Admin/Content/Fileuploads/images/POSTER2026/Lang-trung-tang.jpg', '2026-05-22', NULL, 'T18', '2026-06-04 15:03:57', '2026-06-04 15:04:43'),
(16, 'He-man và những chiến binh vũ trụ', 'Thương hiệu huyền thoại trở lại màn ảnh rộng. Sau 15 năm thất lạc, Thanh Gươm Quyền Năng đưa Hoàng tử Adam (Nicholas Galitzine) trở về hành tinh Eternia và phát hiện quê hương đã rơi vào sự cai trị tàn bạo của Skeletor (Jared Leto). Để cứu gia đình và thế giới của mình, Adam phải sát cánh cùng những đồng minh thân cận như Teela (Camila Mendes) và Duncan/Man-At-Arms (Idris Elba), đồng thời chấp nhận định mệnh thật sự của mình: trở thành He-Man - người đàn ông mạnh nhất vũ trụ.', 'Hành Động, Khoa Học Viễn Tưởng', 'Travis Knight', 140, 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/image/c5f0a1eff4c394a251036189ccddaacd/p/o/poster_1__3_6.jpg', '2026-06-05', NULL, 'T13', '2026-06-04 15:09:07', '2026-06-04 15:09:07'),
(18, 'Ốc Mượn Hồn', 'Một người chồng đau khổ khi vợ qua đời trong tai nạn. Hạnh phúc tưởng chừng được hồi sinh khi linh hồn vợ anh trở về trong thân xác người khác...', 'Tâm lý', 'Đinh Tuấn Vũ', 109, 'https://iguov8nhvyobj.vcdn.cloud/media/catalog/product/cache/1/image/c5f0a1eff4c394a251036189ccddaacd/3/5/350x495-omh_1.jpg', '2026-06-01', 8.5, 'T16', '2026-06-04 15:21:15', '2026-06-04 15:26:32');

CREATE TABLE `users` (
  `dien_thoai` varchar(20) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `dia_chi` varchar(255) DEFAULT NULL,
  `ngay_sinh` date NOT NULL,
  `gioi_tinh` enum('Nam','Nữ','Khác') DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `vai_tro` varchar(20) NOT NULL DEFAULT 'user',
  `tai_khoan_bi_khoa` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`dien_thoai`, `ho_ten`, `email`, `dia_chi`, `ngay_sinh`, `gioi_tinh`, `mat_khau`, `vai_tro`, `tai_khoan_bi_khoa`, `created_at`) VALUES
('0999999001', 'Quản trị viên', 'admin1@admin.com', NULL, '0000-00-00', NULL, '$2y$10$vkDmo35qCTrY6GlCyZkp.elZ4LaxvpAgnTdCVhxycNcgt1wc9/0xe', 'admin', 0, '2026-05-26 23:06:09');

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
(34, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-02 02:13:01'),
(35, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-02 02:14:05'),
(36, '0347996049', 'dang_nhap', 'Đăng nhập thành open (đã xác thực OTP qua email).', '2026-06-02 02:13:52'),
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
(69, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-03 08:19:33'),
(70, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-04 12:50:20'),
(71, '0347996049', 'het_han', 'Vé phim \"Phí Phông: Quỷ Máu Rừng Thiêng\" (suất 04/06/2026 14:30, ghế D4, D5) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-06-04 12:50:45'),
(72, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-04 12:50:58'),
(73, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-04 12:52:56'),
(74, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-04 12:56:41'),
(75, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-04 12:56:52'),
(76, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-04 12:57:42'),
(77, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-04 12:57:55'),
(78, '0999999001', 'quan_tri', 'Thêm phim mới: Tạm biệt Gohan (K)', '2026-06-04 13:17:07'),
(79, '0999999001', 'quan_tri', 'Cập nhật thông tin phim: Tạm biệt Gohan (K)', '2026-06-04 13:25:31'),
(80, '0999999001', 'quan_tri', 'Thêm phim mới: Ma xó', '2026-06-04 13:33:09'),
(81, '0999999001', 'quan_tri', 'Cập nhật thông tin phim: Ma xó', '2026-06-04 13:36:32'),
(82, '0999999001', 'quan_tri', 'Xóa phim: Anh Hùng', '2026-06-04 13:37:56'),
(83, '0999999001', 'quan_tri', 'Xóa phim: Gấu Boonie: Kungfu Ân Sĩ', '2026-06-04 13:38:00'),
(84, '0999999001', 'quan_tri', 'Xóa phim: Heo 5 Móng', '2026-06-04 13:38:04'),
(85, '0999999001', 'quan_tri', 'Xóa phim: Đại Tiệc Trăng Máu 8', '2026-06-04 13:41:06'),
(86, '0999999001', 'quan_tri', 'Xóa phim: Trùm Sò', '2026-06-04 13:41:12'),
(87, '0999999001', 'quan_tri', 'Xóa phim: Super Mario Thiên Hà', '2026-06-04 13:41:18'),
(88, '0999999001', 'quan_tri', 'Xóa phim: Shin Cậu Bé Búp Chì: Quả Trứng Vương Quốc', '2026-06-04 13:41:23'),
(89, '0999999001', 'quan_tri', 'Thêm phim mới: Ốc mượn hồn', '2026-06-04 13:44:19'),
(90, '0999999001', 'quan_tri', 'Thêm phim mới: Ngôi đền kì quái 5', '2026-06-04 13:52:12'),
(91, '0999999001', 'quan_tri', 'Thêm phim mới: Doraemon: Nobita và lâu đài dưới đáy biển', '2026-06-04 13:56:23'),
(92, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-04 14:01:51'),
(93, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-04 14:02:21'),
(94, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-04 14:11:13'),
(95, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-04 14:27:27'),
(96, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-04 14:28:57'),
(97, '0347996049', 'dat_ve', 'Đặt vé phim \"Doraemon: Nobita và lâu đài dưới đáy biển\" - Ghế: A4, A5 - Suất: 05/06/2026 11:30 - 100.000 VND.', '2026-06-04 14:45:58'),
(98, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-04 14:49:52'),
(99, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-04 14:50:07'),
(100, '0999999001', 'quan_tri', 'Thêm phim mới: Bài trùng phá án', '2026-06-04 15:01:05'),
(101, '0999999001', 'quan_tri', 'Cập nhật thông tin phim: Ma xó', '2026-06-04 15:01:21'),
(102, '0999999001', 'quan_tri', 'Thêm phim mới: Làng trùng tang', '2026-06-04 15:03:57'),
(103, '0999999001', 'quan_tri', 'Cập nhật thông tin phim: Làng trùng tang', '2026-06-04 15:04:43'),
(104, '0999999001', 'quan_tri', 'Thêm phim mới: He-man và những chiến binh vũ trụ', '2026-06-04 15:09:07'),
(105, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-04 15:14:24'),
(106, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-04 15:14:55'),
(107, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-04 15:24:42'),
(108, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-04 15:24:57'),
(109, '0999999001', 'quan_tri', 'Cập nhật thông tin phim: Ốc Mượn Hồn', '2026-06-04 15:26:32'),
(110, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-04 15:28:57'),
(111, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-17 07:46:00'),
(112, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-17 08:01:58'),
(113, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-17 08:03:02'),
(114, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-17 08:13:11'),
(115, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-17 08:13:25'),
(116, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-24 09:04:34'),
(117, '0347996049', 'het_han', 'Vé phim \"Doraemon: Nobita và lâu đài dưới đáy biển\" (suất 05/06/2026 11:30, ghế A4, A5) đã qua giờ chiếu và được xóa khỏi danh sách vé.', '2026-06-24 09:05:26'),
(118, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-24 09:28:38'),
(119, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-24 09:37:20'),
(120, '0347996049', 'dat_ve', 'Đặt vé phim \"Ốc Mượn Hồn\" - Ghế: C4, C5 - Suất: 24/06/2026 17:10 - 100.000 VND.', '2026-06-24 09:37:39'),
(121, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-24 09:44:35'),
(122, '0347996049', 'dat_ve', 'Đặt vé phim \"Ốc Mượn Hồn\" - Ghế: B4 - Suất: 24/06/2026 17:10 - 50.000 VND.', '2026-06-24 09:54:50'),
(123, '0347996049', 'sua_ve', 'Cập nhật vé phim \"Ốc Mượn Hồn\" - Suất mới: 24/06/2026 17:10 - Ghế: B4 (Lần sửa thứ 1).', '2026-06-24 10:02:36'),
(124, '0347996049', 'sua_ve', 'Cập nhật vé phim \"Ốc Mượn Hồn\" - Suất mới: 24/06/2026 17:10 - Ghế: C4, C5 (Lần sửa thứ 1).', '2026-06-24 10:02:39'),
(125, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-24 10:16:07'),
(126, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-24 10:16:45'),
(127, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-24 10:16:55'),
(128, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-24 10:17:48'),
(129, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-24 10:18:11'),
(130, '0347996049', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-24 10:29:59'),
(131, '0999999001', 'dang_nhap', 'Đăng nhập quản trị thành công.', '2026-06-24 10:30:10'),
(132, '0999999001', 'quan_tri', 'Cập nhật thông tin phim: Phí Phông: Quỷ Máu Rừng Thiêng', '2026-06-24 10:30:45'),
(133, '0999999001', 'dang_xuat', 'Đăng xuất khỏi hệ thống.', '2026-06-24 10:30:50'),
(134, '0347996049', 'dang_nhap', 'Đăng nhập thành công (đã xác thực OTP qua email).', '2026-06-24 10:31:15');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

ALTER TABLE `movies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_movie_name` FOREIGN KEY (`ten_phim_dat`) REFERENCES `movies` (`ten_phim`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_phone` FOREIGN KEY (`user_phone`) REFERENCES `users` (`dien_thoai`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;