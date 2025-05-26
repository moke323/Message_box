<?php
session_start();

// // ==================== 新增：路径验证逻辑 ====================
// $expected_segment = 'yuzhenghao666';

// // 获取当前请求的URI并移除查询参数
// $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// // 分割URI路径并获取最后一段
// $segments = explode('/', trim($request_uri, '/'));
// $last_segment = end($segments);

// // 验证路径（兼容直接访问和带参数的情况）
// if ($last_segment !== $expected_segment) {
//     header("Location: /admin/error.html"); 
//     exit;
// }
// // ==================== 路径验证结束 ====================

include 'db.php';

// 处理登录表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证 CSRF 令牌（需配合隐藏字段）
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        die("非法请求");
    }

    // 接收表单数据并过滤
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if (empty($username) || empty($password)) {
        header("Location: login.php?error=1"); // 空值错误
        exit;
    }

    try {
        // 查询管理员账号（包含角色和权限信息）
        $stmt = $pdo->prepare("
    SELECT u.id, u.username, u.password, r.permissions 
    FROM admin_users u
    JOIN admin_roles r ON u.role_id = r.id
    WHERE u.username = :username
");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // 验证密码
        if ($user && password_verify($password, $user['password'])) {
            // 存储会话信息（包含权限）
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['permissions'] = json_decode($user['permissions'], true);
            header("Location: dashboard.php"); // 登录成功跳转后台
            exit;
        } else {
            header("Location: login.php?error=2"); // 账号或密码错误
            exit;
        }
    } catch (PDOException $e) {
        die("数据库操作失败：" . $e->getMessage());
    }
}

// 生成 CSRF 令牌
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 生成新令牌
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>管理员登录</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f0f2f5; }
        .login-box { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 1.5rem; }
        input { width: 300px; padding: 0.8rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 1rem; }
        button { width: 100%; padding: 1rem; background: #1a73e8; color: white; border: none; border-radius: 6px; cursor: pointer; }
        .error { color: red; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="color: #1a73e8; margin-bottom: 1.5rem;">留言管理系统</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?php 
                switch ($_GET['error']) {
                    case '1': echo '请填写完整用户名和密码'; break;
                    case '2': echo '账号或密码错误'; break;
                }
                ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <input type="text" name="username" placeholder="用户名" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="密码" required>
            </div>
            <!-- CSRF 令牌 -->
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <button type="submit">登录</button>
        </form>
    </div>
</body>
</html>