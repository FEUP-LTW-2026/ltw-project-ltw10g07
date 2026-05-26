<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$login = trim($_POST['login'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if ($login === '') {
    $errors[] = 'O email ou username é obrigatório.';
}

if ($password === '') {
    $errors[] = 'A palavra-passe é obrigatória.';
}

if ($errors === []) {
    $db = getDB();
    $user = filter_var($login, FILTER_VALIDATE_EMAIL)
        ? User::findByEmail($db, $login)
        : User::findByUsername($db, strtolower($login));

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $errors[] = 'Email, username ou palavra-passe incorretos.';
    } elseif ((int) $user['is_active'] !== 1) {
        $errors[] = 'Esta conta está desativada.';
    } else {
        loginUser($user);
        header('Location: ../pages/landingpage.php');
        exit;
    }
}

setOldInput(['login' => $login]);
setFlashMessages('errors', $errors);
header('Location: ../pages/login.php');
exit;
