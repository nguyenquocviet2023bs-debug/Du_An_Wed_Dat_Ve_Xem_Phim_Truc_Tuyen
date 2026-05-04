# Giao Diện Trang Chủ - Hệ Thống Đặt Vé Xem Phim

## 📋 Mô Tả

Đây là giao diện trang chủ hoàn chỉnh cho một hệ thống đặt vé xem phim trực tuyến, được thiết kế dựa trên tham khảo từ Galaxy Cinema. Giao diện hỗ trợ responsive design và tương thích với tất cả các thiết bị.

## 📁 Cấu Trúc Thư Mục

```
Du_An_Wed_Dat_Ve_Xem_Phim_Truc_Tuyen/
│
├── TrangChu.php                    # Trang chủ chính (HTML)
├── README.md                       # Tài liệu hướng dẫn
│
├── assets/
│   ├── css/
│   │   └── style.css              # Toàn bộ CSS styling
│   ├── js/
│   │   └── script.js              # JavaScript interactivity
│   └── images/                    # Thư mục cho hình ảnh
│       ├── banner-movie.jpg       # Hình ảnh banner chính
│       ├── movie1.jpg - movie4.jpg # Hình ảnh phim đang chiếu
│       └── upcoming1.jpg - upcoming4.jpg # Hình ảnh phim sắp chiếu
```

## 🎯 Các Tính Năng Chính

### 1. **Header (Phần Đầu Trang)**
   - Logo và tên website
   - Menu điều hướng
   - Nút tìm kiếm
   - Nút đăng nhập
   - Badge VIP

### 2. **Banner Chính**
   - Hình ảnh phim lớn
   - Tiêu đề phim nổi bật
   - Nút "Đặt Vé Ngay"

### 3. **Phần Chọn Đặt Vé**
   - Chọn phim
   - Chọn rạp chiếu
   - Chọn ngày chiếu
   - Chọn suất chiếu
   - Nút "Mua vé nhanh"

### 4. **Danh Sách Phim Đang Chiếu**
   - Grid hiển thị 4 phim
   - Poster phim với hình ảnh
   - Đánh giá sao
   - Nhãn phân loại tuổi (T18, K, T16)
   - Nút xem trailer
   - Nút đặt vé

### 5. **Phim Sắp Chiếu**
   - Grid hiển thị phim sắp ra mắt
   - Status "Sắp chiếu"

### 6. **Ưu Đãi & Khuyến Mãi**
   - Khuyến mãi hàng tuần
   - Combo gia đình
   - Hội viên VIP
   - Ưu đãi sinh nhật

### 7. **Footer (Phần Cuối)**
   - Thông tin công ty
   - Các liên kết
   - Mạng xã hội
   - Phương thức thanh toán

## 🎨 Thiết Kế

### Màu Sắc Chính
- **Màu cam**: `#ff6b35` - Nút bấm, liên kết chính
- **Màu vàng**: `#ffd700` - Nhấn mạnh, đánh giá
- **Màu đen**: `#1a1a1a` - Nền, chữ chính
- **Màu xám**: `#ccc`, `#999`, `#666` - Chữ phụ

### Font
- Sử dụng `Segoe UI` hoặc các font Tahoma, Geneva, Verdana

### Icon
- Sử dụng Font Awesome 6.0 (từ CDN)

## 📱 Responsive Design

Giao diện tự động thích ứng với:
- **Desktop**: Dạy đủ 4 phim mỗi hàng
- **Tablet (1024px)**: 2 phim mỗi hàng
- **Mobile (768px)**: 2 phim mỗi hàng, menu ẩn
- **Mobile nhỏ (480px)**: 1 phim mỗi hàng, tối ưu hóa

## 🚀 Hướng Dẫn Sử Dụng

### 1. Cài Đặt

1. Đặt thư mục `Du_An_Wed_Dat_Ve_Xem_Phim_Truc_Tuyen` vào `htdocs` trong XAMPP
2. Mở trình duyệt và truy cập:
   ```
   http://localhost/DuAnWedXemPhim/Du_An_Wed_Dat_Ve_Xem_Phim_Truc_Tuyen/TrangChu.php
   ```

### 2. Thêm Hình Ảnh

