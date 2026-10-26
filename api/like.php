<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => '로그인이 필요합니다.']);
    exit;
}

$postId = (int)($_POST['post_id'] ?? 0);
$userId = $_SESSION['user_id'];

if (!$postId) {
    http_response_code(400);
    echo json_encode(['error' => '잘못된 요청입니다.']);
    exit;
}

$db = getDB();

$stmt = $db->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $db->prepare("DELETE FROM likes WHERE id = ?");
    $stmt->execute([$existing['id']]);
    $liked = false;
} else {
    $stmt = $db->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $stmt->execute([$postId, $userId]);
    $liked = true;
}

$stmt = $db->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
$stmt->execute([$postId]);
$count = $stmt->fetch()['cnt'];

echo json_encode(['liked' => $liked, 'count' => (int)$count]);
