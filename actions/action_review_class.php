<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/ClassReview.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

if (!isLoggedIn()) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/classes.php');
    exit;
}

$classId  = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 0;
$redirect = '../pages/class.php?id=' . $classId . '#reviews-section';

verifyCsrfToken($redirect);
$db       = getDB();

if (isset($_POST['delete_review'])) {
    if (getUserRole() !== 'member') {
        header('Location: ' . $redirect);
        exit;
    }
    ClassReview::delete($db, (int) getUserId(), $classId);
    setFlashMessages('success', ['Avaliação removida com sucesso.']);
    header('Location: ' . $redirect);
    exit;
}

$rating  = isset($_POST['rating'])  ? (int) $_POST['rating']  : 0;
$comment = trim($_POST['comment'] ?? '');
$errors  = [];

if (getUserRole() !== 'member') {
    $errors[] = 'Apenas membros podem avaliar aulas.';
}

if ($classId <= 0) {
    $errors[] = 'Aula inválida.';
}

if ($rating < 1 || $rating > 5) {
    $errors[] = 'A avaliação tem de ser entre 1 e 5 estrelas.';
}

if ($comment === '') {
    $errors[] = 'Escreve um comentário para acompanhar a tua avaliação.';
} elseif (strlen($comment) > 700) {
    $errors[] = 'O comentário deve ter no máximo 700 caracteres.';
}

if ($errors === []) {
    if (!ClassReview::memberHasEnrolledInClass($db, (int) getUserId(), $classId)) {
        $errors[] = 'Só podes avaliar aulas em que participaste e que já terminaram.';
    }
}

if ($errors !== []) {
    setFlashMessages('errors', $errors);
    header('Location: ' . $redirect);
    exit;
}

ClassReview::upsert($db, (int) getUserId(), $classId, $rating, $comment);
setFlashMessages('success', ['Avaliação publicada com sucesso!']);
header('Location: ' . $redirect);
exit;