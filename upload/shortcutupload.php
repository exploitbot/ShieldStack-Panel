<?php
declare(strict_types=1);

$maxFileSize = 10 * 1024 * 1024; // 10 MB limit to match index.php
$baseDir = __DIR__ . '/uploads';
$tempDir = $baseDir . '/temp';
$permanentDir = $baseDir . '/permanent';

ensureDirectory($tempDir);
ensureDirectory($permanentDir);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondError(405, 'Send a POST request with a file attached.');
}

$incomingFile = collectIncomingFile($maxFileSize);

if ($incomingFile === null) {
    respondError(400, 'No file upload detected.');
}

$size = $incomingFile['size'];
$tmpPath = $incomingFile['tmp_path'];

if ($size <= 0) {
    cleanupTempFile($incomingFile);
    respondError(400, 'Upload failed validation. Try again.');
}

if ($size > $maxFileSize) {
    cleanupTempFile($incomingFile);
    respondError(400, 'Image exceeds the 10MB upload cap.');
}

if ($incomingFile['is_native_upload'] && !is_uploaded_file($tmpPath)) {
    cleanupTempFile($incomingFile);
    respondError(400, 'Upload failed validation. Try again.');
}

$mimeType = detectMimeType($tmpPath);
$extension = mapExtension($mimeType);

if ($extension === null) {
    cleanupTempFile($incomingFile);
    respondError(400, 'Unsupported file type. Upload PNG, JPG, GIF, or WebP images.');
}

$requestData = array_merge($_GET ?? [], $_POST ?? []);
$keepForever = shouldKeepForever($requestData);
$targetDir = $keepForever ? $permanentDir : $tempDir;
$relativeBase = $keepForever ? 'uploads/permanent' : 'uploads/temp';
$targetName = generateFileName($extension);
$targetPath = $targetDir . '/' . $targetName;

if (!persistIncomingFile($incomingFile, $targetPath)) {
    cleanupTempFile($incomingFile);
    respondError(500, 'Server could not move the uploaded file. Check permissions.');
}

chmod($targetPath, 0664);
$publicUrl = buildPublicUrl($relativeBase . '/' . $targetName);

cleanupTempFile($incomingFile);
respondSuccess($publicUrl);

function respondSuccess(string $url): void
{
    header('Content-Type: text/plain; charset=UTF-8');
    echo $url;
    exit;
}

function respondError(int $code, string $message): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'ERROR: ' . $message;
    exit;
}

function collectIncomingFile(int $maxFileSize): ?array
{
    if (!empty($_FILES)) {
        foreach (flattenUploadedFiles($_FILES) as $file) {
            $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($errorCode !== UPLOAD_ERR_OK) {
                respondError(400, uploadErrorMessage($errorCode));
            }

            return [
                'tmp_path' => (string) ($file['tmp_name'] ?? ''),
                'size' => (int) ($file['size'] ?? 0),
                'mime' => $file['type'] ?? null,
                'original_name' => is_string($file['name'] ?? null) ? $file['name'] : null,
                'is_native_upload' => true,
                'needs_cleanup' => false,
            ];
        }
    }

    return readRawBodyIntoTemp($maxFileSize);
}

function flattenUploadedFiles(array $files): array
{
    $normalized = [];
    foreach ($files as $file) {
        if (!is_array($file)) {
            continue;
        }

        $name = $file['name'] ?? null;
        if (is_array($name)) {
            $types = is_array($file['type'] ?? null) ? $file['type'] : [];
            $tmpNames = is_array($file['tmp_name'] ?? null) ? $file['tmp_name'] : [];
            $errors = is_array($file['error'] ?? null) ? $file['error'] : [];
            $sizes = is_array($file['size'] ?? null) ? $file['size'] : [];

            foreach ($name as $index => $value) {
                $normalized[] = [
                    'name' => $value,
                    'type' => $types[$index] ?? null,
                    'tmp_name' => $tmpNames[$index] ?? null,
                    'error' => $errors[$index] ?? UPLOAD_ERR_NO_FILE,
                    'size' => (int) ($sizes[$index] ?? 0),
                ];
            }
        } else {
            $normalized[] = $file;
        }
    }

    return $normalized;
}

function readRawBodyIntoTemp(int $maxFileSize): ?array
{
    $input = fopen('php://input', 'rb');
    if (!$input) {
        return null;
    }

    $tempPath = tempnam(sys_get_temp_dir(), 'shortcutupload_');
    if ($tempPath === false) {
        fclose($input);
        return null;
    }

    $output = fopen($tempPath, 'wb');
    if (!$output) {
        fclose($input);
        @unlink($tempPath);
        return null;
    }

    $size = 0;
    while (!feof($input)) {
        $chunk = fread($input, 8192);
        if ($chunk === false) {
            fclose($input);
            fclose($output);
            @unlink($tempPath);
            return null;
        }

        if ($chunk === '') {
            continue;
        }

        $size += strlen($chunk);
        if ($size > $maxFileSize) {
            fclose($input);
            fclose($output);
            @unlink($tempPath);
            respondError(400, 'Image exceeds the 10MB upload cap.');
        }

        if (fwrite($output, $chunk) === false) {
            fclose($input);
            fclose($output);
            @unlink($tempPath);
            return null;
        }
    }

    fclose($input);
    fclose($output);

    if ($size === 0) {
        @unlink($tempPath);
        return null;
    }

    $contentType = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? null);
    $fileName = $_SERVER['HTTP_X_FILE_NAME'] ?? null;

    return [
        'tmp_path' => $tempPath,
        'size' => $size,
        'mime' => is_string($contentType) ? $contentType : null,
        'original_name' => is_string($fileName) ? $fileName : null,
        'is_native_upload' => false,
        'needs_cleanup' => true,
    ];
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

function detectMimeType(string $path): ?string
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

function shouldKeepForever(array $data): bool
{
    if (isset($data['storage'])) {
        return strtolower((string) $data['storage']) === 'permanent';
    }

    if (!isset($data['keep_forever'])) {
        return false;
    }

    $value = strtolower((string) $data['keep_forever']);
    return in_array($value, ['1', 'true', 'yes', 'permanent'], true);
}

function persistIncomingFile(array &$file, string $targetPath): bool
{
    if ($file['is_native_upload']) {
        $result = move_uploaded_file($file['tmp_path'], $targetPath);
        if ($result) {
            $file['needs_cleanup'] = false;
        }
        return $result;
    }

    $tempPath = $file['tmp_path'];

    if (@rename($tempPath, $targetPath)) {
        $file['needs_cleanup'] = false;
        return true;
    }

    $source = fopen($tempPath, 'rb');
    if (!$source) {
        @unlink($tempPath);
        return false;
    }

    $destination = fopen($targetPath, 'wb');
    if (!$destination) {
        fclose($source);
        @unlink($tempPath);
        return false;
    }

    $copied = stream_copy_to_stream($source, $destination);
    fclose($source);
    fclose($destination);

    if ($copied === false) {
        @unlink($tempPath);
        return false;
    }

    @unlink($tempPath);
    $file['needs_cleanup'] = false;
    return true;
}

function cleanupTempFile(?array $file): void
{
    if (!is_array($file)) {
        return;
    }

    if (($file['needs_cleanup'] ?? false) && !empty($file['tmp_path']) && file_exists($file['tmp_path'])) {
        @unlink($file['tmp_path']);
    }
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
    $base = ($scriptDir === '' || $scriptDir === '.') ? '' : $scriptDir . '/';

    return sprintf('%s://%s/%s%s', $scheme, $host, $base, ltrim($relativePath, '/'));
}
?>
