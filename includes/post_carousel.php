<?php
// 게시물 이미지 캐러셀 (index.php, post.php 공용)
// 사용 전 $post 배열에 id, image_path가 있어야 함
$images = getPostImages($post['id'], $post['image_path']);
?>
<div class="post-image-wrap">
    <div class="post-carousel">
        <?php foreach ($images as $img): ?>
            <img class="post-carousel-img" src="uploads/posts/<?= h($img) ?>" alt="게시물 이미지" ondblclick="doubleTapLike(this, <?= $post['id'] ?>)">
        <?php endforeach; ?>
    </div>
    <svg class="heart-burst" viewBox="0 0 24 24" width="80" height="80"><path fill="#fff" d="m12 21-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.18L12 21Z"/></svg>
    <?php if (count($images) > 1): ?>
    <div class="carousel-dots">
        <?php foreach ($images as $i => $img): ?>
            <span class="carousel-dot<?= $i === 0 ? ' active' : '' ?>"></span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
