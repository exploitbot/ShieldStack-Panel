<?php
declare(strict_types=1);

$messages = [];
$uploadedUrl = null;
$uploadedRelativePath = null;
$uploadDir = __DIR__ . '/uploads';
$tempDir = $uploadDir . '/temp';
$permanentDir = $uploadDir . '/permanent';
$maxFileSize = 10 * 1024 * 1024; // 10 MB
$keepForeverRequested = isset($_POST['keep_forever']) && $_POST['keep_forever'] === '1';

ensureDirectory($tempDir);
ensureDirectory($permanentDir);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clear_temp'])) {
        [$deleted, $failed] = clearTempUploads($tempDir);
        $text = $failed === 0
            ? sprintf('Cleared %d temporary file(s).', $deleted)
            : sprintf('Cleared %d temporary file(s), %d failed. Check permissions.', $deleted, $failed);
        $messages[] = ['type' => $failed === 0 ? 'success' : 'error', 'text' => $text];
    }

    $hasUpload = isset($_FILES['image']) && is_array($_FILES['image']);
    if (!$hasUpload) {
        if (!isset($_POST['clear_temp'])) {
            $messages[] = ['type' => 'error', 'text' => 'No file upload detected.'];
        }
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $messages[] = ['type' => 'error', 'text' => uploadErrorMessage((int) $_FILES['image']['error'])];
    } elseif ($_FILES['image']['size'] > $maxFileSize) {
        $messages[] = ['type' => 'error', 'text' => 'Image exceeds the 10MB upload limit.'];
    } else {
        $tmpPath = $_FILES['image']['tmp_name'];
        $fileSize = (int) $_FILES['image']['size'];

        if ($fileSize <= 0 || !is_uploaded_file($tmpPath)) {
            $messages[] = ['type' => 'error', 'text' => 'Upload failed. Try again.'];
        } else {
            $mimeType = getMimeType($tmpPath);
            $extension = mapExtension($mimeType);

            if ($extension === null) {
                $messages[] = ['type' => 'error', 'text' => 'Unsupported file type. Please upload a PNG, JPG, GIF, or WebP image.'];
            } else {
                $targetDir = $keepForeverRequested ? $permanentDir : $tempDir;
                $relativeBase = $keepForeverRequested ? 'uploads/permanent' : 'uploads/temp';
                $targetName = generateFileName($extension);
                $targetPath = $targetDir . '/' . $targetName;

                if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true)) {
                    $messages[] = ['type' => 'error', 'text' => 'Could not create the storage directory.'];
                } else {
                    error_clear_last();
                    if (!move_uploaded_file($tmpPath, $targetPath)) {
                        logMoveFailure($targetPath);
                        $messages[] = ['type' => 'error', 'text' => moveFailureMessage($targetDir)];
                    } else {
                        chmod($targetPath, 0664);
                        $uploadedRelativePath = $relativeBase . '/' . $targetName;
                        $uploadedUrl = buildPublicUrl($uploadedRelativePath);
                        $messages[] = [
                            'type' => 'success',
                            'text' => $keepForeverRequested
                                ? 'Image uploaded and pinned permanently.'
                                : 'Image uploaded to the temporary pool (auto-clears weekly).',
                        ];
                    }
                }
            }
        }
    }
}

function uploadErrorMessage(int $errorCode): string
{
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Image exceeds the configured upload limit.',
        UPLOAD_ERR_PARTIAL => 'Upload did not complete. Please try again.',
        UPLOAD_ERR_NO_FILE => 'No file was selected for upload.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error: missing temp directory.',
        UPLOAD_ERR_CANT_WRITE => 'Server is not permitted to save the upload.',
        UPLOAD_ERR_EXTENSION => 'Upload blocked by a PHP extension.',
        default => 'Unexpected upload error. Please retry.',
    };
}

function getMimeType(string $path): ?string
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if (!$finfo) {
        return null;
    }

    $type = finfo_file($finfo, $path);
    finfo_close($finfo);
    return $type ?: null;
}

