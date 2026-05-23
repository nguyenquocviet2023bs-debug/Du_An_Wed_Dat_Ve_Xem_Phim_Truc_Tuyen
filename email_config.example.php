<?php
/**
 * Đổi tên file này thành email_config.php và điền thông tin Gmail.
 *
 * Gmail: bật xác minh 2 bước → tạo "Mật khẩu ứng dụng" (App Password)
 * https://myaccount.google.com/apppasswords
 */
return [
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'your-email@gmail.com',
    'smtp_pass' => 'your-16-char-app-password',
    'smtp_from' => 'your-email@gmail.com',
    'smtp_from_name' => 'Cinema Group 11',
    // true: nếu gửi mail thất bại, ghi mã OTP vào otp_dev.log (chỉ dùng khi dev)
    'dev_mode' => true,
];
