<?php 
session_start(); 
include 'db.php';  
include 'functions.php';

// 生成一次 CSRF 令牌，供当前页面所有操作使用
$pageCsrfToken = generate_csrf_token();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// 搜索参数
$search = $_GET['q'] ?? '';
$where = $search ? "WHERE nickname LIKE :q OR content LIKE :q" : "";

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

// 计算总条数
$totalStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM messages {$where}");
if ($search) {
    $totalStmt->bindValue(':q', "%{$search}%", PDO::PARAM_STR);
}
$totalStmt->execute();
$total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>留言管理 - 后台</title>
    <!-- 现代设计依赖 -->
    <link href="https://cdn.jsdelivr.net/npm/modern-normalize@1.1.0/modern-normalize.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;        /* 主色-柔和蓝 */
            --primary-light: #e0f2fe;  /* 主色浅版 */
            --danger: #ef4444;         /* 危险色-珊瑚红 */
            --danger-light: #fee2e2;   /* 危险色浅版 */
            --bg: #f8f9fa;             /* 背景色 */
            --text: #111827;           /* 主文本 */
            --text-secondary: #6b7280; /* 次文本 */
            --border: #e5e7eb;         /* 边框色 */
            --card-shadow: 0 4px 24px rgba(17, 24, 39, 0.05);
            --radius-lg: 1.25rem;      /* 大圆角 */
            --radius-md: 0.75rem;      /* 中等圆角 */
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background-color: var(--bg);
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* 头部导航 */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .logo-icon {
            font-size: 1.75rem;
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--danger);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md);
            border: none;
            font-size: 0.9375rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        /* 搜索区域 */
        .search-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2.5rem;
            animation: fadeInUp 0.5s ease-out;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            max-width: max-width;
        }

        .search-input {
            flex: 1;
            padding: 1rem 1.25rem;
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }

        .search-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary);
            color: white;
            padding: 0 2rem;
            border: none;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .search-btn:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        /* 留言列表 */
        .message-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
            animation: fadeIn 0.5s ease-out;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-title-icon {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .total-badge {
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th,
        .custom-table td {
            padding: 1.25rem 1.5rem;
            text-align: left;
        }

        .custom-table th {
            background: var(--primary);
            color: white;
            font-weight: 500;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .custom-table th:first-child {
            border-radius: var(--radius-lg) 0 0 0;
        }

        .custom-table th:last-child {
            border-radius: 0 var(--radius-lg) 0 0;
        }

        .custom-table tbody tr {
            transition: background 0.2s;
        }

        .custom-table tbody tr:hover {
            background: var(--primary-light);
        }

        .custom-table tbody td {
            border-bottom: 1px solid var(--border);
        }

        .content-cell {
            max-width: 30ch;
            word-break: break-word;
            color: var(--text-secondary);
        }

        .time-cell {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .action-btn {
            color: var(--danger);
            background: var(--danger-light);
            padding: 0.5rem;
            border-radius: var(--radius-md);
            display: inline-flex;
            transition: all 0.2s;
        }

        .action-btn:hover {
            background: var(--danger);
            color: white;
            transform: scale(1.05);
        }

        /* 分页 */
        .pagination {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 2.5rem;
            flex-wrap: wrap;
            list-style-type: none;
        }

        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .page-link:not(.active):not(.disabled) {
            color: var(--primary);
            background: white;
            border: 1px solid var(--border);
        }

        .page-link:not(.active):not(.disabled):hover {
            background: var(--primary-light);
            border-color: var(--primary);
        }

        .page-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .page-link.disabled {
            color: var(--text-secondary);
            background: #f3f4f6;
            cursor: not-allowed;
        }

        /* 动画 */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* 响应式 */
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1.5rem;
                align-items: flex-start;
            }

            .search-card {
                padding: 1.5rem;
            }

            .search-form {
                flex-direction: column;
            }

            .search-btn {
                padding: 1rem;
                justify-content: center;
            }

            .custom-table th,
            .custom-table td {
                padding: 1rem 1.25rem;
            }

            .page-link {
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 头部 -->
        <div class="header">
            <div class="logo">
                <i class="fas fa-comments logo-icon"></i>
                留言管理系统
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> 退出登录
            </a>
        </div>

        <!-- 搜索卡片 -->
        <div class="search-card">
            <form class="search-form" method="get">
                <input type="text" name="q" class="search-input" 
                       placeholder="搜索昵称、内容或联系方式" 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> 搜索
                </button>
            </form>
        </div>

        <!-- 留言卡片 -->
        <div class="message-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-envelope-open-text card-title-icon"></i>
                    最新留言列表
                    <span class="total-badge"><?= $total ?> 条</span>
                </div>
            </div>

            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>昵称</th>
                            <th>联系方式</th>
                            <th>内容</th>
                            <th>时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
    <tr>
        <td><?= $msg['id'] ?></td>
        <td><?= htmlspecialchars($msg['nickname']) ?></td>
        <td><?= htmlspecialchars($msg['contact']) ?></td>
        <td class="content-cell">
            <?= htmlspecialchars($msg['content']) ?>
            <?php if ($msg['is_display'] == 0): ?>
                <span style="color: red;">（非展示）</span>
            <?php endif; ?>
        </td>
        <td class="time-cell"><?= date('Y-m-d H:i', strtotime($msg['create_time'])) ?></td>
        
        
        
        <td>
            <?php if (has_permission('delete')): ?>
                <!-- 所有删除链接使用同一 CSRF 令牌 -->
                <a href="delete.php?id=<?= $msg['id'] ?>&csrf_token=<?= $pageCsrfToken ?>" 
                   class="action-btn"
                   onclick="return confirm('确认删除此留言？')">
                    <i class="fas fa-trash"></i>
                </a>
            <?php endif; ?>
            
            <?php if (has_permission('delete')): ?>
                <a href="reply.php?id=<?= $msg['id'] ?>" 
                   class="action-btn"
                   onclick="return confirm('回复此留言？')">
                    <i class="fas fa-reply"></i>
                </a>
            <?php endif; ?>
            
        </td>
    </tr>
<?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        

        <!-- 分页导航 -->
        <div class="pagination">
            <li class="page-item <?= $page === 1 ? 'disabled' : '' ?>">
                <a href="?page=<?= $page - 1 ?>&q=<?= $search ?>" class="page-link">
                    上一页
                </a>
            </li>
            <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a href="?page=<?= $i ?>&q=<?= $search ?>" class="page-link">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor ?>
            <li class="page-item <?= $page >= ceil($total / $perPage) ? 'disabled' : '' ?>">
                <a href="?page=<?= $page + 1 ?>&q=<?= $search ?>" class="page-link">
                
                    下一页
                </a>
            </li>
        </div>
    </div>
    
    </body>
</html>