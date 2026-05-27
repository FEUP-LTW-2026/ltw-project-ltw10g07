<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

if (!isLoggedIn()) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/account.php');
    exit;
}

verifyCsrfToken('../pages/account.php');

$userId = getUserId();
$old = [
    'name' => trim($_POST['name'] ?? ''),
    'username' => strtolower(trim($_POST['username'] ?? '')),
    'email' => trim($_POST['email'] ?? ''),
];
$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$newPasswordConfirm = $_POST['new_password_confirm'] ?? '';
$errors = [];

if ($old['name'] === '') {
    $errors[] = 'O nome é obrigatório.';
}

if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $old['username'])) {
    $errors[] = 'O username deve ter 3 a 30 caracteres e só pode conter letras, números, ".", "_" ou "-".';
}

if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'O email não é válido.';
}

$wantsPasswordChange = $currentPassword !== '' || $newPassword !== '' || $newPasswordConfirm !== '';

if ($wantsPasswordChange) {
    if ($currentPassword === '') {
        $errors[] = 'Indica a palavra-passe atual para definires uma nova.';
    }

    if (strlen($newPassword) < 8) {
        $errors[] = 'A nova palavra-passe deve ter pelo menos 8 caracteres.';
    }

    if ($newPassword !== $newPasswordConfirm) {
        $errors[] = 'As novas palavras-passe não coincidem.';
    }
}

$db = getDB();
$user = User::findById($db, $userId);

if ($user === null || (int) $user['is_active'] !== 1) {
    logoutUser();
    header('Location: ../pages/login.php');
    exit;
}

if ($errors === []) {
    $existingUser = User::findByEmail($db, $old['email']);
    $existingUsername = User::findByUsername($db, $old['username']);

    if ($existingUser !== null && (int) $existingUser['id'] !== $userId) {
        $errors[] = 'Este email já está associado a outra conta.';
    } elseif ($existingUsername !== null && (int) $existingUsername['id'] !== $userId) {
        $errors[] = 'Este username já está associado a outra conta.';
    }
}

if ($errors === [] && $wantsPasswordChange && !password_verify($currentPassword, $user['password_hash'])) {
    $errors[] = 'A palavra-passe atual está incorreta.';
}

if ($errors === []) {
    User::updateProfile($db, $userId, $old['name'], $old['username'], $old['email']);

    if ($wantsPasswordChange) {
        User::updatePassword($db, $userId, password_hash($newPassword, PASSWORD_DEFAULT));
    }

    $_SESSION['user_name'] = $old['name'];

    setFlashMessages('success', ['Conta atualizada com sucesso.']);
    header('Location: ../pages/account.php');
    exit;
}

setOldInput($old);
setFlashMessages('errors', $errors);
header('Location: ../pages/account.php');
exit;