function mapExtension(?string $mimeType): ?string
{
    return match ($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => null,
    };
}

function generateFileName(string $extension): string
{
    $token = rtrim(strtr(base64_encode(random_bytes(6)), '+/', '-_'), '=');
    return sprintf('%s.%s', $token, $extension);
}

function moveFailureMessage(string $targetDir): string
{
    if (!is_dir($targetDir)) {
        return 'Destination directory is missing on the server. Contact support.';
    }

    if (!is_writable($targetDir)) {
        return 'Server cannot write to the destination folder. Ping an admin to fix permissions.';
    }

    return 'Moving the uploaded file failed. Please retry in a moment.';
}

function logMoveFailure(string $targetPath): void
{
    $error = error_get_last();
    $details = $error['message'] ?? 'unknown reason';
    error_log(sprintf('[shieldstack-upload] move_uploaded_file failed for %s: %s', $targetPath, $details));
}

function buildPublicUrl(string $relativePath): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = trim(str_replace('\\', '/', dirname($scriptName)), '/');
    $baseSegment = $basePath === '' || $basePath === '.' ? '' : $basePath . '/';

    return sprintf('%s://%s/%s%s', $scheme, $host, $baseSegment, ltrim($relativePath, '/'));
}

function ensureDirectory(string $path): void
{
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function clearTempUploads(string $dir): array
{
    if (!is_dir($dir)) {
        return [0, 0];
    }

    $deleted = 0;
    $failed = 0;
    $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        $path = $item->getPathname();
        $result = $item->isDir() ? deleteDirectory($path) : @unlink($path);
        if ($result) {
            $deleted++;
        } else {
            $failed++;
        }
    }

    return [$deleted, $failed];
}

function deleteDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return true;
    }

    $iterator = new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS);
    foreach ($iterator as $item) {
        $path = $item->getPathname();
        if ($item->isDir()) {
            if (!deleteDirectory($path)) {
                return false;
            }
        } else {
            if (!@unlink($path)) {
                return false;
            }
        }
    }

    return @rmdir($dir);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Lightning-fast single tap uploader for Shieldstack.">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Shield Upload">
    <link rel="manifest" href="/upload/manifest.webmanifest?v=2">
    <link rel="apple-touch-icon" href="/upload/icons/apple-touch-icon.png?v=2">
    <link rel="icon" type="image/svg+xml" href="/upload/favicon.svg?v=2">
    <title>Shieldstack Uploads</title>
    <style>
        :root {
            color-scheme: dark;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --bg: #050c1f;
            --bg-soft: #0a142c;
            --panel: #0f1b34;
            --border: #1f2a44;
            --text: #e2e8f0;
            --muted: #9da9c3;
            --accent: #38bdf8;
            --accent-soft: #22d3ee;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 5vw, 2rem);
            background: radial-gradient(circle at 20% 20%, #122459, var(--bg) 70%);
            color: var(--text);
        }
        .panel {
            width: min(480px, 100%);
            background: var(--panel);
            border-radius: 28px;
            border: 1px solid var(--border);
            padding: clamp(1.25rem, 5vw, 2.75rem);
            box-shadow: 0 18px 45px rgba(3, 6, 18, 0.75);
        }
        .logo-stack {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .logo-badge {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 20px;
            display: grid;
            place-items: center;
            background: linear-gradient(140deg, var(--accent), var(--accent-soft));
            box-shadow: 0 12px 24px rgba(34, 211, 238, 0.4);
        }
        .logo-badge svg {
            width: 34px;
            height: 34px;
            fill: #041228;
        }
        h1 {
            margin: 0;
            font-size: clamp(1.7rem, 5vw, 2.2rem);
        }
        p.subhead {
            margin: 0.35rem 0 0;
            color: var(--muted);
            font-size: 1rem;
        }
        form.upload-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        input[type="file"] {
            padding: 1rem;
            border-radius: 18px;
            border: 1px dashed rgba(148, 163, 184, 0.55);
            background: var(--bg-soft);
            color: var(--muted);
        }
        button.primary {
            padding: 1rem;
            border: none;
            border-radius: 18px;
            background: linear-gradient(120deg, var(--accent), var(--accent-soft));
            color: #041228;
            font-weight: 700;
            cursor: pointer;
            font-size: 1rem;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            box-shadow: 0 10px 18px rgba(34, 211, 238, 0.35);
        }
        button.primary:active {
            transform: translateY(1px);
            box-shadow: inset 0 2px 6px rgba(4, 18, 40, 0.45);
        }
        .message {
            padding: 0.85rem 1rem;
            border-radius: 16px;
            margin-bottom: 1rem;
        }
        .message.error {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.4);
        }
        .message.success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.4);
        }
        .link-box {
            margin-top: 1rem;
            padding: 0.9rem 1rem;
            border-radius: 16px;
            background: rgba(148, 163, 184, 0.12);
            font-size: 0.95rem;
            word-break: break-all;
        }
        .link-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 0.5rem;
        }
        .copy-btn {
            border: none;
            border-radius: 999px;
            padding: 0.35rem 0.9rem;
            font-size: 0.8rem;
            font-weight: 600;
            background: rgba(56, 189, 248, 0.15);
            color: var(--accent);
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .copy-btn:hover {
            background: rgba(56, 189, 248, 0.3);
        }
        .copy-btn.copied {
            color: #0f172a;
            background: var(--accent);
        }
        .storage-toggle {
            display: flex;
            gap: 0.75rem;
            align-items: flex-start;
            background: rgba(148, 163, 184, 0.08);
            border-radius: 18px;
            padding: 0.9rem 1rem;
        }
        .storage-toggle input {
            margin-top: 0.25rem;
        }
        .storage-toggle span {
            display: block;
            font-weight: 600;
        }
        .storage-toggle small {
            display: block;
            color: var(--muted);
            font-size: 0.85rem;
        }
        .temp-manager {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 18px;
            background: rgba(15, 23, 42, 0.65);
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .temp-manager p {
            margin: 0 0 0.75rem;
            color: var(--muted);
            font-size: 0.9rem;
        }
        .ghost-btn {
            border: 1px solid rgba(56, 189, 248, 0.5);
            color: var(--accent);
            background: transparent;
            border-radius: 16px;
            padding: 0.75rem 1rem;
            font-weight: 600;
            cursor: pointer;
        }
        .footer-note {
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--muted);
            text-align: center;
        }
        .cta-row {
            margin-top: 0.75rem;
            font-size: 0.85rem;
            text-align: center;
            color: #cbd5f5;
        }
        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }
            .panel {
                border-radius: 22px;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            button.primary {
                transition: none;
            }
            button.primary:active {
                transform: none;
                box-shadow: 0 10px 18px rgba(34, 211, 238, 0.2);
            }
        }
    </style>
