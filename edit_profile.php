<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');
    $interests = buildInterestsString($_POST['interests'] ?? [], $_POST['interests_other'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $avatarFile = $user['profile_image'];

    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (isset($allowed[$mime]) && $file['size'] <= 4 * 1024 * 1024) {
            $ext = $allowed[$mime];
            $avatarFile = uniqid('avatar_', true) . '.' . $ext;
            $destDir = __DIR__ . '/uploads/avatars/';
            if (!is_dir($destDir)) mkdir($destDir, 0755, true);
            move_uploaded_file($file['tmp_name'], $destDir . $avatarFile);
        } else {
            $error = '프로필 사진은 jpg/png/webp, 4MB 이하만 가능합니다.';
        }
    }

    if ($error === '') {
        $stmt = $db->prepare("UPDATE users SET nickname = ?, interests = ?, bio = ?, profile_image = ? WHERE id = ?");
        $stmt->execute([$nickname, $interests, $bio, $avatarFile, $user['id']]);
        header('Location: profile.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>프로필 편집 · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <a href="profile.php" class="topbar-icon">
            <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="topbar-logo">프로필 편집</h1>
        <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
    </header>

    <main class="upload-main">
        <?php if ($error): ?><div class="auth-error"><?= h($error) ?></div><?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="edit-avatar-row">
                <img id="avatarPreview" class="profile-avatar" src="uploads/avatars/<?= h($user['profile_image']) ?>" onerror="this.src='assets/default_avatar.png'">
                <label class="btn-outline btn-inline">
                    사진 변경
                    <input type="file" name="avatar" id="avatarInput" accept="image/*" style="display:none">
                </label>
            </div>
            <input type="text" name="nickname" placeholder="별명" value="<?= h($user['nickname']) ?>">

            <div class="interest-picker">
                <div class="interest-picker-label">관심동물 (최대 <?= interestMaxCount() ?>개 선택)</div>
                <div class="interest-picker-options">
                    <?php
                        $savedInterests = parseInterests($user['interests']);
                        $standardOptions = interestOptions();
                        $customExisting = array_values(array_diff($savedInterests, $standardOptions));
                        $selectedInterests = array_values(array_intersect($savedInterests, $standardOptions));
                        if (!empty($customExisting)) {
                            $selectedInterests[] = '기타';
                        }
                    ?>
                    <?php foreach (interestOptions() as $opt): ?>
                        <label class="interest-chip">
                            <input type="checkbox" name="interests[]" value="<?= h($opt) ?>" <?= in_array($opt, $selectedInterests) ? 'checked' : '' ?>>
                            <span><?= h($opt) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <input type="text" name="interests_other" id="interestsOther" class="interest-other-input" placeholder="기타 관심동물을 콤마(,)로 구분해 입력하세요" value="<?= h(implode(', ', $customExisting)) ?>" style="<?= empty($customExisting) ? 'display:none' : '' ?>">
            </div>

            <textarea name="bio" placeholder="소개글" maxlength="150"><?= h($user['bio']) ?></textarea>
            <button type="submit" class="btn-primary">저장하기</button>
        </form>
    </main>

    <?php include __DIR__ . '/includes/bottomnav.php'; ?>
    <script src="js/interest-picker.js"></script>
    <script src="js/theme-toggle.js"></script>
    <script>
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreview = document.getElementById('avatarPreview');
        avatarInput.addEventListener('change', () => {
            if (avatarInput.files && avatarInput.files[0]) {
                avatarPreview.src = URL.createObjectURL(avatarInput.files[0]);
            }
        });
    </script>
</body>
</html>
