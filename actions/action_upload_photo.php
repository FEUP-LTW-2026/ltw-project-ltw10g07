<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';

if (!isLoggedIn()) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/account.php');
    exit;
}

$errors  = [];
$userId  = (int) getUserId();

if (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'Seleciona uma imagem para fazer upload.';
}

if ($errors === []) {
    $file  = $_FILES['photo'];
    $error = $file['error'];

    if ($error !== UPLOAD_ERR_OK) {
        $errors[] = match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O ficheiro é demasiado grande (máx. 2 MB).',
            default => 'Erro ao fazer upload do ficheiro.',
        };
    }
}

if ($errors === []) {
    $maxBytes  = 2 * 1024 * 1024; // 2 MB
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    if ($file['size'] > $maxBytes) {
        $errors[] = 'O ficheiro é demasiado grande (máx. 2 MB).';
    }

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMime, true)) {
        $errors[] = 'Só são aceites imagens JPEG, PNG ou WebP.';
    }
}

if ($errors !== []) {
    setFlashMessages('errors', $errors);
    header('Location: ../pages/account.php');
    exit;
}

$ext       = match ($mimeType) {
    'image/png'  => 'png',
    'image/webp' => 'webp',
    default      => 'jpg',
};
$uploadDir = __DIR__ . '/../uploads/profile_photos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename  = 'user_' . $userId . '_' . time() . '.' . $ext;
$destPath  = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    setFlashMessages('errors', ['Erro ao guardar a imagem. Tenta novamente.']);
    header('Location: ../pages/account.php');
    exit;
}

$db   = getDB();
$user = User::findById($db, $userId);
if ($user && !empty($user['photo']) && str_contains($user['photo'], 'uploads/profile_photos/')) {
    $oldPath = __DIR__ . '/../' . ltrim($user['photo'], '/');
    if (is_file($oldPath)) {
        @unlink($oldPath);
    }
}

$relativePath = '../uploads/profile_photos/' . $filename;

$stmt = $db->prepare('UPDATE user SET photo = ? WHERE id = ?');
$stmt->execute([$relativePath, $userId]);

setFlashMessages('success', ['Foto de perfil atualizada com sucesso!']);
header('Location: ../pages/account.php');
exit;
