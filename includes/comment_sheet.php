<?php
// 댓글 아이콘 클릭 시 아래에서 위로 올라오는 댓글 상세 보기 바텀시트 (index.php, post.php 공용)
?>
<div id="commentSheetBackdrop" class="comment-sheet-backdrop" hidden></div>
<div id="commentSheet" class="comment-sheet" hidden>
    <div class="comment-sheet-handle"></div>
    <div class="comment-sheet-header">
        <span>댓글</span>
        <button type="button" class="comment-sheet-close" aria-label="닫기">✕</button>
    </div>
    <div class="comment-sheet-list" id="commentSheetList"></div>
    <form class="comment-form comment-sheet-form" id="commentSheetForm">
        <input type="text" id="commentSheetInput" placeholder="댓글 달기...">
        <button type="submit">게시</button>
    </form>
</div>
