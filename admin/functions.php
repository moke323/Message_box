<?php

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 发送邮件函数
function send_email($to, $subject, $content) {
    $config = require 'mail_config.php';
    
    $mail = new PHPMailer(true);
    
    try {
        // 服务器配置
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];        // 修正：使用正确的键名
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username']; // 修正：使用正确的键名
        $mail->Password = $config['smtp_password']; // 修正：使用正确的键名
        $mail->SMTPSecure = $config['smtp_secure']; // 修正：使用正确的键名
        $mail->Port = $config['smtp_port'];        // 修正：使用正确的键名
        
        // 发件人
        $mail->setFrom($config['from_email'], $config['from_name']); // 修正：使用正确的键名
        
        // 收件人
        $mail->addAddress($to);
        
        // 内容
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $content;
        $mail->CharSet = 'UTF-8';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("邮件发送失败: " . $mail->ErrorInfo);
        return false;
    }
}

// 6.1 密码加密函数
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// 6.2 CSRF令牌生成与验证
function generate_csrf_token() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// function validate_csrf_token($token) {
//     return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
// }

// 权限验证函数（6.3）
function has_permission($permission) {
    // 检查会话权限是否存在且为数组
    if (!isset($_SESSION['permissions']) || !is_array($_SESSION['permissions'])) {
        return false;
    }
    return in_array($permission, $_SESSION['permissions']); // 与直接检查逻辑一致
}