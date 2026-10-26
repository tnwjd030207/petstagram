<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$me = currentUser();
$db = getDB();

$viewId = isset($_GET['id']) ? (int)$_GET['id'] : $me['id'];
$isMe = $viewId === (int)$me['id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$viewId]);
$profileUser = $stmt->fetch();

if (!$profileUser) {
    header('Location: index.php');
    exit;
}

$stmt = $db->prepare("
    SELECT posts.*, (SELECT COUNT(*) FROM post_images WHERE post_images.post_id = posts.id) as image_count
    FROM posts WHERE user_id = ? ORDER BY created_at DESC
");
$stmt->execute([$viewId]);
$posts = $stmt->fetchAll();

$stmt = $db->prepare("SELECT COUNT(*) as cnt FROM posts WHERE user_id = ?");
$stmt->execute([$viewId]);
$postCount = $stmt->fetch()['cnt'];

$followCounts = getFollowCounts($viewId);
$amFollowing = !$isMe && isFollowing($me['id'], $viewId);
$interests = parseInterests($profileUser['interests']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?= h($profileUser['username']) ?> · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <h1 class="topbar-logo profile-title"><?= h($profileUser['username']) ?></h1>
        <div class="topbar-actions">
            <?php if ($isMe): ?>
            <a href="logout.php" class="topbar-icon" title="로그아웃">
                <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="1.8" d="M15 3h3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-3M10 17l5-5-5-5M15 12H3"/></svg>
            </a>
            <?php endif; ?>
            <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
        </div>
    </header>

    <main class="profile-main">
        <section class="profile-info">
            <img class="profile-avatar" src="uploads/avatars/<?= h($profileUser['profile_image']) ?>" onerror="this.src='assets/default_avatar.png'">
            <div class="profile-stats">
                <div class="stat"><strong><?= $postCount ?></strong><span>게시물</span></div>
                <a href="follows.php?id=<?= $viewId ?>&mode=followers" class="stat">
                    <strong><?= $followCounts['followers'] ?></strong><span>팔로워</span>
                </a>
                <a href="follows.php?id=<?= $viewId ?>&mode=following" class="stat">
                    <strong><?= $followCounts['following'] ?></strong><span>팔로잉</span>
                </a>
            </div>
        </section>

        <section class="profile-bio">
            <?php if ($profileUser['nickname']): ?>
                <div class="profile-dogname">🐾 <?= h($profileUser['nickname']) ?></div>
            <?php endif; ?>
            <?php if (!empty($interests)): ?>
                <div class="interest-tags">
                    <?php foreach ($interests as $tag): ?>
                        <span class="interest-tag"><?= h($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($profileUser['bio']): ?>
                <div class="profile-bio-text"><?= h($profileUser['bio']) ?></div>
            <?php endif; ?>
        </section>

        <?php if ($isMe): ?>
        <a href="edit_profile.php" class="btn-outline profile-edit-btn">프로필 편집</a>
        <?php else: ?>
        <button class="btn-primary profile-edit-btn follow-toggle-btn <?= $amFollowing ? 'is-following' : '' ?>" data-user-id="<?= $viewId ?>" onclick="toggleFollowBtn(this)">
            <?= $amFollowing ? '팔로잉' : '팔로우' ?>
        </button>
        <?php endif; ?>

        <div class="profile-grid">
            <?php if (empty($posts)): ?>
                <div class="empty-state">게시물이 없습니다.</div>
            <?php endif; ?>
            <?php foreach ($posts as $post): ?>
                <a href="post.php?id=<?= $post['id'] ?>" class="grid-item">
                    <?php if ($post['image_count'] > 1): ?>
                        <svg class="grid-multi-icon" viewBox="0 0 24 24" width="16" height="16"><path fill="#fff" stroke="rgba(0,0,0,.35)" stroke-width=".5" d="M17 3H5a2 2 0 0 0-2 2v12h2V5h12V3Zm4 4H9a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Zm0 14H9V9h12v12Z"/></svg>
                    <?php endif; ?>
                    <img src="uploads/posts/<?= h($post['image_path']) ?>" alt="게시물">
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include __DIR__ . '/includes/bottomnav.php'; ?>
    <script src="js/app.js"></script>
    <script src="js/theme-toggle.js"></script>
</body>
</html>
