<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

if (!isLoggedIn() || getUserRole() !== 'trainer') {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/trainer_profile_edit.php');
    exit;
}

verifyCsrfToken('../pages/trainer_profile_edit.php');

$old = [
    'bio' => trim($_POST['bio'] ?? ''),
    'specializations' => trim($_POST['specializations'] ?? ''),
    'certifications' => trim($_POST['certifications'] ?? ''),
];
$errors = [];

if (strlen($old['bio']) > 1000) {
    $errors[] = 'A bio deve ter no máximo 1000 caracteres.';
}

if (strlen($old['specializations']) > 500) {
    $errors[] = 'As especializações devem ter no máximo 500 caracteres.';
}

if (strlen($old['certifications']) > 700) {
    $errors[] = 'As certificações devem ter no máximo 700 caracteres.';
}

$db = getDB();
$trainer = Trainer::findByUserId($db, (int) getUserId());

if ($trainer === null) {
    setFlashMessages('errors', ['O teu perfil de treinador ainda não está configurado.']);
    header('Location: ../pages/account.php');
    exit;
}

if ($errors !== []) {
    setOldInput($old);
    setFlashMessages('errors', $errors);
    header('Location: ../pages/trainer_profile_edit.php');
    exit;
}

Trainer::updatePublicProfile(
    $db,
    (int) $trainer['id'],
    $old['bio'],
    $old['specializations'],
    $old['certifications']
);

setFlashMessages('success', ['Perfil público atualizado com sucesso.']);
header('Location: ../pages/trainer_profile_edit.php');
exit;
