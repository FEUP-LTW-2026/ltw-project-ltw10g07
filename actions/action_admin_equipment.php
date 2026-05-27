<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Equipment.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

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

verifyCsrfToken('../pages/admin.php#equipment');

$db = getDB();
$action = $_POST['action'] ?? '';
$equipmentId = isset($_POST['equipment_id']) ? (int) $_POST['equipment_id'] : 0;
$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$status = $_POST['status'] ?? '';
$location = trim($_POST['location'] ?? '');
$totalQuantity = (int) ($_POST['total_quantity'] ?? 1);
$maintenanceQuantity = (int) ($_POST['maintenance_quantity'] ?? 0);
$rentalAvailable = isset($_POST['rental_available']) ? 1 : 0;
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

if ($totalQuantity < 0) {
    $errors[] = 'A quantidade total não pode ser negativa.';
}

if ($maintenanceQuantity < 0) {
    $errors[] = 'A quantidade em manutenção não pode ser negativa.';
}

if ($maintenanceQuantity > $totalQuantity) {
    $errors[] = 'A quantidade em manutenção não pode ser maior do que a quantidade total.';
}

if ($errors !== []) {
    redirectAdminEquipment($errors);
}

if ($action === 'create') {
    Equipment::create($db, $name, $category, $status, $location, $totalQuantity, $maintenanceQuantity, $rentalAvailable);
    redirectAdminEquipment([], ['Equipamento criado com sucesso.']);
}

if ($equipmentId <= 0) {
    redirectAdminEquipment(['Equipamento inválido.']);
}

Equipment::update($db, $equipmentId, $name, $category, $status, $location, $totalQuantity, $maintenanceQuantity, $rentalAvailable);
redirectAdminEquipment([], ['Equipamento atualizado com sucesso.']);
