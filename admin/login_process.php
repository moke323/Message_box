<?php 
    session_start(); 
    include 'db.php'; ?>
<?php
// 6.2 验证CSRF令牌
if (!validate_csrf_token($_POST['csrf_token'])) {
    die("非法请求");
}

$username = $_POST['username'];
$password = $_POST['password'];

// 查询管理员（含角色和权限）
$stmt = $pdo->prepare("
    SELECT u.*, r.permissions 
    FROM admin_users u
    JOIN admin_roles r ON u.role_id = r.id
    WHERE u.username = ?
");
$stmt->execute([$username]);
$user = $stmt->fetch();

// 6.1 密码加密验证
if ($user && password_verify($password, $user['password'])) {
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_role_permissions'] = $user['permissions']; // 存储权限
    header("Location: dashboard.php");
} else {
    header("Location: login.php?error=1");
}