<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/ClassCatalog.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../utils/session.php';

if (!isLoggedIn() || !in_array(getUserRole(), ['trainer', 'admin'], true)) {
    setFlashMessages('errors', ['Não tens permissões para publicar aulas.']);
    header('Location: ../pages/landingpage.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/publish_class.php');
    exit;
}

$role = getUserRole();
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$intensity = trim($_POST['intensity'] ?? '');
$description = trim($_POST['description'] ?? '');
$duration = (int) ($_POST['duration'] ?? 0);
$capacity = (int) ($_POST['capacity'] ?? 0);
$sessionDate = trim($_POST['session_date'] ?? '');
$startTime = trim($_POST['start_time'] ?? '');
$endTime = trim($_POST['end_time'] ?? '');
$room = trim($_POST['room'] ?? '');
$errors = [];

if ($name === '') {
    $errors[] = 'O nome da aula é obrigatório.';
}

if ($type === '') {
    $errors[] = 'O tipo da aula é obrigatório.';
}

if (!in_array($intensity, ['Baixa', 'Média', 'Alta'], true)) {
    $errors[] = 'A intensidade selecionada não é válida.';
}

if ($description === '') {
    $errors[] = 'A descrição é obrigatória.';
}

if ($duration < 15 || $duration > 180) {
    $errors[] = 'A duração deve estar entre 15 e 180 minutos.';
}

if ($capacity < 1 || $capacity > 80) {
    $errors[] = 'A capacidade deve estar entre 1 e 80 pessoas.';
}

if ($sessionDate === '' || $startTime === '' || $endTime === '' || $room === '') {
    $errors[] = 'Preenche todos os dados da primeira sessão.';
}

$db = getDB();
$trainerId = null;

if ($role === 'trainer') {
    $trainer = Trainer::findByUserId($db, (int) getUserId());

    if ($trainer === null) {
        $errors[] = 'A tua conta ainda não tem perfil de treinador associado.';
    } else {
        $trainerId = (int) $trainer['id'];
    }
}

if ($errors !== []) {
    setOldInput($_POST);
    setFlashMessages('errors', $errors);
    header('Location: ../pages/publish_class.php');
    exit;
}

try {
    ClassCatalog::createWithSession(
        $db,
        $trainerId,
        $name,
        $description,
        $type,
        $intensity,
        $duration,
        $capacity,
        '',
        $sessionDate,
        $startTime,
        $endTime,
        $room
    );

    setFlashMessages('success', ['Aula publicada com sucesso.']);
    header('Location: ../pages/classes.php');
    exit;
} catch (Throwable $exception) {
    setOldInput($_POST);
    setFlashMessages('errors', ['Ocorreu um erro ao publicar a aula. Tenta novamente.']);
    header('Location: ../pages/publish_class.php');
    exit;
}
