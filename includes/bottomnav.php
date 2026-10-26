<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$user = currentUser();
?>
<nav class="bottomnav">
    <a href="index.php" class="nav-item <?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 3 2 12h3v8h6v-6h2v6h6v-8h3z"/></svg>
    </a>
    <a href="search.php" class="nav-item <?= $currentPage === 'search.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" width="24" height="24"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><line x1="21" y1="21" x2="16.65" y2="16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </a>
    <a href="upload.php" class="nav-item nav-add <?= $currentPage === 'upload.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" width="26" height="26"><rect x="2.5" y="2.5" width="19" height="19" rx="6" fill="none" stroke="currentColor" stroke-width="1.8"/><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" d="M12 8v8m-4-4h8"/></svg>
    </a>
    <a href="activity.php" class="nav-item <?= $currentPage === 'activity.php' ? 'active' : '' ?>">
        <svg viewBox="0 0 24 24" width="24" height="24"><path fill="none" stroke="currentColor" stroke-width="2" d="M12 21s-7.5-4.6-10-9.3C.5 8 2 4.5 5.6 4c2.1-.3 4 .8 6.4 3.4C14.4 4.8 16.3 3.7 18.4 4c3.6.5 5.1 4 3.6 7.7C19.5 16.4 12 21 12 21Z"/></svg>
    </a>
    <a href="profile.php" class="nav-item nav-profile <?= $currentPage === 'profile.php' ? 'active' : '' ?>">
        <img src="uploads/avatars/<?= h($user['profile_image']) ?>" onerror="this.src='assets/default_avatar.png'" alt="프로필">
    </a>
</nav>
