<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';

function redirectAdminUsers(array $errors = [], array $success = []): void {
    if ($errors !== []) {
        setFlashMessages('errors', $errors);
    }

    if ($success !== []) {
        setFlashMessages('success', $success);
    }

    header('Location: ../pages/admin.php#users');
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
$roles = ['member', 'trainer', 'admin'];
$errors = [];

$name = trim($_POST['name'] ?? '');
$username = strtolower(trim($_POST['username'] ?? ''));
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? 'member';
$isActive = isset($_POST['is_active']) ? 1 : 0;
$password = $_POST['password'] ?? '';
$userId = isset($_POST['user_id']) ? (int) $_POST['user_id'] : 0;

if (!in_array($role, $roles, true)) {
    $errors[] = 'O tipo de utilizador selecionado não é válido.';
}

if ($name === '') {
    $errors[] = 'O nome é obrigatório.';
}

if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $username)) {
    $errors[] = 'O username deve ter 3 a 30 caracteres e só pode conter letras, números, ".", "_" ou "-".';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'O email não é válido.';
}

if ($action === 'create' && strlen($password) < 8) {
    $errors[] = 'A palavra-passe deve ter pelo menos 8 caracteres.';
}

if ($action === 'update' && $userId <= 0) {
    $errors[] = 'Utilizador inválido.';
}

if ($errors !== []) {
    redirectAdminUsers($errors);
}

$existingEmail = User::findByEmail($db, $email);
$existingUsername = User::findByUsername($db, $username);

if ($action === 'create') {
    if ($existingEmail !== null) {
        $errors[] = 'Este email já está associado a outra conta.';
    }

    if ($existingUsername !== null) {
        $errors[] = 'Este username já está associado a outra conta.';
    }

    if ($errors !== []) {
        redirectAdminUsers($errors);
    }

    $newUserId = User::createWithStatus($db, $name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $role, $isActive);

    if ($role === 'trainer') {
        User::ensureTrainerProfile($db, $newUserId);
    }

    redirectAdminUsers([], ['Utilizador criado com sucesso.']);
}

if ($action === 'update') {
    $user = User::findById($db, $userId);

    if ($user === null) {
        redirectAdminUsers(['Utilizador não encontrado.']);
    }

    if ($existingEmail !== null && (int) $existingEmail['id'] !== $userId) {
        $errors[] = 'Este email já está associado a outra conta.';
    }

    if ($existingUsername !== null && (int) $existingUsername['id'] !== $userId) {
        $errors[] = 'Este username já está associado a outra conta.';
    }

    if ($userId === getUserId() && ($role !== 'admin' || $isActive !== 1)) {
        $errors[] = 'Não podes remover o teu próprio acesso de admin nem desativar a tua conta.';
    }

    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'A nova palavra-passe deve ter pelo menos 8 caracteres.';
    }

    if ($errors !== []) {
        redirectAdminUsers($errors);
    }

    User::updateAdminProfile($db, $userId, $name, $username, $email, $role, $isActive);

    if ($password !== '') {
        User::updatePassword($db, $userId, password_hash($password, PASSWORD_DEFAULT));
    }

    if ($role === 'trainer') {
        User::ensureTrainerProfile($db, $userId);
    } else {
        User::deleteTrainerProfile($db, $userId);
    }

    redirectAdminUsers([], ['Utilizador atualizado com sucesso.']);
}

redirectAdminUsers(['Ação inválida.']);
