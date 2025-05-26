<?php
// 数据库配置（请与宝塔面板创建的数据库一致）
$db_host = 'localhost';        // 数据库主机
$db_name = 'message_system';   // 数据库名
$db_user = 'msg_admin';        // 数据库用户名
$db_pass = '123456';           // 数据库密码

try {
    // 创建 PDO 连接（全局作用域）
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,    // 开启异常模式
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // 默认返回关联数组
        ]
    );
} catch (PDOException $e) {
    // 捕获连接错误并输出详细信息（正式环境可改为日志记录）
    die("数据库连接失败：" . $e->getMessage());
}