1. Chuẩn bị các hình ảnh:
   - `banner-movie.jpg` - Banner chính (khuyến cáo: 1920x500px)
   - `movie1.jpg - movie4.jpg` - Poster phim (khuyến cáo: 300x450px)
   - `upcoming1.jpg - upcoming4.jpg` - Phim sắp chiếu (khuyến cáo: 300x400px)

2. Đặt vào thư mục: `assets/images/`

### 3. Tùy Chỉnh Nội Dung

#### Thay Đổi Tiêu Đề/Logo
Mở `TrangChu.php` tìm:
```html
<div class="logo">
    <i class="fas fa-film"></i>
    <span>Cinema Hub</span>
</div>
```

#### Thay Đổi Tên Phim
Tìm phần `<!-- Movie Card 1 -->` và sửa:
```html
<h3 class="movie-title">Heo 5 Mộng</h3>
```

#### Thay Đổi Danh Sách Phim (Dropdown)
Tìm section `<!-- BOOKING SELECTION -->`:
```html
<select class="selection-input">
    <option>Chọn phim yêu thích...</option>
    <option>Heo 5 Mộng</option>
    <option>Trùm Số</option>
    <!-- Thêm phim mới ở đây -->
</select>
```

### 4. Thay Đổi Màu Sắc

Mở `assets/css/style.css` và tìm các biến màu:
```css
/* Thay đổi màu cam chính */
background: #ff6b35;    /* Thành màu khác */

/* Ví dụ: thay thành xanh */
background: #0066cc;
```

### 5. Kết Nối Database

Hiện tại file chỉ là giao diện. Để kết nối với database:

1. Tạo file `config/db.php`:
```php
<?php
$host = 'localhost';
$db_name = 'cinema_db';
$db_user = 'root';
$db_pass = '';

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

2. Thay file HTML thành PHP và sử dụng query để lấy dữ liệu từ database

## 🔧 JavaScript Functions

### Chính
- `initNavigation()` - Khởi tạo menu điều hướng
- `initBookingButtons()` - Khởi tạo nút đặt vé
- `handleBooking()` - Xử lý sự kiện đặt vé
- `validateBookingForm()` - Kiểm tra form

## 📝 File Cấu Hình CSS

### File `style.css` bao gồm:
- **Reset Styles** - Cài đặt mặc định
- **Header Styles** - Phần đầu trang
- **Banner Styles** - Hình ảnh chính
- **Booking Selection** - Chọn đặt vé
- **Movies Section** - Danh sách phim
- **Footer Styles** - Phần cuối
- **Responsive Design** - Media queries

## 🔗 Liên Kết Bên Ngoài

- Font Awesome: `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css`

## 🐛 Troubleshooting

### 1. Hình ảnh không hiển thị
- Kiểm tra đường dẫn file trong thư mục `assets/images/`
- Đảm bảo tên file trùng khớp với src trong HTML

### 2. CSS không được áp dụng
- Kiểm tra đường dẫn: `assets/css/style.css`
- F5 để tải lại trang (clear cache: Ctrl+Shift+Delete)

### 3. JavaScript không hoạt động
- Kiểm tra console: F12 > Console
- Đảm bảo file `assets/js/script.js` tồn tại

## 📚 Thêm Tính Năng

### Thêm Modal Tìm Kiếm
```javascript
function openSearchModal() {
    // Code hiển thị modal tìm kiếm
}
```

### Thêm Trang Đặt Vé
Tạo file `DatVe.php` để xử lý đặt vé

### Thêm Trang Chi Tiết Phim
Tạo file `ChiTietPhim.php` để hiển thị thông tin phim chi tiết

## 👨‍💻 Phát Triển Thêm

Để mở rộng dự án:
1. Tạo database cho phim, rạp, suất chiếu
2. Kết nối PHP với database
3. Tạo trang đặt vé động
4. Thêm trang thanh toán
5. Thêm hệ thống user/authentication

## 📄 Thông Tin Thêm

- **HTML5** - Cấu trúc trang
- **CSS3** - Styling và animation
- **JavaScript (Vanilla)** - Tương tác người dùng
- **Font Awesome** - Icon

## 🤝 Hỗ Trợ

Nếu gặp vấn đề:
1. Kiểm tra console (F12)
2. Xem file error logs
3. Đảm bảo XAMPP đang chạy
4. Kiểm tra đường dẫn file

---

**Lần cập nhật cuối cùng**: 2026
**Phiên bản**: 1.0
