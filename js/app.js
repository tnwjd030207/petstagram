// ===== Petstagram 프론트엔드 스크립트 =====

// 좋아요 토글 (버튼 클릭)
function toggleLike(postId, btnEl) {
    fetch('api/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + encodeURIComponent(postId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) return;
        const article = btnEl.closest('.post');
        const countEl = article.querySelector('.like-count');
        countEl.textContent = data.count;

        if (data.liked) {
            btnEl.classList.add('liked');
        } else {
            btnEl.classList.remove('liked');
        }
        const svgPath = btnEl.querySelector('path');
        svgPath.setAttribute('fill', data.liked ? '#ed4956' : 'none');
        svgPath.setAttribute('stroke', data.liked ? '#ed4956' : 'currentColor');

        btnEl.classList.add('like-anim');
        setTimeout(() => btnEl.classList.remove('like-anim'), 350);
    })
    .catch(() => {});
}

// 이미지 더블탭(더블클릭) 시 좋아요 + 하트 애니메이션
function doubleTapLike(imgEl, postId) {
    const wrap = imgEl.closest('.post-image-wrap');
    const heart = wrap.querySelector('.heart-burst');
    const article = imgEl.closest('.post');
    const likeBtn = article.querySelector('.like-btn');

    // 이미 좋아요 상태가 아니면 좋아요 처리, 눌러진 상태여도 하트 애니메이션은 보여줌
    if (!likeBtn.classList.contains('liked')) {
        toggleLike(postId, likeBtn);
    }

    heart.classList.remove('pop');
    void heart.offsetWidth; // reflow to restart animation
    heart.classList.add('pop');
}

// 캐러셀(피드의 여러 장 게시물, 업로드 미리보기 공용) 마우스 드래그로도 넘길 수 있게 지원.
// 터치는 브라우저 기본 스와이프(scroll-snap)가 이미 동작하므로 마우스 포인터일 때만 관여한다.
function enableCarouselDrag(container) {
    if (!container || container.dataset.dragEnabled) return;
    container.dataset.dragEnabled = '1';
    let dragging = false, startX = 0, startScroll = 0;

    container.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse') return;
        dragging = true;
        startX = e.clientX;
        startScroll = container.scrollLeft;
        container.classList.add('dragging');
        container.setPointerCapture(e.pointerId);
    });
    container.addEventListener('pointermove', (e) => {
        if (!dragging) return;
        container.scrollLeft = startScroll - (e.clientX - startX);
    });
    const stopDrag = () => {
        dragging = false;
        container.classList.remove('dragging');
    };
    container.addEventListener('pointerup', stopDrag);
    container.addEventListener('pointercancel', stopDrag);
    container.addEventListener('pointerleave', stopDrag);
}

document.querySelectorAll('.post-carousel').forEach(enableCarouselDrag);

// 캐러셀 스와이프 시 하단 점 표시 동기화
// scroll 이벤트는 버블링되지 않으므로 캡처 단계에서 document에 위임해 감지한다.
document.addEventListener('scroll', function (e) {
    const carousel = e.target.closest && (e.target.closest('.post-carousel') || e.target.closest('.upload-preview-carousel'));
    if (!carousel) return;
    const wrap = carousel.closest('.post-image-wrap') || carousel.closest('.upload-drop');
    const dots = wrap ? wrap.querySelectorAll('.carousel-dot') : [];
    if (!dots.length) return;
    const index = Math.round(carousel.scrollLeft / carousel.clientWidth);
    dots.forEach((d, i) => d.classList.toggle('active', i === index));
}, true);

// ===== 댓글 바텀시트 (댓글 아이콘 클릭 시 아래에서 위로 올라와 자세히 보기) =====
const commentSheet = document.getElementById('commentSheet');
const commentSheetBackdrop = document.getElementById('commentSheetBackdrop');
const commentSheetList = document.getElementById('commentSheetList');
const commentSheetForm = document.getElementById('commentSheetForm');
const commentSheetInput = document.getElementById('commentSheetInput');
let currentSheetPostId = null;

function renderSheetComments(comments) {
    commentSheetList.innerHTML = '';
    if (!comments.length) {
        commentSheetList.innerHTML = '<div class="comment-sheet-empty">아직 댓글이 없어요. 첫 댓글을 남겨보세요.</div>';
        return;
    }
    comments.forEach(c => {
        const line = document.createElement('div');
        line.className = 'comment-line';
        line.innerHTML = '<span class="post-username"></span> <span class="comment-text"></span>';
        line.querySelector('.post-username').textContent = c.username;
        line.querySelector('.comment-text').textContent = c.comment;
        commentSheetList.appendChild(line);
    });
}

function openCommentSheet(postId) {
    if (!commentSheet) return;
    currentSheetPostId = String(postId);
    commentSheetList.innerHTML = '<div class="comment-sheet-empty">불러오는 중...</div>';
    if (commentSheetInput) commentSheetInput.value = '';

    commentSheetBackdrop.hidden = false;
    commentSheet.hidden = false;
    requestAnimationFrame(() => {
        commentSheetBackdrop.classList.add('show');
        commentSheet.classList.add('show');
    });

    fetch('api/comment.php?post_id=' + encodeURIComponent(postId))
        .then(res => res.json())
        .then(data => {
            if (currentSheetPostId !== String(postId)) return; // 그 사이 시트가 닫히거나 다른 게시물로 바뀐 경우 무시
            renderSheetComments(data.comments || []);
        })
        .catch(() => {
            commentSheetList.innerHTML = '<div class="comment-sheet-empty">댓글을 불러오지 못했습니다.</div>';
        });
}

