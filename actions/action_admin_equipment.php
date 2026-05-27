<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Equipment.php';
require_once __DIR__ . '/../utils/session.php';

function redirectAdminEquipment(array $errors = [], array $success = []): void {
    if ($errors !== []) {
        setFlashMessages('errors', $errors);
    }

    if ($success !== []) {
        setFlashMessages('success', $success);
    }

    header('Location: ../pages/admin.php#equipment');
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

$db = getDB();
$action = $_POST['action'] ?? '';
$equipmentId = isset($_POST['equipment_id']) ? (int) $_POST['equipment_id'] : 0;
$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$status = $_POST['status'] ?? '';
$location = trim($_POST['location'] ?? '');
$errors = [];

if ($action === 'delete') {
    if ($equipmentId <= 0) {
        redirectAdminEquipment(['Equipamento inválido.']);
    }

    Equipment::delete($db, $equipmentId);
    redirectAdminEquipment([], ['Equipamento removido com sucesso.']);
}

if (!in_array($action, ['create', 'update'], true)) {
    redirectAdminEquipment(['Ação inválida.']);
}

if ($name === '') {
    $errors[] = 'O nome do equipamento é obrigatório.';
}

if ($category === '') {
    $errors[] = 'A categoria do equipamento é obrigatória.';
}

if (!in_array($status, ['available', 'in_use', 'maintenance'], true)) {
    $errors[] = 'O estado selecionado não é válido.';
}

if ($errors !== []) {
    redirectAdminEquipment($errors);
}

if ($action === 'create') {
    Equipment::create($db, $name, $category, $status, $location);
    redirectAdminEquipment([], ['Equipamento criado com sucesso.']);
}

if ($equipmentId <= 0) {
    redirectAdminEquipment(['Equipamento inválido.']);
}

Equipment::update($db, $equipmentId, $name, $category, $status, $location);
redirectAdminEquipment([], ['Equipamento atualizado com sucesso.']);
