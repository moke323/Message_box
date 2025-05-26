<?php
// 要加密的密码
$password = "admin";

// 生成哈希值（自动生成盐值，默认cost=10）
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// 输出哈希结果
echo $hash;
?>
