<?php
require_once __DIR__ . '/../../includes/auth.php';
if (!isset($auth)) {
    $auth = new Auth();
}
?>
<div class="topbar">
    <button class="mobile-menu-toggle" style="display: none;">☰</button>
    
    <div class="topbar-right">
        <div class="topbar-user">
            <span class="user-name-mobile"><?php echo htmlspecialchars(substr($auth->getCurrentCustomerName(), 0, 15)); ?></span>
            <a href="/panel/logout.php" class="btn btn-secondary btn-logout">Logout</a>
        </div>
    </div>
</div>
