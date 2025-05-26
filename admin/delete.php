<?php 
session_start(); 
include 'db.php'; 
include 'functions.php';
?>

<?php
// 6.2 验证CSRF令牌
// if (!validate_csrf_token($_GET['csrf_token'])) {
//     die("非法请求");
// }

// // 6.3 权限验证
// if (!has_permission('delete')) {
//     die("无权限操作");
// }

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: dashboard.php?csrf_token=" . generate_csrf_token());
exit;
?>