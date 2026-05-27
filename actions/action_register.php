<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/register.php');
    exit;
}

verifyCsrfToken('../pages/register.php');

$old = [
    'first_name' => trim($_POST['first_name'] ?? ''),
    'last_name' => trim($_POST['last_name'] ?? ''),
    'username' => strtolower(trim($_POST['username'] ?? '')),
    'email' => trim($_POST['email'] ?? ''),
    'phone' => trim($_POST['phone'] ?? ''),
];

$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';
$errors = [];

if ($old['first_name'] === '') {
    $errors[] = 'O nome é obrigatório.';
}

if ($old['last_name'] === '') {
    $errors[] = 'O apelido é obrigatório.';
}

if (!preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $old['username'])) {
    $errors[] = 'O username deve ter 3 a 30 caracteres e só pode conter letras, números, ".", "_" ou "-".';
}

if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'O email não é válido.';
}

if ($password !== $passwordConfirm) {
    $errors[] = 'As palavras-passe não coincidem.';
}

if (strlen($password) < 8) {
    $errors[] = 'A palavra-passe deve ter pelo menos 8 caracteres.';
}

if ($errors === []) {
    $db = getDB();
    $existingUser = User::findByEmail($db, $old['email']);
    $existingUsername = User::findByUsername($db, $old['username']);

    if ($existingUser !== null) {
        $errors[] = 'Este email já está registado.';
    } elseif ($existingUsername !== null) {
        $errors[] = 'Este username já está registado.';
    } else {
        $name = $old['first_name'] . ' ' . $old['last_name'];
        $hash = password_hash($password, PASSWORD_DEFAULT);

        User::create($db, $name, $old['username'], $old['email'], $hash);

        setFlashMessages('success', ['Conta criada com sucesso. Já podes entrar.']);
        header('Location: ../pages/login.php');
        exit;
    }
}

setOldInput($old);
setFlashMessages('errors', $errors);
header('Location: ../pages/register.php');
exit;
