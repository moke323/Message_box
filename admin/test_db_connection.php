<?php
$db_host = 'localhost';
$db_name = 'message_system';
$db_user = 'msg_admin';
$db_pass = '123456';

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
    echo "数据库连接成功！";
    $pdo = null; // 关闭连接
} catch (PDOException $e) {
    die("连接失败：" . $e->getMessage());
}
?>