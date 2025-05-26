<?php
session_start();
include 'db.php';
include 'functions.php';

// 验证 CSRF 令牌
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    die("非法请求");
}

// 获取留言 ID 和回复内容
$messageId = $_POST['message_id'];
$replyContent = $_POST['reply_content'];
$replyTime = date('Y-m-d H:i:s');

try {
    // 更新留言表，添加回复信息
    $stmt = $pdo->prepare("UPDATE messages SET reply_content = :reply_content, reply_time = :reply_time WHERE id = :id");
    $stmt->execute([
        ':reply_content' => $replyContent,
        ':reply_time' => $replyTime,
        ':id' => $messageId
    ]);
    
    // 1. 查询留言详细信息
$stmt = $pdo->prepare("SELECT nickname, contact, is_anonymous, content FROM messages WHERE id = :id");
$stmt->execute([':id' => $messageId]);
$message = $stmt->fetch();

// 2. 检查是否为非匿名且联系方式是邮箱
if ($message && $message['is_anonymous'] == 0) {
    $contact = $message['contact'];
    $emailRegex = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/'; //邮箱正则
    
    if (preg_match($emailRegex, $contact)) {
        // 3. 发送邮件
        $subject = '「时光迹·留言箱」留言回复提醒';
        $mailContent = "您好，{$message['nickname']}：<br><br>"
                    . " “于正浩” 回复了您的留言 “{$message['content']}” ：<br><br>"
                    . "{$replyContent}<br><br>"
                    ."<span>&nbsp;</span>"
                    ."<span>&nbsp;</span><br>"
                    . "<div style='text-align: right; margin: 10px;'>「时光迹」团队</div>"
                    . "<div style='text-align: right; margin: 5px;'>www.yuzh.ink</div>";
                     
                     
        
//      $subject = '您的留言已收到回复';
//      $mailContent = "尊敬的 {$message['nickname']}：<br><br>"
//              . "您的留言内容：<br><blockquote style='background:#f8f9fa; padding:12px; border-left:4px solid #ddd; margin:10px 0;'>"
//              . htmlspecialchars($message['content']) // 转义防止XSS
//              . "</blockquote>"
//              . "管理员回复如下：<br><blockquote style='background:#f8f9fa; padding:12px; border-left:4px solid #165DFF; margin:10px 0;'>"
//              . htmlspecialchars($replyContent)
//              . "</blockquote>"
//              . "<br>——时光迹团队";
        
        
        include __DIR__ . '/admin/functions.php'; // 引入 functions.php
        $mailSent = send_email($contact, $subject, $mailContent);
        
        if (!$mailSent) {
            error_log("邮件发送失败：{$contact}");
        }
    }
}
    
    // 重定向到 dashboard.php
    header("Location: dashboard.php");
    exit;
} catch (PDOException $e) {
    die("数据库操作失败：" . $e->getMessage());
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && $token === $_SESSION['csrf_token'];
}
?>