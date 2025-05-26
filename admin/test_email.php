<?php
session_start();
require 'functions.php';

// 测试发送邮件
$result = send_email(
    '3069886684@qq.com',  // 替换为你的测试邮箱
    '测试邮件',
    '这是一封测试邮件，用于验证PHPMailer配置是否正确。'
);

if ($result) {
    echo '邮件发送成功！';
} else {
    echo '邮件发送失败，请检查配置和日志。';
}
?>