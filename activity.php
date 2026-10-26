<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$db = getDB();

// 내 게시물에 달린 좋아요/댓글을 최신순으로
$stmt = $db->prepare("
    SELECT 'like' as type, likes.created_at as created_at, users.username, posts.id as post_id, posts.image_path
    FROM likes
    JOIN posts ON likes.post_id = posts.id
    JOIN users ON likes.user_id = users.id
    WHERE posts.user_id = ? AND likes.user_id != ?
    UNION ALL
    SELECT 'comment' as type, comments.created_at as created_at, users.username, posts.id as post_id, posts.image_path
    FROM comments
    JOIN posts ON comments.post_id = posts.id
    JOIN users ON comments.user_id = users.id
    WHERE posts.user_id = ? AND comments.user_id != ?
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->execute([$user['id'], $user['id'], $user['id'], $user['id']]);
$activities = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>활동 · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <h1 class="topbar-logo">알림</h1>
        <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
    </header>

    <main class="activity-list">
        <?php if (empty($activities)): ?>
            <div class="empty-state">아직 활동 내역이 없어요.</div>
        <?php endif; ?>
        <?php foreach ($activities as $a): ?>
            <a href="post.php?id=<?= $a['post_id'] ?>" class="activity-item">
                <span class="post-username"><?= h($a['username']) ?></span>
                <span><?= $a['type'] === 'like' ? '님이 회원님의 게시물을 좋아합니다.' : '님이 댓글을 남겼습니다.' ?></span>
                <span class="activity-time"><?= timeAgo($a['created_at']) ?></span>
                <img class="activity-thumb" src="uploads/posts/<?= h($a['image_path']) ?>">
            </a>
        <?php endforeach; ?>
    </main>

    <?php include __DIR__ . '/includes/bottomnav.php'; ?>
    <script src="js/theme-toggle.js"></script>
</body>
</html>
