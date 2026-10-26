<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => '로그인이 필요합니다.']);
    exit;
}

$db = getDB();
$userId = $_SESSION['user_id'];

// 댓글 목록 조회
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $postId = (int)($_GET['post_id'] ?? 0);
    $stmt = $db->prepare("
        SELECT comments.comment, comments.created_at, users.username
        FROM comments
        JOIN users ON comments.user_id = users.id
        WHERE comments.post_id = ?
        ORDER BY comments.created_at ASC
    ");
    $stmt->execute([$postId]);
    $rows = $stmt->fetchAll();
    echo json_encode(['comments' => $rows]);
    exit;
}

// 댓글 작성
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int)($_POST['post_id'] ?? 0);
    $text = trim($_POST['comment'] ?? '');

    if (!$postId || $text === '') {
        http_response_code(400);
        echo json_encode(['error' => '내용을 입력해주세요.']);
        exit;
    }
    if (mb_strlen($text) > 300) {
        http_response_code(400);
        echo json_encode(['error' => '댓글은 300자 이내로 작성해주세요.']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $userId, $text]);

    $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $username = $stmt->fetch()['username'];

    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM comments WHERE post_id = ?");
    $stmt->execute([$postId]);
    $count = $stmt->fetch()['cnt'];

    echo json_encode(['username' => $username, 'comment' => $text, 'count' => (int)$count]);
    exit;
}
