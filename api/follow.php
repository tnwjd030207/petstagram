<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => '로그인이 필요합니다.']);
    exit;
}

$targetId = (int)($_POST['user_id'] ?? 0);
$myId = $_SESSION['user_id'];

if (!$targetId || $targetId === $myId) {
    http_response_code(400);
    echo json_encode(['error' => '잘못된 요청입니다.']);
    exit;
}

$db = getDB();

$stmt = $db->prepare("SELECT id FROM follows WHERE follower_id = ? AND following_id = ?");
$stmt->execute([$myId, $targetId]);
$existing = $stmt->fetch();

if ($existing) {
    $stmt = $db->prepare("DELETE FROM follows WHERE id = ?");
    $stmt->execute([$existing['id']]);
    $following = false;
} else {
    $stmt = $db->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
    $stmt->execute([$myId, $targetId]);
    $following = true;
}

$counts = getFollowCounts($targetId);

echo json_encode([
    'following' => $following,
    'followers' => $counts['followers'],
]);
