<?php
// Load cache buster so we can force client asset refreshes when admins clear cache.
if (!function_exists('getCacheBusterVersion')) {
    require_once __DIR__ . '/cache-buster.php';
}
$__cacheBuster = getCacheBusterVersion();
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

<script>
    (function() {
        // Append cache-buster to asset URLs and reload once when version changes.
        const version = <?php echo json_encode($__cacheBuster); ?>;
        const key = 'ss_cache_buster';
        const reloadKey = 'ss_cache_buster_reload_' + version;
        const stored = localStorage.getItem(key);
        const needsReload = stored && stored !== version;

        localStorage.setItem(key, version);

        const bumpUrl = (urlString) => {
            try {
                const url = new URL(urlString, window.location.origin);
                url.searchParams.set('v', version);
                return url.toString();
            } catch (e) {
                return urlString;
            }
        };

        // Touch common asset tags
        document.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
            if (link.href && link.href.indexOf('/assets/') !== -1) {
                link.href = bumpUrl(link.href);
            }
        });

        document.querySelectorAll('script[src]').forEach(script => {
            if (script.src && script.src.indexOf('/assets/') !== -1) {
                script.src = bumpUrl(script.src);
            }
        });

        if (needsReload && !sessionStorage.getItem(reloadKey)) {
            sessionStorage.setItem(reloadKey, '1');
            window.location.reload(true);
        }
    })();
</script>