</head>
<body>
    <main class="panel">
        <div class="logo-stack">
            <div class="logo-badge" aria-hidden="true">
                <svg viewBox="0 0 64 64" role="presentation" focusable="false">
                    <defs>
                        <linearGradient id="logoGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#38bdf8" />
                            <stop offset="100%" stop-color="#22d3ee" />
                        </linearGradient>
                    </defs>
                    <rect x="4" y="4" width="56" height="56" rx="16" fill="url(#logoGradient)" />
                    <rect x="4" y="4" width="56" height="56" rx="16" fill="#050c1f" opacity="0.35" />
                    <path d="M18 36c-2.7 0-4.9-2.2-4.9-4.9S15.3 26 18 26c.5 0 1 .1 1.5.2A7.5 7.5 0 0 1 26 22a7.5 7.5 0 0 1 7.1 5.3c.5-.1.9-.2 1.4-.2 2.7 0 4.9 2.2 4.9 4.9 0 2.7-2.4 5-5.5 5H18z" fill="#ffffff" fill-opacity="0.9" />
                    <path d="M32 17l7.5 7.5H34v9h-4v-9h-5.5z" fill="#050c1f" />
                    <rect x="24" y="38" width="16" height="4" rx="1" fill="#ffffff" fill-opacity="0.85" />
                </svg>
            </div>
            <h1>Shieldstack Upload</h1>
            <p class="subhead">Tap once, share everywhere. Pick temporary or permanent storage.</p>
        </div>

        <?php foreach ($messages as $message): ?>
            <div class="message <?= htmlspecialchars($message['type'], ENT_QUOTES) ?>">
                <?= htmlspecialchars($message['text'], ENT_QUOTES) ?>
            </div>
        <?php endforeach; ?>

        <?php if ($uploadedUrl): ?>
            <div class="link-box">
                <div class="link-head">
                    <span>Direct link</span>
                    <button class="copy-btn" type="button" data-copy="<?= htmlspecialchars($uploadedUrl, ENT_QUOTES) ?>">Copy</button>
                </div>
                <a href="<?= htmlspecialchars($uploadedUrl, ENT_QUOTES) ?>" target="_blank" rel="noopener">
                    <?= htmlspecialchars($uploadedUrl, ENT_QUOTES) ?>
                </a>
            </div>
            <div class="link-box">
                <div class="link-head">
                    <span>Relative path</span>
                    <button class="copy-btn" type="button" data-copy="<?= htmlspecialchars($uploadedRelativePath ?? '', ENT_QUOTES) ?>">Copy</button>
                </div>
                <?= htmlspecialchars($uploadedRelativePath ?? '', ENT_QUOTES) ?>
            </div>
        <?php endif; ?>

        <form class="upload-form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxFileSize ?>">
            <input type="file" name="image" accept="image/png,image/jpeg,image/gif,image/webp" required>
            <label class="storage-toggle">
                <input type="checkbox" name="keep_forever" value="1" <?= $keepForeverRequested ? 'checked' : '' ?>>
                <div>
                    <span>Keep this upload forever</span>
                    <small>Unchecked files live in <code>uploads/temp</code> and vanish weekly unless you clear them sooner.</small>
                </div>
            </label>
            <button class="primary" type="submit">Upload</button>
        </form>

        <div class="temp-manager">
            <p>Temporary uploads auto-purge via cron, but you can wipe them instantly.</p>
            <form method="post" data-confirm="Clear all temporary uploads now? This cannot be undone.">
                <input type="hidden" name="clear_temp" value="1">
                <button type="submit" class="ghost-btn">Clear temporary uploads</button>
            </form>
        </div>

        <p class="footer-note">Permanent files: /var/www/html/upload/uploads/permanent<br>Temporary files: /var/www/html/upload/uploads/temp</p>
        <p class="cta-row">Tip: on iOS tap Share → “Add to Home Screen” for a one-tap uploader.</p>
    </main>

    <script>
        (() => {
            const buttons = document.querySelectorAll('.copy-btn');
            if (buttons.length) {
                const fallbackCopy = (text) => {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.focus();
                    textarea.select();
                    try {
                        document.execCommand('copy');
                    } finally {
                        document.body.removeChild(textarea);
                    }
                };

                buttons.forEach((btn) => {
                    btn.addEventListener('click', async () => {
                        const text = btn.dataset.copy;
                        if (!text) {
                            return;
                        }

                        try {
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                await navigator.clipboard.writeText(text);
                            } else {
                                fallbackCopy(text);
                            }
                            btn.classList.add('copied');
                            btn.textContent = 'Copied!';
                            setTimeout(() => {
                                btn.classList.remove('copied');
                                btn.textContent = 'Copy';
                            }, 1800);
                        } catch (err) {
                            btn.textContent = 'Failed';
                            setTimeout(() => {
                                btn.classList.remove('copied');
                                btn.textContent = 'Copy';
                            }, 1800);
                        }
                    });
                });
            }

            document.querySelectorAll('form[data-confirm]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const message = form.getAttribute('data-confirm');
                    if (message && !window.confirm(message)) {
                        event.preventDefault();
                    }
                });
            });
        })();
    </script>
</body>
</html>
