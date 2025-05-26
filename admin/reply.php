<?php
session_start();
include 'db.php';

// 验证 CSRF 令牌
// if (!isset($_GET['csrf_token']) || !validate_csrf_token($_GET['csrf_token'])) {
//     die("非法请求");
// }

// 获取留言 ID
$messageId = $_GET['id'];

try {
    // 查询留言信息
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = :id");
    $stmt->execute([':id' => $messageId]);
    $message = $stmt->fetch();

    if (!$message) {
        die("留言未找到");
    }
} catch (PDOException $e) {
    die("数据库操作失败：" . $e->getMessage());
}

// // 生成 CSRF 令牌
// function validate_csrf_token($token) {
//     return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
// }
// $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 生成新令牌
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>回复留言</title>
    <link href="https://cdn.jsdelivr.net/npm/modern-normalize@1.1.0/modern-normalize.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #111827;
            background-color: #f8f9fa;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: white;
            border-radius: 1.25rem;
            padding: 2rem;
            box-shadow: 0 4px 24px rgba(17, 24, 39, 0.05);
            width: 100%;
            max-width: 600px;
        }
        h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .message-info {
            margin-bottom: 1.5rem;
        }
        .message-info p {
            margin-bottom: 0.5rem;
        }
        label {
            display: block;
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
        button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        button:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>回复留言</h2>
        <div class="message-info">
            <p><strong>留言人姓名:</strong> <?= htmlspecialchars($message['nickname']) ?></p>
            <p><strong>留言内容:</strong> <?= htmlspecialchars($message['content']) ?></p>
        </div>
        <form method="post" action="save_reply.php">
            <input type="hidden" name="message_id" value="<?= $messageId ?>">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <label for="reply_content">回复内容:</label>
            <textarea id="reply_content" name="reply_content" rows="4" required></textarea>
            <button type="submit">提交回复</button>
        </form>
    </div>
</body>
</html>