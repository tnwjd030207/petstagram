<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$db = getDB();

$q = trim($_GET['q'] ?? '');
$results = [];
$explorePosts = [];

if ($q !== '') {
    $stmt = $db->prepare("SELECT * FROM users WHERE username LIKE ? OR nickname LIKE ? LIMIT 30");
    $like = '%' . $q . '%';
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll();
} else {
    // 돋보기 기본 화면: 맞팔/팔로우 여부와 상관없이 가입한 모든 사용자의 게시물을 노출
    $stmt = $db->prepare("
        SELECT posts.id, posts.image_path,
               (SELECT COUNT(*) FROM post_images WHERE post_images.post_id = posts.id) as image_count
        FROM posts
        ORDER BY posts.created_at DESC
        LIMIT 60
    ");
    $stmt->execute();
    $explorePosts = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>검색 · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <form method="GET" class="search-form">
            <svg viewBox="0 0 24 24" width="18" height="18"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <input type="text" name="q" placeholder="아이디 또는 별명 검색" value="<?= h($q) ?>" autofocus>
        </form>
        <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
    </header>

    <main class="search-results">
        <?php if ($q !== ''): ?>
            <?php if (empty($results)): ?>
                <div class="empty-state">검색 결과가 없습니다.</div>
            <?php endif; ?>
            <?php foreach ($results as $r): ?>
                <a href="profile.php?id=<?= $r['id'] ?>" class="search-item">
                    <img class="post-avatar" src="uploads/avatars/<?= h($r['profile_image']) ?>" onerror="this.src='assets/default_avatar.png'">
                    <div>
                        <div class="post-username"><?= h($r['username']) ?></div>
                        <?php if ($r['nickname']): ?><div class="post-dogname">🐾 <?= h($r['nickname']) ?></div><?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <?php if (empty($explorePosts)): ?>
                <div class="empty-state">아직 게시물이 없어요.</div>
            <?php endif; ?>
            <div class="profile-grid">
                <?php foreach ($explorePosts as $p): ?>
                    <a href="post.php?id=<?= $p['id'] ?>" class="grid-item">
                        <?php if ($p['image_count'] > 1): ?>
                            <svg class="grid-multi-icon" viewBox="0 0 24 24" width="16" height="16"><path fill="#fff" stroke="rgba(0,0,0,.35)" stroke-width=".5" d="M17 3H5a2 2 0 0 0-2 2v12h2V5h12V3Zm4 4H9a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Zm0 14H9V9h12v12Z"/></svg>
                        <?php endif; ?>
                        <img src="uploads/posts/<?= h($p['image_path']) ?>" alt="게시물">
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include __DIR__ . '/includes/bottomnav.php'; ?>
    <script src="js/theme-toggle.js"></script>
</body>
</html>
