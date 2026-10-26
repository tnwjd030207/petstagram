<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$db = getDB();

$postId = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("
    SELECT posts.*, users.username, users.nickname, users.profile_image, users.id as owner_id
    FROM posts JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: index.php');
    exit;
}

$meta = getPostMeta($post['id'], $user['id']);
$canDelete = $post['owner_id'] === $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && $canDelete) {
    $imagesToDelete = getPostImages($post['id'], $post['image_path']);
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    foreach ($imagesToDelete as $img) {
        @unlink(__DIR__ . '/uploads/posts/' . $img);
    }
    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?= h($post['username']) ?>의 게시물 · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <a href="javascript:history.back()" class="topbar-icon">
            <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="topbar-logo">게시물</h1>
        <div class="topbar-actions">
            <?php if ($canDelete): ?>
            <form method="POST" onsubmit="return confirm('이 게시물을 삭제할까요?')">
                <button type="submit" name="delete" value="1" class="topbar-icon" style="background:none;border:none;">
                    <svg viewBox="0 0 24 24" width="22" height="22"><path fill="none" stroke="currentColor" stroke-width="1.8" d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
            <?php endif; ?>
            <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
        </div>
    </header>

    <main class="feed">
        <article class="post" data-post-id="<?= $post['id'] ?>">
            <a href="profile.php?id=<?= $post['user_id'] ?>" class="post-header">
                <img class="post-avatar" src="uploads/avatars/<?= h($post['profile_image']) ?>" onerror="this.src='assets/default_avatar.png'">
                <div class="post-header-text">
                    <span class="post-username"><?= h($post['username']) ?></span>
                    <?php if ($post['nickname']): ?><span class="post-dogname">🐾 <?= h($post['nickname']) ?></span><?php endif; ?>
                </div>
            </a>

            <?php include __DIR__ . '/includes/post_carousel.php'; ?>

            <div class="post-actions">
                <button class="action-btn like-btn <?= $meta['liked'] ? 'liked' : '' ?>" onclick="toggleLike(<?= $post['id'] ?>, this)">
                    <svg viewBox="0 0 24 24" width="26" height="26"><path fill="<?= $meta['liked'] ? '#ed4956' : 'none' ?>" stroke="<?= $meta['liked'] ? '#ed4956' : 'currentColor' ?>" stroke-width="1.8" d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.18L12 21Z"/></svg>
                </button>
                <button class="action-btn" onclick="focusComment(<?= $post['id'] ?>)">
                    <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="1.8" d="M20.656 17.008a9.5 9.5 0 1 0-3.59 3.615L21 21.5Z"/></svg>
                </button>
            </div>

            <div class="post-likes"><span class="like-count"><?= $meta['likes'] ?></span>명이 좋아합니다</div>

            <?php if ($post['caption']): ?>
            <div class="post-caption"><span class="post-username"><?= h($post['username']) ?></span> <?= h($post['caption']) ?></div>
            <?php endif; ?>

            <div class="post-comments-preview">
                <div class="comment-list" id="comments-<?= $post['id'] ?>"></div>
            </div>

            <div class="post-time"><?= timeAgo($post['created_at']) ?></div>

            <form class="comment-form" onsubmit="return submitComment(event, <?= $post['id'] ?>)">
                <input type="text" id="comment-input-<?= $post['id'] ?>" placeholder="댓글 달기...">
                <button type="submit">게시</button>
            </form>
        </article>
    </main>

    <?php include __DIR__ . '/includes/bottomnav.php'; ?>
    <?php include __DIR__ . '/includes/comment_sheet.php'; ?>
    <script src="js/app.js"></script>
    <script src="js/theme-toggle.js"></script>
    <script>loadComments(<?= $post['id'] ?>);</script>
</body>
</html>
