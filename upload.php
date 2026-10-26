<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$user = currentUser();
$error = '';

define('MAX_UPLOAD_BYTES', 20 * 1024 * 1024); // 20MB (이미지 1장당)
$maxUploadLabel = (MAX_UPLOAD_BYTES / 1024 / 1024) . 'MB';
$maxImages = maxPostImages();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption = trim($_POST['caption'] ?? '');

    if (empty($_FILES) && empty($_POST) && !empty($_SERVER['CONTENT_LENGTH'])) {
        // 서버의 post_max_size를 초과하면 PHP가 $_POST/$_FILES를 통째로 비워버림
        $error = "이미지 용량이 너무 커서 업로드할 수 없습니다. 사진 개수를 줄이거나 {$maxUploadLabel} 이하의 이미지로 시도해주세요.";
    } else {
        // input[name="images[]"]의 배열 형태를 파일 단위 목록으로 재구성 (빈 슬롯 제외)
        $files = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if ($name === '') continue;
                $files[] = [
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i],
                ];
            }
        }

        if (empty($files)) {
            $error = '이미지를 선택해주세요.';
        } elseif (count($files) > $maxImages) {
            $error = "사진은 최대 {$maxImages}장까지 업로드할 수 있습니다.";
        } else {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $validated = [];

            foreach ($files as $file) {
                if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                    $error = "이미지 용량이 너무 커서 업로드할 수 없습니다. {$maxUploadLabel} 이하의 이미지를 선택해주세요.";
                    break;
                }
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error = '업로드 중 오류가 발생했습니다. 다시 시도해주세요.';
                    break;
                }
                $mime = finfo_file($finfo, $file['tmp_name']);
                if (!isset($allowed[$mime])) {
                    $error = 'jpg, png, webp, gif 형식의 이미지만 업로드할 수 있습니다.';
                    break;
                }
                if ($file['size'] > MAX_UPLOAD_BYTES) {
                    $error = "이미지 용량은 장당 {$maxUploadLabel} 이하여야 합니다.";
                    break;
                }
                $validated[] = ['tmp_name' => $file['tmp_name'], 'ext' => $allowed[$mime]];
            }
            finfo_close($finfo);

            if (!$error) {
                $destDir = __DIR__ . '/uploads/posts/';
                if (!is_dir($destDir)) mkdir($destDir, 0755, true);

                $savedFilenames = [];
                foreach ($validated as $v) {
                    $filename = uniqid('dog_', true) . '.' . $v['ext'];
                    if (!move_uploaded_file($v['tmp_name'], $destDir . $filename)) {
                        $error = '업로드 중 오류가 발생했습니다. 다시 시도해주세요.';
                        break;
                    }
                    $savedFilenames[] = $filename;
                }

                if (!$error) {
                    $db = getDB();
                    $db->beginTransaction();
                    $stmt = $db->prepare("INSERT INTO posts (user_id, image_path, caption) VALUES (?, ?, ?)");
                    $stmt->execute([$user['id'], $savedFilenames[0], $caption]);
                    $postId = $db->lastInsertId();

                    $imgStmt = $db->prepare("INSERT INTO post_images (post_id, image_path, sort_order) VALUES (?, ?, ?)");
                    foreach ($savedFilenames as $order => $filename) {
                        $imgStmt->execute([$postId, $filename, $order]);
                    }
                    $db->commit();

                    header('Location: index.php');
                    exit;
                } else {
                    foreach ($savedFilenames as $filename) {
                        @unlink($destDir . $filename);
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>새 게시물 · Petstagram</title>
<script src="js/theme-init.js"></script>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="topbar">
        <a href="index.php" class="topbar-icon">
            <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" d="m15 18-6-6 6-6"/></svg>
        </a>
        <h1 class="topbar-logo">새 게시물</h1>
        <button type="button" class="theme-toggle-btn" aria-label="다크 모드로 전환" title="다크 모드로 전환">🌙</button>
    </header>

    <main class="upload-main">
        <?php if ($error): ?>
            <div class="auth-error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="upload-drop" id="uploadDrop">
                <div id="previewCarousel" class="upload-preview-carousel" style="display:none"></div>
                <div id="previewDots" class="carousel-dots" style="display:none"></div>
                <button type="button" id="changePhotoBtn" class="upload-change-btn" style="display:none">사진 변경</button>
                <div id="uploadPlaceholder" class="upload-placeholder">
                    <svg viewBox="0 0 24 24" width="48" height="48"><rect x="2.5" y="2.5" width="19" height="19" rx="6" fill="none" stroke="currentColor" stroke-width="1.4"/><circle cx="8.5" cy="9.5" r="1.8" fill="currentColor"/><path fill="none" stroke="currentColor" stroke-width="1.4" d="m4 17 5-5 3 3 4-5 4 5"/></svg>
                    <span>우리 반려동물 사진을 선택하세요 (최대 <?= $maxImages ?>장)</span>
                    <input type="file" name="images[]" id="imageInput" accept="image/*" multiple required>
                </div>
            </div>
            <p class="upload-hint" id="uploadHint" style="display:none">여러 장을 선택했다면 사진을 옆으로 넘겨 확인해보세요.</p>

            <textarea name="caption" placeholder="문구를 입력하세요... (예: 오늘도 산책 완료! 🐕)" maxlength="500"></textarea>

            <button type="submit" class="btn-primary">공유하기</button>
        </form>
    </main>

    <?php include __DIR__ . '/includes/bottomnav.php'; ?>
    <script src="js/app.js"></script>
    <script src="js/theme-toggle.js"></script>
    <script>
        const MAX_IMAGES = <?= $maxImages ?>;
        const input = document.getElementById('imageInput');
        const previewCarousel = document.getElementById('previewCarousel');
        const previewDots = document.getElementById('previewDots');
        const placeholder = document.getElementById('uploadPlaceholder');
        const changeBtn = document.getElementById('changePhotoBtn');
        const uploadHint = document.getElementById('uploadHint');

        function resetPreview() {
            previewCarousel.style.display = 'none';
            previewCarousel.innerHTML = '';
            previewDots.style.display = 'none';
            previewDots.innerHTML = '';
            changeBtn.style.display = 'none';
            uploadHint.style.display = 'none';
            placeholder.style.display = 'flex';
        }

        input.addEventListener('change', () => {
            const files = Array.from(input.files || []);

            if (files.length > MAX_IMAGES) {
                alert('사진은 최대 ' + MAX_IMAGES + '장까지 선택할 수 있습니다.');
                input.value = '';
                resetPreview();
                return;
            }

            if (files.length === 0) {
                resetPreview();
                return;
            }

            previewCarousel.innerHTML = '';
            files.forEach(file => {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.draggable = false;
                previewCarousel.appendChild(img);
            });
            previewCarousel.style.display = 'flex';
            enableCarouselDrag(previewCarousel);

            previewDots.innerHTML = '';
            if (files.length > 1) {
                files.forEach((_, i) => {
                    const dot = document.createElement('span');
                    dot.className = 'carousel-dot' + (i === 0 ? ' active' : '');
                    previewDots.appendChild(dot);
                });
                previewDots.style.display = 'flex';
                uploadHint.style.display = 'block';
            } else {
                previewDots.style.display = 'none';
                uploadHint.style.display = 'none';
            }

            changeBtn.style.display = 'block';
            placeholder.style.display = 'none';
        });

        changeBtn.addEventListener('click', () => input.click());
    </script>
</body>
</html>
