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

function logEmailDebug($message) {
    $line = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents(__DIR__ . '/email_debug.log', $line, FILE_APPEND | LOCK_EX);
}

function sendOtpEmail($toEmail, $userName, $otp) {
    try {
        $config = getEmailConfig();
        $subject = 'Mã xác thực đăng nhập - Cinema Group 11';
        $body = "Xin chào {$userName},\n\n"
            . "Mã xác thực đăng nhập của bạn là: {$otp}\n\n"
            . "Mã có hiệu lực trong 60 giây. Không chia sẻ mã này với bất kỳ ai.\n\n"
            . "Nếu bạn không yêu cầu đăng nhập, hãy bỏ qua email này.\n\n"
            . "— Cinema Group 11";

        $user = trim($config['smtp_user'] ?? '');
        $pass = trim($config['smtp_pass'] ?? '');

        // Nếu chưa cấu hình email, luôn bật dev mode
        if ($user === '' || $pass === '') {
            logEmailDebug("Email config incomplete, using dev mode");
            logOtpDev($toEmail, $otp);
            return ['success' => true, 'dev' => true];
        }

        // Thử gửi email (chỉ nếu dev_mode = false)
        if (empty($config['dev_mode'])) {
            $sent = sendGmailSMTP($config, $toEmail, $subject, $body);
            if ($sent) {
                logEmailDebug("Email sent successfully to {$toEmail}");
                return ['success' => true];
            }
        }

        // Dev mode hoặc email thất bại - ghi OTP vào log file
        logEmailDebug("Using dev mode fallback - OTP logged to file");
        logOtpDev($toEmail, $otp);
        return ['success' => true, 'dev' => true];
    } catch (Exception $e) {
        logEmailDebug("Exception in sendOtpEmail: " . $e->getMessage());
        logOtpDev($toEmail, $otp);
        return ['success' => true, 'dev' => true];
    }
}

function sendGmailSMTP(array $config, $to, $subject, $body) {
    $host = $config['smtp_host'] ?? 'smtp.gmail.com';
    $port = (int)($config['smtp_port'] ?? 587);
    $user = $config['smtp_user'];
    $pass = $config['smtp_pass'];
    $from = $config['smtp_from'] ?? $user;
    $fromName = $config['smtp_from_name'] ?? 'Cinema Group 11';

    logEmailDebug("=== SMTP Start ===");
    logEmailDebug("Host: {$host}, Port: {$port}, User: {$user}");

    try {
        // Create SSL context
        $context = stream_context_create([
            'ssl' => [
                'allow_self_signed' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);

        // Connect using stream_socket_client (better than fsockopen)
        $socket = @stream_socket_client(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            logEmailDebug("❌ Kết nối thất bại: {$errstr} ({$errno})");
            return false;
        }

        stream_set_blocking($socket, true);
        stream_set_timeout($socket, 15);

        // Read initial server response
        $response = fgets($socket, 1024);
        logEmailDebug("→ Server response: " . trim($response));
        
        if (strpos($response, '220') === false) {
            logEmailDebug("❌ Invalid server response (expected 220)");
            fclose($socket);
            return false;
        }

        // EHLO command
        fwrite($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ EHLO response: " . trim($response));

        // Read multi-line response
        while (substr($response, 3, 1) === '-') {
            $response = fgets($socket, 1024);
        }

        // STARTTLS command
        fwrite($socket, "STARTTLS\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ STARTTLS response: " . trim($response));

        if (strpos($response, '220') === false) {
            logEmailDebug("❌ STARTTLS not accepted by server");
            fclose($socket);
            return false;
        }

        // Enable TLS encryption on the socket
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            logEmailDebug("❌ TLS enable failed on socket");
            fclose($socket);
            return false;
        }

        logEmailDebug("✓ TLS enabled successfully");

        // EHLO again after TLS
        fwrite($socket, "EHLO localhost\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ Post-TLS EHLO: " . trim($response));

        // Read multi-line response
        while (substr($response, 3, 1) === '-') {
            $response = fgets($socket, 1024);
        }

        // AUTH LOGIN
        fwrite($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ AUTH LOGIN response: " . trim($response));

        if (strpos($response, '334') === false) {
            logEmailDebug("❌ AUTH LOGIN not accepted");
            fclose($socket);
            return false;
        }

        // Send username (base64 encoded)
        fwrite($socket, base64_encode($user) . "\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ Username sent, response: " . trim($response));

        // Send password (base64 encoded)
        fwrite($socket, base64_encode($pass) . "\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ Password sent, response: " . trim($response));

        if (strpos($response, '235') === false) {
            logEmailDebug("❌ Authentication failed");
            fclose($socket);
            return false;
        }

        logEmailDebug("✓ Authenticated successfully");

        // MAIL FROM
        fwrite($socket, "MAIL FROM:<{$from}>\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ MAIL FROM response: " . trim($response));

        // RCPT TO
        fwrite($socket, "RCPT TO:<{$to}>\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ RCPT TO response: " . trim($response));

        // DATA
        fwrite($socket, "DATA\r\n");
        $response = fgets($socket, 1024);
        logEmailDebug("→ DATA response: " . trim($response));

        if (strpos($response, '354') === false) {
            logEmailDebug("❌ DATA command not accepted");
            fclose($socket);
            return false;
        }

        // Prepare email headers
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';

        $message = "From: {$encodedFromName} <{$from}>\r\n";
        $message .= "To: <{$to}>\r\n";
        $message .= "Subject: {$encodedSubject}\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $message .= "Content-Transfer-Encoding: 8bit\r\n";
        $message .= "Date: " . date('r') . "\r\n\r\n";
        $message .= $body . "\r\n.\r\n";

        // Send message
        fwrite($socket, $message);
        $response = fgets($socket, 1024);
        logEmailDebug("→ Message send response: " . trim($response));

        if (strpos($response, '250') === false) {
            logEmailDebug("❌ Message send failed");
            fclose($socket);
            return false;
        }

        logEmailDebug("✓ Message sent successfully");

        // QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        logEmailDebug("=== SMTP End (SUCCESS) ===\n");
        return true;

    } catch (Exception $e) {
        logEmailDebug("❌ Exception: " . $e->getMessage());
        logEmailDebug("=== SMTP End (ERROR) ===\n");
        return false;
    }
}
