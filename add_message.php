<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'admin/db.php';
include 'admin/functions.php';

// 获取POST数据
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $nickname = $data['nickname'];
    $contact = $data['contact'];
    $content = $data['content'];
    $create_time = $data['create_time'];
    $is_anonymous = $data['is_anonymous'] ? 1 : 0;
    $is_display = $data['is_display'] ? 0 : 1;

    try {
        // 插入数据到数据库
        $stmt = $pdo->prepare("INSERT INTO messages (nickname, contact, content, create_time, is_anonymous, is_display) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nickname, $contact, $content, $create_time, $is_anonymous, $is_display]);

        // 发送邮件通知
        $adminEmail = '3069886684@qq.com'; 
        $mailSubject = '新的用户留言';
        $displayStatus = ($is_display == 1) ? "" : "<br><strong style='color:#ff4444'>（非展示留言）</strong>";
        $mailContent = "访问者 {$nickname} 提交了新的留言：<br><br>内容：{$content}{$displayStatus}<br><br>联系方式：{$contact}";

        // 使用send_email函数发送邮件
        $mailSent = send_email($adminEmail, $mailSubject, $mailContent);
        if ($mailSent) {
            error_log('邮件发送成功');
        } else {
            error_log('邮件发送失败');
        }

        // 返回成功响应
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => '留言提交成功', 'mail_sent' => $mailSent]);
    } catch (PDOException $e) {
        // 返回错误响应
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => '数据库操作失败：' . $e->getMessage()]);
    }
} else {
    // 返回错误响应
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => '无效的请求数据']);
}
?>