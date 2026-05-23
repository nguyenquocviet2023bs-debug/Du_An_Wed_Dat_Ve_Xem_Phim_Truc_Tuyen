<?php

function getEmailConfig() {
    $path = __DIR__ . '/email_config.php';
    if (!file_exists($path)) {
        return ['dev_mode' => true];
    }
    return require $path;
}

function maskEmail($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return '***@***';
    }
    $name = $parts[0];
    $domain = $parts[1];
    $visible = substr($name, 0, min(2, strlen($name)));
    return $visible . '***@' . $domain;
}

function logOtpDev($to, $otp) {
    $line = date('Y-m-d H:i:s') . " | {$to} | OTP: {$otp}\n";
    file_put_contents(__DIR__ . '/otp_dev.log', $line, FILE_APPEND | LOCK_EX);
}

function sendOtpEmail($toEmail, $userName, $otp) {
    $config = getEmailConfig();
    $subject = 'Mã xác thực đăng nhập - Cinema Group 11';
    $body = "Xin chào {$userName},\n\n"
        . "Mã xác thực đăng nhập của bạn là: {$otp}\n\n"
        . "Mã có hiệu lực trong 60 giây. Không chia sẻ mã này với bất kỳ ai.\n\n"
        . "Nếu bạn không yêu cầu đăng nhập, hãy bỏ qua email này.\n\n"
        . "— Cinema Group 11";

    $user = trim($config['smtp_user'] ?? '');
    $pass = trim($config['smtp_pass'] ?? '');

    if ($user === '' || $pass === '') {
        if (!empty($config['dev_mode'])) {
            logOtpDev($toEmail, $otp);
            return ['success' => true, 'dev' => true];
        }
        return ['success' => false, 'message' => 'Chưa cấu hình email Gmail. Liên hệ quản trị viên.'];
    }

    $sent = sendSmtpMail($config, $toEmail, $subject, $body);
    if ($sent) {
        return ['success' => true];
    }

    if (!empty($config['dev_mode'])) {
        logOtpDev($toEmail, $otp);
        return ['success' => true, 'dev' => true];
    }

    return ['success' => false, 'message' => 'Không gửi được email. Kiểm tra cấu hình Gmail.'];
}

function sendSmtpMail(array $config, $to, $subject, $body) {
    $host = $config['smtp_host'] ?? 'smtp.gmail.com';
    $port = (int)($config['smtp_port'] ?? 587);
    $user = $config['smtp_user'];
    $pass = $config['smtp_pass'];
    $from = $config['smtp_from'] ?? $user;
    $fromName = $config['smtp_from_name'] ?? 'Cinema Group 11';

    $socket = @stream_socket_client(
        "tcp://{$host}:{$port}",
        $errno,
        $errstr,
        20,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 20);

    $read = function () use ($socket) {
        $data = '';
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };

    $cmd = function ($command) use ($socket, $read) {
        fwrite($socket, $command . "\r\n");
        return $read();
    };

    $read();
    $cmd('EHLO localhost');
    $cmd('STARTTLS');

    if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
        fclose($socket);
        return false;
    }

    $cmd('EHLO localhost');
    $cmd('AUTH LOGIN');
    $cmd(base64_encode($user));
    $auth = $cmd(base64_encode($pass));
    if (strpos($auth, '235') === false) {
        fclose($socket);
        return false;
    }

    $cmd('MAIL FROM:<' . $from . '>');
    $cmd('RCPT TO:<' . $to . '>');
    $cmd('DATA');

    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
    $message = "From: {$encodedFromName} <{$from}>\r\n";
    $message .= "To: <{$to}>\r\n";
    $message .= "Subject: {$encodedSubject}\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $message .= $body . "\r\n.\r\n";

    fwrite($socket, $message);
    $result = $read();
    $cmd('QUIT');
    fclose($socket);

    return strpos($result, '250') !== false;
}
