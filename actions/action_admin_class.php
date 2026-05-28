<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/ClassCatalog.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

function redirectAdminClasses(array $errors = [], array $success = []): void {
    if ($errors !== []) {
        setFlashMessages('errors', $errors);
    }

    if ($success !== []) {
        setFlashMessages('success', $success);
    }

    header('Location: ../pages/admin.php#classes');
    exit;
}

function uploadClassImage(?array $file, array &$errors): ?string {
    if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        $errors[] = match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'A imagem é demasiado grande (máx. 2 MB).',
            default => 'Erro ao fazer upload da imagem.',
        };
        return null;
    }

    $maxBytes = 2 * 1024 * 1024;
    if ((int) $file['size'] > $maxBytes) {
        $errors[] = 'A imagem é demasiado grande (máx. 2 MB).';
        return null;
    }

    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, $allowedMime, true)) {
        $errors[] = 'Só são aceites imagens JPEG, PNG ou WebP.';
        return null;
    }

    $ext = match ($mimeType) {
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg',
    };

    $uploadDir = __DIR__ . '/../uploads/class_images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'class_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $errors[] = 'Erro ao guardar a imagem. Tenta novamente.';
        return null;
    }

    return '../uploads/class_images/' . $filename;
}

function deleteUploadedClassImage(string $image): void {
    if ($image === '' || !str_contains($image, 'uploads/class_images/')) {
        return;
    }

    $relativePath = preg_replace('#^\.\./#', '', $image);
    $path = __DIR__ . '/../' . ltrim($relativePath, '/');
    if (is_file($path)) {
        @unlink($path);
    }
}

if (!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/admin.php');
    exit;
}

verifyCsrfToken('../pages/admin.php#classes');

$db = getDB();
$action = $_POST['action'] ?? '';
$classId = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 0;
$trainerId = isset($_POST['trainer_id']) ? (int) $_POST['trainer_id'] : 0;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$type = trim($_POST['type'] ?? '');
$intensity = $_POST['intensity'] ?? '';
$capacity = (int) ($_POST['capacity'] ?? 0);
$errors = [];
$existingClass = null;

if ($action === 'delete') {
    if ($classId <= 0) {
        redirectAdminClasses(['Aula inválida.']);
    }

    ClassCatalog::delete($db, $classId);
    redirectAdminClasses([], ['Aula removida com sucesso.']);
}

if (!in_array($action, ['create', 'update'], true)) {
    redirectAdminClasses(['Ação inválida.']);
}

if ($action === 'update') {
    if ($classId <= 0) {
        redirectAdminClasses(['Aula inválida.']);
    }

    $existingClass = ClassCatalog::findById($db, $classId);
    if ($existingClass === null) {
        redirectAdminClasses(['Aula inválida.']);
    }
}

if ($name === '') {
    $errors[] = 'O nome da aula é obrigatório.';
}

if ($type === '') {
    $errors[] = 'O tipo da aula é obrigatório.';
}

if (!in_array($intensity, ['Baixa', 'Média', 'Alta'], true)) {
    $errors[] = 'A intensidade selecionada não é válida.';
}

if ($capacity < 1 || $capacity > 80) {
    $errors[] = 'A capacidade deve estar entre 1 e 80 pessoas.';
}

if ($trainerId <= 0) {
    $errors[] = 'Seleciona o treinador da aula.';
} else {
    $stmt = $db->prepare('SELECT COUNT(*) FROM trainer WHERE id = ?');
    $stmt->execute([$trainerId]);

    if ((int) $stmt->fetchColumn() === 0) {
        $errors[] = 'O treinador selecionado não é válido.';
    }
}

if ($errors !== []) {
    redirectAdminClasses($errors);
}

$duration = $existingClass !== null ? (int) $existingClass['duration'] : 45;
$image = $existingClass !== null ? (string) ($existingClass['image'] ?? '') : '';
$uploadedImage = uploadClassImage($_FILES['image_file'] ?? null, $errors);

if ($errors !== []) {
    redirectAdminClasses($errors);
}

if ($uploadedImage !== null) {
    $image = $uploadedImage;
}

if ($action === 'create') {
    ClassCatalog::create($db, $trainerId, $name, $description, $type, $intensity, $duration, $capacity, $image);
    redirectAdminClasses([], ['Aula criada com sucesso.']);
}

ClassCatalog::update($db, $classId, $trainerId, $name, $description, $type, $intensity, $duration, $capacity, $image);

if ($uploadedImage !== null) {
    deleteUploadedClassImage((string) ($existingClass['image'] ?? ''));
}

redirectAdminClasses([], ['Aula atualizada com sucesso.']);
