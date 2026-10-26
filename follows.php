<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$me = currentUser();
$db = getDB();

$viewId = isset($_GET['id']) ? (int)$_GET['id'] : $me['id'];
$mode = ($_GET['mode'] ?? 'followers') === 'following' ? 'following' : 'followers';

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$viewId]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    header('Location: index.php');
    exit;
}

if ($mode === 'followers') {
    // 이 사람을 팔로우하는 사람들
    $stmt = $db->prepare("
        SELECT users.* FROM follows
        JOIN users ON follows.follower_id = users.id
        WHERE follows.following_id = ?
        ORDER BY follows.created_at DESC
    ");
} else {
    // 이 사람이 팔로우하는 사람들
    $stmt = $db->prepare("
        SELECT users.* FROM follows
        JOIN users ON follows.following_id = users.id
        WHERE follows.follower_id = ?
        ORDER BY follows.created_at DESC
    ");
}
$stmt->execute([$viewId]);
$list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?= $mode === 'followers' ? '팔로워' : '팔로잉' ?> · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <a href="profile.php?id=<?= $viewId ?>" class="topbar-icon">
            <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="topbar-logo profile-title"><?= h($targetUser['username']) ?></h1>
        <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
    </header>

    <nav class="subtabs">
        <a href="follows.php?id=<?= $viewId ?>&mode=followers" class="subtab <?= $mode === 'followers' ? 'active' : '' ?>">팔로워</a>
        <a href="follows.php?id=<?= $viewId ?>&mode=following" class="subtab <?= $mode === 'following' ? 'active' : '' ?>">팔로잉</a>
    </nav>

    <main class="search-results">
        <?php if (empty($list)): ?>
            <div class="empty-state"><?= $mode === 'followers' ? '아직 팔로워가 없습니다.' : '아직 팔로우한 사람이 없습니다.' ?></div>
        <?php endif; ?>
        <?php foreach ($list as $u):
            $mutual = isFollowing($me['id'], $u['id']);
            $isSelf = $u['id'] === $me['id'];
        ?>
            <div class="search-item">
                <a href="profile.php?id=<?= $u['id'] ?>" class="search-item-link">
                    <img class="post-avatar" src="uploads/avatars/<?= h($u['profile_image']) ?>" onerror="this.src='assets/default_avatar.png'">
                    <div>
                        <div class="post-username"><?= h($u['username']) ?></div>
                        <?php if ($u['nickname']): ?><div class="post-dogname">🐾 <?= h($u['nickname']) ?></div><?php endif; ?>
                    </div>
                </a>
                <?php if (!$isSelf): ?>
                <button class="btn-outline btn-follow-small <?= $mutual ? 'is-following' : '' ?>" data-user-id="<?= $u['id'] ?>" onclick="toggleFollowBtn(this)">
                    <?= $mutual ? '팔로잉' : '팔로우' ?>
                </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </main>

    <?php include __DIR__ . '/includes/bottomnav.php'; ?>
    <script src="js/app.js"></script>
    <script src="js/theme-toggle.js"></script>
</body>
</html>
