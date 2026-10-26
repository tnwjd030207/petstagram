<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>로그인 · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
        <div class="auth-card">
            <h1 class="auth-logo">🐾 Petstagram</h1>
            <p class="auth-sub">반려동물들을 위한 특별한 공간</p>

            <?php if ($error): ?>
                <div class="auth-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <input type="text" name="username" placeholder="아이디" required>
                <input type="password" name="password" placeholder="비밀번호" required>
                <button type="submit" class="btn-primary">로그인</button>
            </form>
        </div>
        <div class="auth-card auth-switch">
            계정이 없으신가요? <a href="register.php">가입하기</a>
        </div>
    </div>
    <script src="js/theme-toggle.js"></script>
</body>
</html>
