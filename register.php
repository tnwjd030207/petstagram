<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $nickname = trim($_POST['nickname'] ?? '');
    $interests = buildInterestsString($_POST['interests'] ?? [], $_POST['interests_other'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $error = '필수 항목을 모두 입력해주세요.';
    } elseif (strlen($password) < 4) {
        $error = '비밀번호는 4자 이상이어야 합니다.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.]{2,30}$/', $username)) {
        $error = '아이디는 영문, 숫자, _, . 만 사용 가능합니다.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = '이미 사용 중인 아이디 또는 이메일입니다.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password_hash, nickname, interests) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hash, $nickname, $interests]);
            $_SESSION['user_id'] = $db->lastInsertId();
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>회원가입 · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
        <div class="auth-card">
            <h1 class="auth-logo">🐾 Petstagram</h1>
            <p class="auth-sub">우리 반려동물의 순간을 기록하고 나눠보세요</p>

            <?php if ($error): ?>
                <div class="auth-error"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <input type="text" name="username" placeholder="아이디" value="<?= h($_POST['username'] ?? '') ?>" required>
                <input type="email" name="email" placeholder="이메일" value="<?= h($_POST['email'] ?? '') ?>" required>
                <input type="password" name="password" placeholder="비밀번호 (4자 이상)" required>
                <input type="text" name="nickname" placeholder="별명 (선택)" value="<?= h($_POST['nickname'] ?? '') ?>">

                <div class="interest-picker">
                    <div class="interest-picker-label">관심동물 (최대 <?= interestMaxCount() ?>개 선택)</div>
                    <div class="interest-picker-options">
                        <?php $selectedInterests = $_POST['interests'] ?? []; ?>
                        <?php foreach (interestOptions() as $opt): ?>
                            <label class="interest-chip">
                                <input type="checkbox" name="interests[]" value="<?= h($opt) ?>" <?= in_array($opt, $selectedInterests) ? 'checked' : '' ?>>
                                <span><?= h($opt) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="interests_other" id="interestsOther" class="interest-other-input" placeholder="기타 관심동물을 콤마(,)로 구분해 입력하세요" value="<?= h($_POST['interests_other'] ?? '') ?>" style="display:none">
                </div>

                <button type="submit" class="btn-primary">가입하기</button>
            </form>
        </div>
        <div class="auth-card auth-switch">
            이미 계정이 있으신가요? <a href="login.php">로그인</a>
        </div>
    </div>
    <script src="js/interest-picker.js"></script>
    <script src="js/theme-toggle.js"></script>
</body>
</html>
