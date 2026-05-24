<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../database/TrainerReview.php';
require_once __DIR__ . '/../utils/session.php';

if (!isLoggedIn()) {
    header('Location: ../pages/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/trainers.php');
    exit;
}

$trainerId = isset($_POST['trainer_id']) ? (int) $_POST['trainer_id'] : 0;
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : -1;
$comment = trim($_POST['comment'] ?? '');
$redirect = '../pages/trainer.php?id=' . $trainerId . '#review-form';
$errors = [];

if ($trainerId <= 0) {
    $errors[] = 'Treinador inválido.';
}

if ($rating < 0 || $rating > 5) {
    $errors[] = 'A avaliação tem de estar entre 0 e 5 estrelas.';
}

if ($comment === '') {
    $errors[] = 'Escreve um comentário para acompanhares a tua avaliação.';
} elseif (strlen($comment) > 700) {
    $errors[] = 'O comentário deve ter no máximo 700 caracteres.';
}

if (getUserRole() !== 'member') {
    $errors[] = 'Apenas membros podem avaliar treinadores.';
}

$db = getDB();
$trainer = $trainerId > 0 ? Trainer::findById($db, $trainerId) : null;

if ($trainer === null) {
    $errors[] = 'Treinador não encontrado.';
    $redirect = '../pages/trainers.php';
}

if ($errors === []) {
    TrainerReview::upsert($db, (int) getUserId(), $trainerId, $rating, $comment);
    TrainerReview::refreshTrainerRating($db, $trainerId);

    setFlashMessages('success', ['Avaliação publicada com sucesso.']);
    header('Location: ../pages/trainer.php?id=' . $trainerId . '#reviews-heading');
    exit;
}

setOldInput([
    'rating' => (string) max(0, min(5, $rating)),
    'comment' => $comment,
]);
setFlashMessages('errors', $errors);
header('Location: ' . $redirect);
exit;
