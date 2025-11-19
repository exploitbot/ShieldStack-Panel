<?php
declare(strict_types=1);

$uploadRoot = realpath(__DIR__ . '/../uploads') ?: __DIR__ . '/../uploads';
$logFile = __DIR__ . '/visitor-log.json';

ensureDirectory(dirname($logFile));

$visitor = collectVisitorIps();
$visitorLog = loadVisitorLog($logFile);
$now = time();

if ($visitor['primary'] !== null) {
    $ip = $visitor['primary'];
    $existing = $visitorLog[$ip] ?? [
        'ip' => $ip,
        'version' => $visitor['version'],
        'count' => 0,
        'first_seen' => $now,
        'last_seen' => $now,
    ];

    $existing['count'] = ($existing['count'] ?? 0) + 1;
    $existing['last_seen'] = $now;
    $existing['version'] = $visitor['version'];

    if (!isset($existing['first_seen'])) {
        $existing['first_seen'] = $now;
    }

    $visitorLog[$ip] = $existing;
    saveVisitorLog($logFile, $visitorLog);
}

$visitorRows = formatVisitorRows($visitorLog);
$permanentFiles = listUploads($uploadRoot . '/permanent', 'uploads/permanent');
$tempFiles = listUploads($uploadRoot . '/temp', 'uploads/temp');

function collectVisitorIps(): array
{
    $candidates = [];

    $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
    if (is_string($forwarded) && trim($forwarded) !== '') {
        foreach (explode(',', $forwarded) as $part) {
            $candidates[] = trim($part);
        }
    }

    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $header) {
        $value = $_SERVER[$header] ?? null;
        if (is_string($value) && trim($value) !== '') {
            $candidates[] = trim($value);
        }
    }

    $seen = [];
    $validIps = [];
    foreach ($candidates as $candidate) {
        $ip = filter_var($candidate, FILTER_VALIDATE_IP);
        if ($ip && !isset($seen[$ip])) {
            $seen[$ip] = true;
            $validIps[] = $ip;
        }
    }

    $primary = $validIps[0] ?? null;
    return [
        'primary' => $primary,
        'version' => ipVersion($primary),
        'all' => $validIps,
    ];
}

function ipVersion(?string $ip): string
{
    if ($ip === null) {
        return 'Unknown';
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        return 'IPv6';
    }

    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return 'IPv4';
    }

    return 'Unknown';
}

function loadVisitorLog(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function saveVisitorLog(string $path, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT);
    if ($json === false) {
        return;
    }

    file_put_contents($path, $json, LOCK_EX);
    @chmod($path, 0664);
}

function formatVisitorRows(array $log): array
{
    usort($log, function (array $a, array $b): int {
        return ($b['last_seen'] ?? 0) <=> ($a['last_seen'] ?? 0);
    });

    return $log;
}

function listUploads(string $directory, string $relativeBase): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $items = [];
    $iterator = new FilesystemIterator($directory, FilesystemIterator::SKIP_DOTS);

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isDir()) {
            continue;
        }

        $relative = $relativeBase . '/' . $fileInfo->getFilename();
        $items[] = [
            'name' => $fileInfo->getFilename(),
            'url' => buildPublicUrl($relative),
            'size' => $fileInfo->getSize(),
            'modified' => $fileInfo->getMTime(),
        ];
    }

    usort($items, static function (array $a, array $b): int {
        return ($b['modified'] ?? 0) <=> ($a['modified'] ?? 0);
    });

    return $items;
}

function ensureDirectory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function buildPublicUrl(string $relativePath): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $projectBase = trim(str_replace('\\', '/', dirname($scriptDir)), '/');
    $baseSegment = ($projectBase === '' || $projectBase === '.') ? '' : $projectBase . '/';
    $path = $baseSegment . ltrim($relativePath, '/');

    return sprintf('%s://%s/%s', $scheme, $host, $path);
}

function formatBytes(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }

    $units = ['KB', 'MB', 'GB'];
    $value = $bytes;
    foreach ($units as $unit) {
        $value /= 1024;
        if ($value < 1024) {
            return sprintf('%.1f %s', $value, $unit);
        }
    }

    return sprintf('%.1f TB', $value / 1024);
}

