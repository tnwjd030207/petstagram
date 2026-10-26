<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$user = currentUser();
$db = getDB();

// 팔로잉 피드: 내가 팔로우한 사람(맞팔 포함) + 내 게시물만 최신순으로
$stmt = $db->prepare("
    SELECT posts.*, users.username, users.nickname, users.profile_image
    FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.user_id = ?
       OR posts.user_id IN (SELECT following_id FROM follows WHERE follower_id = ?)
    ORDER BY posts.created_at DESC
    LIMIT 50
");
$stmt->execute([$user['id'], $user['id']]);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/topbar.php'; ?>

    <main class="feed">
        <?php if (empty($posts)): ?>
            <div class="empty-state">
                <p>팔로우한 계정의 게시물이 없어요 🐶</p>
                <a href="search.php" class="btn-primary btn-inline">돋보기에서 둘러보기</a>
            </div>
        <?php endif; ?>

        <?php foreach ($posts as $post):
            $meta = getPostMeta($post['id'], $user['id']);
        ?>
        <article class="post" data-post-id="<?= $post['id'] ?>">
            <a href="profile.php?id=<?= $post['user_id'] ?>" class="post-header">
                <img class="post-avatar" src="uploads/avatars/<?= h($post['profile_image']) ?>" onerror="this.src='assets/default_avatar.png'">
                <div class="post-header-text">
                    <span class="post-username"><?= h($post['username']) ?></span>
                    <?php if ($post['nickname']): ?>
                        <span class="post-dogname">🐾 <?= h($post['nickname']) ?></span>
                    <?php endif; ?>
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
                <a href="#" class="view-comments" data-post-id="<?= $post['id'] ?>">
                    댓글 <span class="comment-count"><?= $meta['comments'] ?></span>개 모두 보기
                </a>
                <div class="comment-list" id="comments-<?= $post['id'] ?>"></div>
            </div>

            <div class="post-time"><?= timeAgo($post['created_at']) ?></div>

            <form class="comment-form" onsubmit="return submitComment(event, <?= $post['id'] ?>)">
                <input type="text" id="comment-input-<?= $post['id'] ?>" placeholder="댓글 달기...">
                <button type="submit">게시</button>
            </form>
        </article>
        <?php endforeach; ?>
    </main>

    <?php include __DIR__ . '/includes/bottomnav.php'; ?>
    <?php include __DIR__ . '/includes/comment_sheet.php'; ?>
    <script src="js/app.js"></script>
    <script src="js/theme-toggle.js"></script>
</body>
</html>
