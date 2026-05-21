<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'O email não é válido.';
}

if ($password === '') {
    $errors[] = 'A palavra-passe é obrigatória.';
}

if ($errors === []) {
    $db = getDB();
    $user = User::findByEmail($db, $email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Email ou palavra-passe incorretos.';
    } elseif ((int) $user['is_active'] !== 1) {
        $errors[] = 'Esta conta está desativada.';
    } else {
        loginUser($user);
        header('Location: ../pages/landingpage.php');
        exit;
    }
}

setOldInput(['email' => $email]);
setFlashMessages('errors', $errors);
header('Location: ../pages/login.php');
exit;