function formatTimestamp(?int $timestamp): string
{
    if ($timestamp === null) {
        return '—';
    }

    return date('Y-m-d H:i:s T', $timestamp);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor IPs & Uploads</title>
    <style>
        :root {
            color-scheme: dark;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --bg: #050c1f;
            --panel: #0f1b34;
            --border: #1f2a44;
            --muted: #9da9c3;
            --accent: #38bdf8;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(circle at 18% 20%, #122459, var(--bg) 70%);
            color: #e2e8f0;
            padding: clamp(1rem, 4vw, 2rem);
        }
        .page {
            max-width: 1024px;
            margin: 0 auto;
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: clamp(1.25rem, 4vw, 2rem);
            box-shadow: 0 18px 45px rgba(3, 6, 18, 0.75);
        }
        h1, h2 {
            margin: 0 0 0.5rem;
        }
        p.muted {
            color: var(--muted);
            margin: 0 0 1.25rem;
        }
        .card {
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 16px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .ip-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
        }
        .pill {
            display: inline-block;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            background: rgba(56, 189, 248, 0.15);
            color: var(--accent);
            font-weight: 600;
            font-size: 0.9rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        th, td {
            text-align: left;
            padding: 0.5rem 0.6rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.15);
        }
        th {
            font-weight: 700;
            color: var(--muted);
        }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.75rem;
        }
        .file-card {
            display: block;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 14px;
            padding: 0.75rem;
            background: rgba(10, 20, 44, 0.6);
            color: inherit;
            text-decoration: none;
        }
        .file-card:hover {
            border-color: var(--accent);
        }
        .file-card img {
            display: block;
            width: 100%;
            max-height: 180px;
            object-fit: contain;
            border-radius: 10px;
            background: #0b1328;
        }
        .file-meta {
            margin-top: 0.6rem;
            font-size: 0.9rem;
            color: var(--muted);
        }
        .section-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="section-head">
            <div>
                <h1>Visitor IPs</h1>
                <p class="muted">See IPv4/IPv6 hits and browse anything stored in <code>/upload/uploads/</code>.</p>
            </div>
            <?php if ($visitor['primary']): ?>
                <span class="pill"><?= htmlspecialchars($visitor['primary'], ENT_QUOTES) ?> (<?= htmlspecialchars($visitor['version'], ENT_QUOTES) ?>)</span>
            <?php endif; ?>
        </div>

        <div class="card">
            <strong>Your IP chain:</strong>
            <?php if (empty($visitor['all'])): ?>
                <div class="muted">No IP detected.</div>
            <?php else: ?>
                <div class="ip-summary">
                    <?php foreach ($visitor['all'] as $ip): ?>
                        <div>
                            <div><?= htmlspecialchars($ip, ENT_QUOTES) ?></div>
                            <small class="muted"><?= ipVersion($ip) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>All visitors</h2>
            <?php if (empty($visitorRows)): ?>
                <p class="muted">No visitors logged yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>IP</th>
                            <th>Type</th>
                            <th>Hits</th>
                            <th>Last seen</th>
                            <th>First seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitorRows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['ip'] ?? 'unknown', ENT_QUOTES) ?></td>
                                <td><?= htmlspecialchars($row['version'] ?? 'Unknown', ENT_QUOTES) ?></td>
                                <td><?= (int) ($row['count'] ?? 0) ?></td>
                                <td><?= formatTimestamp($row['last_seen'] ?? null) ?></td>
                                <td><?= formatTimestamp($row['first_seen'] ?? null) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="section-head">
                <h2>Uploads</h2>
                <span class="pill">uploads/permanent + uploads/temp</span>
            </div>

            <?php if (empty($permanentFiles) && empty($tempFiles)): ?>
                <p class="muted">No files found under <code>/upload/uploads/</code> yet.</p>
            <?php else: ?>
                <?php foreach ([['label' => 'Permanent', 'items' => $permanentFiles], ['label' => 'Temporary', 'items' => $tempFiles]] as $group): ?>
                    <h3><?= htmlspecialchars($group['label'], ENT_QUOTES) ?></h3>
                    <?php if (empty($group['items'])): ?>
                        <p class="muted"><?= htmlspecialchars($group['label'], ENT_QUOTES) ?> is empty.</p>
                    <?php else: ?>
                        <div class="gallery">
                            <?php foreach ($group['items'] as $file): ?>
                                <a class="file-card" href="<?= htmlspecialchars($file['url'], ENT_QUOTES) ?>" target="_blank" rel="noopener">
                                    <img src="<?= htmlspecialchars($file['url'], ENT_QUOTES) ?>" alt="<?= htmlspecialchars($file['name'], ENT_QUOTES) ?>">
                                    <div class="file-meta">
                                        <div><?= htmlspecialchars($file['name'], ENT_QUOTES) ?></div>
                                        <div><?= formatBytes((int) $file['size']) ?> • <?= formatTimestamp($file['modified'] ?? null) ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
