<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

// Bloquear qualquer pessoa que não seja Treinador
if (!isLoggedIn() || getUserRole() !== 'trainer') {
    header('Location: ../pages/classes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/publish_class.php');
    exit;
}

verifyCsrfToken('../pages/publish_class.php');

$classId = (int)($_POST['class_id'] ?? 0);
$sessionDate = trim($_POST['session_date'] ?? '');
$startTime = trim($_POST['start_time'] ?? '');
$endTime = trim($_POST['end_time'] ?? '');
$room = trim($_POST['room'] ?? '');
$timezone = new DateTimeZone('Europe/Lisbon');
$today = new DateTimeImmutable('today', $timezone);
$maxSessionDate = $today->modify('+1 month');

if ($classId <= 0 || $sessionDate === '' || $startTime === '' || $endTime === '' || $room === '') {
    setFlashMessages('errors', ['Por favor preenche todos os campos.']);
    header('Location: ../pages/publish_class.php');
    exit;
}

$sessionDateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $sessionDate, $timezone);
$sessionDateErrors = DateTimeImmutable::getLastErrors();

if (
    !$sessionDateObject
    || ($sessionDateErrors !== false && ($sessionDateErrors['warning_count'] > 0 || $sessionDateErrors['error_count'] > 0))
) {
    setFlashMessages('errors', ['Seleciona uma data válida.']);
    header('Location: ../pages/publish_class.php');
    exit;
}

if ($sessionDateObject < $today || $sessionDateObject > $maxSessionDate) {
    setFlashMessages('errors', ['Só podes agendar sessões entre hoje e daqui a 1 mês.']);
    header('Location: ../pages/publish_class.php');
    exit;
}

try {
    $db = getDB();
    
    // Verificação de Segurança: O treinador só pode agendar para as SUAS aulas
    $stmt = $db->prepare('
        SELECT id FROM class 
        WHERE id = ? AND trainer_id = (SELECT id FROM trainer WHERE user_id = ?)
    ');
    $stmt->execute([$classId, getUserId()]);
    if (!$stmt->fetch()) {
        setFlashMessages('errors', ['Não tens permissões para agendar sessões para esta aula.']);
        header('Location: ../pages/publish_class.php');
        exit;
    }

    // Inserir a nova sessão
    $stmt = $db->prepare('
        INSERT INTO class_session (class_id, session_date, start_time, end_time, room) 
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$classId, $sessionDate, $startTime, $endTime, $room]);

    setFlashMessages('success', ['Sessão agendada com sucesso!']);
    header('Location: ../pages/classes.php');
    exit;

} catch (Exception $e) {
    setFlashMessages('errors', ['Erro ao agendar sessão. Tenta novamente.']);
    header('Location: ../pages/publish_class.php');
    exit;
}
