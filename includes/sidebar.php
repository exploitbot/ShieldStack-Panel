<div class="sidebar">
    <div class="sidebar-header">
        <svg width="35" height="35" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 2L4 10V18C4 27.5 10.5 36.1 20 38C29.5 36.1 36 27.5 36 18V10L20 2Z" stroke="#00d4ff" stroke-width="2" fill="rgba(0,212,255,0.1)"/>
            <path d="M20 12V28M14 20H26" stroke="#00d4ff" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <h2>ShieldStack</h2>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📊</span>
                <span>Dashboard</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Hosting</div>
            <a href="services.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'services.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🖥️</span>
                <span>My Services</span>
            </a>
            <a href="plans.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'plans.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📦</span>
                <span>Browse Plans</span>
            </a>
            <a href="domains.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'domains.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🌐</span>
                <span>Domains</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Billing</div>
            <a href="invoices.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'invoices.php' ? 'active' : ''; ?>">
                <span class="nav-icon">💳</span>
                <span>Invoices</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Support</div>
            <a href="tickets.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'tickets.php' ? 'active' : ''; ?>">
                <span class="nav-icon">🎫</span>
                <span>Support Tickets</span>
            </a>
            <a href="knowledgebase.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'knowledgebase.php' ? 'active' : ''; ?>">
                <span class="nav-icon">📚</span>
                <span>Knowledge Base</span>
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <span class="nav-icon">👤</span>
                <span>My Profile</span>
            </a>
            <?php if ($auth->isAdmin()): ?>
            <a href="admin/dashboard.php" class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span>Admin Panel</span>
            </a>
            <?php endif; ?>
            <a href="logout.php" class="nav-item">
                <span class="nav-icon">🚪</span>
                <span>Logout</span>
            </a>
        </div>
    </nav>
</div>
