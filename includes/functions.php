<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// 로그인 여부 확인
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// 비로그인 시 로그인 페이지로 이동
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    // 세션엔 로그인 정보가 남아있지만 DB에 해당 사용자가 없는 경우
    // (예: 데이터 초기화) 세션을 정리하고 로그인 페이지로 보낸다.
    if (!currentUser()) {
        session_unset();
        session_destroy();
        header('Location: login.php');
        exit;
    }
}

// 현재 로그인한 사용자 정보 조회
function currentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// XSS 방지용 출력 헬퍼
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// 게시물이 좋아요를 몇 개 받았는지, 내가 눌렀는지 체크
function getPostMeta($postId, $userId) {
    $db = getDB();

    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
    $stmt->execute([$postId]);
    $likeCount = $stmt->fetch()['cnt'];

    $liked = false;
    if ($userId) {
        $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $userId]);
        $liked = $stmt->fetch()['cnt'] > 0;
    }

    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM comments WHERE post_id = ?");
    $stmt->execute([$postId]);
    $commentCount = $stmt->fetch()['cnt'];

    return ['likes' => (int)$likeCount, 'liked' => $liked, 'comments' => (int)$commentCount];
}

// 게시물당 업로드 가능한 최대 이미지 수
function maxPostImages() {
    return 3;
}

// 게시물의 이미지 목록 조회 (다중 이미지). post_images에 기록이 없으면(기존 단일 이미지 게시물)
// posts.image_path 하나짜리 배열로 대체한다.
function getPostImages($postId, $fallbackImage = null) {
    $db = getDB();
    $stmt = $db->prepare("SELECT image_path FROM post_images WHERE post_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$postId]);
    $images = array_column($stmt->fetchAll(), 'image_path');
    if (empty($images) && $fallbackImage) {
        return [$fallbackImage];
    }
    return $images;
}

// 관심동물 선택 항목 (회원가입/프로필 편집에서 공통으로 사용)
function interestOptions() {
    return ['강아지', '고양이', '새', '물고기', '곤충', '기타'];
}

// 관심동물 최대 선택 개수 (기타 직접입력 항목 포함)
function interestMaxCount() {
    return 3;
}

// 저장된 interests 문자열("강아지,고양이")을 배열로 변환
function parseInterests($interestsStr) {
    if (!$interestsStr) return [];
    return array_filter(array_map('trim', explode(',', $interestsStr)));
}

// 폼에서 넘어온 체크박스 배열(+ 기타 직접입력)을 저장용 문자열로 변환 (최대 3개, 허용된 항목만 통과)
function buildInterestsString($selected, $customText = '') {
    if (!is_array($selected)) $selected = [];
    $max = interestMaxCount();
    $allowed = interestOptions();
    $valid = array_slice(array_values(array_intersect($selected, $allowed)), 0, $max);

    $result = array_values(array_diff($valid, ['기타']));

    if (in_array('기타', $valid, true)) {
        $remaining = $max - count($result);
        if ($remaining > 0) {
            $customItems = array_filter(array_map('trim', explode(',', $customText)));
            foreach ($customItems as $custom) {
                if (count($result) >= $max) break;
                $custom = mb_substr($custom, 0, 20);
                if ($custom !== '' && !in_array($custom, $result, true)) {
                    $result[] = $custom;
                }
            }
        }
    }

    return implode(',', $result);
}

// 팔로워 수 / 팔로잉 수 조회
function getFollowCounts($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM follows WHERE following_id = ?");
    $stmt->execute([$userId]);
    $followers = $stmt->fetch()['cnt'];

    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM follows WHERE follower_id = ?");
    $stmt->execute([$userId]);
    $following = $stmt->fetch()['cnt'];

    return ['followers' => (int)$followers, 'following' => (int)$following];
}

// $viewerId가 $targetId를 팔로우하고 있는지 여부
function isFollowing($viewerId, $targetId) {
    if (!$viewerId || !$targetId || $viewerId == $targetId) return false;
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$viewerId, $targetId]);
    return $stmt->fetch()['cnt'] > 0;
}

// 상대 시간 표시 (예: 3시간 전)
function timeAgo($datetime) {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return '방금 전';
    if ($diff < 3600) return floor($diff / 60) . '분 전';
    if ($diff < 86400) return floor($diff / 3600) . '시간 전';
    if ($diff < 604800) return floor($diff / 86400) . '일 전';
    return date('Y.m.d', strtotime($datetime));
}
