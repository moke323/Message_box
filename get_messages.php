<?php
session_start();
include __DIR__ . '/admin/db.php';

// 搜索参数
$search = $_GET['q'] ?? '';
$where = $search ? "WHERE (nickname LIKE :q OR content LIKE :q) AND is_display = 1" : "WHERE is_display = 1";

// 分页参数
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$perPage = 10;
$offset = ($page - 1) * $perPage;

// 带搜索和分页的查询
$stmt = $pdo->prepare("
    SELECT * FROM messages 
    {$where} 
    ORDER BY id DESC 
    LIMIT :offset, :perPage
");
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
if ($search) {
    $stmt->bindValue(':q', "%{$search}%", PDO::PARAM_STR);
}
$stmt->execute();
$messages = $stmt->fetchAll();

// 设置响应头为JSON格式
header('Content-Type: application/json');
// 输出JSON数据
echo json_encode($messages);
?>