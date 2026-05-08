# HỆ THỐNG ĐĂNG NHẬP & QUẢN LÝ TÀI KHOẢN

## 📋 Mục Lục
1. [Tổng Quan](#tổng-quan)
2. [Cấu Trúc Tệp](#cấu-trúc-tệp)
3. [Tính Năng](#tính-năng)
4. [Hướng Dẫn Sử Dụng](#hướng-dẫn-sử-dụng)
5. [Luồng Xử Lý](#luồng-xử-lý)

---

## Tổng Quan

Hệ thống đăng nhập & quản lý tài khoản hoàn chỉnh cho Cinema Group 11 với các tính năng:

✅ Đăng ký tài khoản mới  
✅ Đăng nhập với kiểm tra database  
✅ Xác thực mật khẩu được mã hóa (bcrypt)  
✅ Admin có thể khóa/mở khóa tài khoản  
✅ Ghi nhật ký hệ thống  
✅ Hiển thị thông báo lỗi chi tiết  

---

## Cấu Trúc Tệp

### Frontend (HTML)
```
📄 login.php             - Trang đăng nhập
📄 signup.php            - Trang đăng ký
📄 admin_users.php       - Trang quản lý người dùng (Admin)
```

### Backend (PHP)
```
📄 process_login.php     - Xử lý đăng nhập
📄 process_signup.php    - Xử lý đăng ký
📄 logout.php            - Xử lý đăng xuất
📄 db_helper.php         - Hàm database (đã cập nhật)
```

### Database
```
✓ Cảnh báo: Database cần được tạo trước
  - Chạy: database_schema.sql
  - Bảng: users, system_logs
```

---

## Tính Năng

### 1️⃣ ĐĂNG NHẬP

**Tệp:** `login.php` + `process_login.php`

**Quy trình:**
1. Người dùng nhập username/email + mật khẩu
2. `process_login.php` gọi `authenticateUser()`
3. Kiểm tra:
   - Tài khoản có tồn tại?
   - Tài khoản bị khóa (banned)?
   - Mật khẩu có chính xác?
4. Nếu thành công: Tạo session + chuyển hướng
5. Nếu thất bại: Hiển thị thông báo lỗi

**Thông báo:**
```
❌ Tài khoản không tồn tại
❌ Tài khoản của bạn đã bị khóa
❌ Mật khẩu không chính xác
✓ Đăng nhập thành công
```

---

### 2️⃣ ĐĂNG KÝ

**Tệp:** `signup.php` + `process_signup.php`

**Quy trình:**
1. Người dùng điền form với thông tin:
   - Họ tên, email, username
   - Mật khẩu (ít nhất 8 ký tự)
   - Số điện thoại, địa chỉ, ngày sinh, CCCD...

2. Validation trước gửi (Client-side)
   - Kiểm tra độ mạnh mật khẩu
   - Mật khẩu trùng khớp

3. `process_signup.php` validation (Server-side)
   - Kiểm tra dữ liệu bắt buộc
   - Kiểm tra email hợp lệ
   - Kiểm tra username có tồn tại?
   - Kiểm tra email đã đăng ký?
   - Kiểm tra CCCD đã đăng ký?

4. Nếu hợp lệ: Lưu vào database
   - Mật khẩu được mã hóa (bcrypt)
   - Status = 'active'
   - Ghi log hành động

5. Chuyển hướng đến login

**Validation Rules:**
```
Họ tên:        Bắt buộc
Email:         Bắt buộc, hợp lệ, không trùng
Username:      6-20 ký tự, không trùng, chỉ chữ/số/-/_
Mật khẩu:      ≥ 8 ký tự
Giới tính:     male/female/other
```

---

### 3️⃣ QUẢN LÝ TÀI KHOẢN (ADMIN)

**Tệp:** `admin_users.php`

**Tính năng:**
- 📊 Danh sách tất cả người dùng
- 🔒 Khóa tài khoản (account_status = 'banned')
- 🔓 Mở khóa tài khoản (account_status = 'active')
- 📝 Thêm lý do khóa/mở khóa
- 📄 Phân trang (20 người/trang)
- 🕐 Hiển thị lần đăng nhập cuối

**Cách Sử Dụng:**
1. Admin truy cập `admin_users.php`
2. Chọn người dùng cần khóa
3. Nhấn nút "Khóa" hoặc "Mở"
4. Điền lý do (tùy chọn)
5. Xác nhận
6. System log sẽ ghi nhận hành động

**Hiệu Ứng Khóa Tài Khoản:**
- Người dùng KHÔNG thể đăng nhập
- Hiển thị thông báo: "❌ Tài khoản của bạn đã bị khóa"

---

## Hướng Dẫn Sử Dụng

### Bước 1: Cài Đặt Database

Chạy script SQL:
```sql
-- Từ file database_schema.sql
-- Tạo bảng users, system_logs, v.v.
mysql -u root -p < database_schema.sql
```

### Bước 2: Cập Nhật db_helper.php

Kiểm tra file `db_helper.php` có hàm:
```php
✓ authenticateUser()        - Xác thực đăng nhập
✓ createUser()              - Tạo user mới
✓ lockUserAccount()         - Khóa tài khoản
✓ unlockUserAccount()       - Mở khóa tài khoản
✓ getAllUsers()             - Lấy danh sách user
✓ logAction()               - Ghi log hệ thống
```

### Bước 3: Kiểm Tra Kết Nối

Mở file `login.php` trong trình duyệt:
```
http://localhost/xampp/htdocs/DuAnWedXemPhim/Du_An_Wed_Dat_Ve_Xem_Phim_Truc_Tuyen/login.php
```

### Bước 4: Tạo Tài Khoản Test

1. Nhấn "Đăng Ký Ngay"
2. Điền thông tin
3. Nhấn "Đăng Ký"

### Bước 5: Đăng Nhập

1. Nhập username/email
2. Nhập mật khẩu
3. Nhấn "Đăng Nhập"

### Bước 6: Admin Quản Lý

1. Truy cập `admin_users.php` (cần xác thực admin trước)
2. Tìm người dùng
3. Nhấn "Khóa" để khóa tài khoản
4. Điền lý do
5. Xác nhận

---

## Luồng Xử Lý

### 📌 Luồng Đăng Nhập

```
[Trang login.php]
    ↓
[Form POST đến process_login.php]
    ↓
[Kiểm tra dữ liệu rỗng]
    ↓
[Gọi authenticateUser($username, $password)]
    ├─→ Kiểm tra user có tồn tại?
    ├─→ Kiểm tra status = 'banned'?
    ├─→ Kiểm tra status = 'inactive'?
    ├─→ Kiểm tra password_verify()?
    └─→ Return array status
    ↓
[Nếu status = 'success']
    ├─→ updateLastLogin()
    ├─→ Tạo $_SESSION
    ├─→ logAction()
    └─→ header('Location: TrangChu.php')
    ↓
[Nếu status = 'banned']
    └─→ header('Location: login.php?type=error&message=...')
    ↓
[Nếu status = 'error']
    └─→ header('Location: login.php?type=error&message=...')
```

### 📌 Luồng Đăng Ký

```
[Trang signup.php]
    ↓
[Form POST đến process_signup.php]
    ↓
[Validation Client-side]
    ├─→ Kiểm tra bắt buộc
    ├─→ Kiểm tra mật khẩu trùng
    └─→ Độ mạnh mật khẩu
    ↓
[Validation Server-side]
    ├─→ Kiểm tra tất cả bắt buộc
    ├─→ Kiểm tra email hợp lệ
    ├─→ Kiểm tra email tồn tại?
    ├─→ Kiểm tra username tồn tại?
    └─→ Kiểm tra CCCD tồn tại?
    ↓
[Nếu hợp lệ]
    ├─→ Gọi createUser()
    │   ├─→ Hash password với bcrypt
    │   └─→ INSERT INTO users
    ├─→ logAction('REGISTER')
    └─→ header('Location: login.php?type=success')
    ↓
[Nếu lỗi]
    └─→ header('Location: signup.php?type=error&message=...')
```

### 📌 Luồng Quản Lý Tài Khoản (Admin)

```
[Admin truy cập admin_users.php]
    ├─→ Kiểm tra admin đã đăng nhập?
    └─→ Hiển thị danh sách user
    ↓
[Admin nhấn "Khóa" hoặc "Mở"]
    ├─→ Modal hiển thị
    └─→ Điền lý do
    ↓
[Admin nhấn "Xác Nhận"]
    ├─→ Form POST
    ├─→ Gọi lockUserAccount() hoặc unlockUserAccount()
    │   ├─→ UPDATE users SET account_status = 'banned'/'active'
    │   └─→ logAction()
    └─→ Hiển thị thông báo kết quả
    ↓
[Người dùng thử đăng nhập]
    ├─→ Gọi authenticateUser()
    └─→ Nếu status = 'banned'
        └─→ Hiển thị: "❌ Tài khoản của bạn đã bị khóa"
```

---

## 🔐 Bảo Mật

### Mã Hóa Mật Khẩu
```php
// Khi tạo:
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Khi xác thực:
password_verify($inputPassword, $hashedPassword);
```

### Ghi Log Hệ Thống
```php
logAction(
    'ACTION_NAME',           // LOGIN, LOGOUT, REGISTER, LOCK_ACCOUNT
    'Description',           // Mô tả chi tiết
    'TABLE_NAME',           // users
    $userId,                // ID record
    'old_value',            // Giá trị cũ
    'new_value'             // Giá trị mới
);
```

Thông tin ghi nhật ký:
- User ID
- Hành động (action)
- Mô tả (description)
- Bảng dữ liệu
- ID record bị ảnh hưởng
- Giá trị cũ/mới
- IP address
- User Agent (Browser)
- Thời gian

### SQL Injection Prevention
```php
// Sử dụng Prepared Statements
$stmt = $mysqli->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

---

## 🐛 Troubleshooting

### Lỗi: "Kết nối database thất bại"
**Giải pháp:**
- Kiểm tra MySQL đang chạy
- Kiểm tra thông tin kết nối trong `db_helper.php`
- Kiểm tra database đã được tạo

### Lỗi: "Email này đã được đăng ký"
**Giải pháp:**
- Dùng email khác
- Hoặc đăng nhập nếu đã có tài khoản

### Lỗi: "Tài khoản của bạn đã bị khóa"
**Giải pháp:**
- Liên hệ admin để mở khóa
- Kiểm tra lý do khóa từ admin

### Session không lưu trữ
**Giải pháp:**
- Kiểm tra `session_start()` được gọi trước
- Kiểm tra PHP session directory có quyền ghi
- Xóa cookie/session cũ

---

## 📧 Liên Hệ

Để có thêm hỗ trợ:
- Email: group11@itqnu.com
- Hotline: +84 ****049

---

**Phiên bản:** 2.0  
**Ngày cập nhật:** 2026-05-08  
**Tác giả:** Cinema Group 11 Dev Team
