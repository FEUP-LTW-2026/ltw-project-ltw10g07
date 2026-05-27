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
$trainerId = isset($_POST['trainer_id']) && $_POST['trainer_id'] !== '' ? (int) $_POST['trainer_id'] : null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$type = trim($_POST['type'] ?? '');
$intensity = $_POST['intensity'] ?? '';
$duration = (int) ($_POST['duration'] ?? 0);
$capacity = (int) ($_POST['capacity'] ?? 0);
$image = trim($_POST['image'] ?? '');
$errors = [];

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

if ($name === '') {
    $errors[] = 'O nome da aula é obrigatório.';
}

if ($type === '') {
    $errors[] = 'O tipo da aula é obrigatório.';
}

if (!in_array($intensity, ['Baixa', 'Média', 'Alta'], true)) {
    $errors[] = 'A intensidade selecionada não é válida.';
}

if ($duration < 15 || $duration > 180) {
    $errors[] = 'A duração deve estar entre 15 e 180 minutos.';
}

if ($capacity < 1 || $capacity > 80) {
    $errors[] = 'A capacidade deve estar entre 1 e 80 pessoas.';
}

if ($errors !== []) {
    redirectAdminClasses($errors);
}

if ($action === 'create') {
    ClassCatalog::create($db, $trainerId, $name, $description, $type, $intensity, $duration, $capacity, $image);
    redirectAdminClasses([], ['Aula criada com sucesso.']);
}

if ($classId <= 0) {
    redirectAdminClasses(['Aula inválida.']);
}

ClassCatalog::update($db, $classId, $trainerId, $name, $description, $type, $intensity, $duration, $capacity, $image);
redirectAdminClasses([], ['Aula atualizada com sucesso.']);