function closeCommentSheet() {
    if (!commentSheet) return;
    commentSheet.classList.remove('show');
    commentSheetBackdrop.classList.remove('show');
    setTimeout(() => {
        commentSheet.hidden = true;
        commentSheetBackdrop.hidden = true;
    }, 250);
    currentSheetPostId = null;
}

if (commentSheetBackdrop) commentSheetBackdrop.addEventListener('click', closeCommentSheet);

document.addEventListener('click', function (e) {
    if (e.target.closest && e.target.closest('.comment-sheet-close')) closeCommentSheet();
});

if (commentSheetForm) {
    commentSheetForm.addEventListener('submit', function (evt) {
        evt.preventDefault();
        const postId = currentSheetPostId;
        const text = commentSheetInput.value.trim();
        if (!text || !postId) return;

        fetch('api/comment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'post_id=' + encodeURIComponent(postId) + '&comment=' + encodeURIComponent(text)
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            if (commentSheetList.querySelector('.comment-sheet-empty')) commentSheetList.innerHTML = '';

            const line = document.createElement('div');
            line.className = 'comment-line';
            line.innerHTML = '<span class="post-username"></span> <span class="comment-text"></span>';
            line.querySelector('.post-username').textContent = data.username;
            line.querySelector('.comment-text').textContent = data.comment;
            commentSheetList.appendChild(line);
            commentSheetList.scrollTop = commentSheetList.scrollHeight;
            commentSheetInput.value = '';

            // 피드에 보이는 댓글 수 배지도 함께 갱신하고, 인라인 목록은 다음에 다시 불러오도록 표시
            const article = document.querySelector('.post[data-post-id="' + postId + '"]');
            if (article) {
                const countEl = article.querySelector('.comment-count');
                if (countEl) countEl.textContent = data.count;
                const inlineList = article.querySelector('.comment-list');
                if (inlineList) inlineList.dataset.loaded = '0';
            }
        })
        .catch(() => {});
    });
}

// 댓글 아이콘 클릭 시 댓글 바텀시트 열기
function focusComment(postId) {
    openCommentSheet(postId);
}

// 댓글 "모두 보기" 클릭 시 로드
document.addEventListener('click', function (e) {
    const link = e.target.closest('.view-comments');
    if (link) {
        e.preventDefault();
        loadComments(link.dataset.postId, true);
    }
});

function loadComments(postId, forceOpen) {
    const list = document.getElementById('comments-' + postId);
    if (!list) return;
    if (list.dataset.loaded === '1' && !forceOpen) return;

    fetch('api/comment.php?post_id=' + encodeURIComponent(postId))
        .then(res => res.json())
        .then(data => {
            if (!data.comments) return;
            list.innerHTML = '';
            data.comments.forEach(c => {
                const line = document.createElement('div');
                line.className = 'comment-line';
                line.innerHTML = '<span class="post-username"></span> <span class="comment-text"></span>';
                line.querySelector('.post-username').textContent = c.username;
                line.querySelector('.comment-text').textContent = c.comment;
                list.appendChild(line);
            });
            list.dataset.loaded = '1';
        })
        .catch(() => {});
}

// 댓글 작성 폼 제출
function submitComment(evt, postId) {
    evt.preventDefault();
    const input = document.getElementById('comment-input-' + postId);
    const text = input.value.trim();
    if (!text) return false;

    fetch('api/comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'post_id=' + encodeURIComponent(postId) + '&comment=' + encodeURIComponent(text)
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }
        const list = document.getElementById('comments-' + postId);
        const line = document.createElement('div');
        line.className = 'comment-line';
        line.innerHTML = '<span class="post-username"></span> <span class="comment-text"></span>';
        line.querySelector('.post-username').textContent = data.username;
        line.querySelector('.comment-text').textContent = data.comment;
        list.appendChild(line);
        list.dataset.loaded = '1';

        const article = input.closest('.post');
        const countEl = article.querySelector('.comment-count');
        if (countEl) countEl.textContent = data.count;

        input.value = '';
    })
    .catch(() => {});

    return false;
}

// 페이지 로드 시 각 게시물의 댓글 미리보기(최근 댓글 일부) 로드는
// 성능을 위해 "모두 보기" 클릭 시에만 로드하도록 구성되어 있습니다.

// 팔로우 / 언팔로우 토글 (프로필 상단 버튼, 팔로워·팔로잉 목록의 버튼 공용)
function toggleFollowBtn(btnEl) {
    const userId = btnEl.dataset.userId;
    btnEl.disabled = true;

    fetch('api/follow.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'user_id=' + encodeURIComponent(userId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) { alert(data.error); return; }

        btnEl.textContent = data.following ? '팔로잉' : '팔로우';
        btnEl.classList.toggle('is-following', data.following);

        // 프로필 상단에 팔로워 수 표시가 있으면 함께 갱신
        const followerStat = document.querySelector('.stat[href*="mode=followers"] strong');
        if (followerStat) followerStat.textContent = data.followers;
    })
    .catch(() => {})
    .finally(() => { btnEl.disabled = false; });
}
