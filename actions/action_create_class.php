<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../utils/session.php';

// 1. Validar se é treinador ou admin
if (!isLoggedIn() || !in_array(getUserRole(), ['trainer', 'admin'], true)) {
    setFlashMessages('errors', ['Não tens permissões para publicar aulas.']);
    header('Location: ../pages/landingpage.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/publish_class.php');
    exit;
}

// 2. Apanhar os dados do formulário
$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? '');
$intensity = trim($_POST['intensity'] ?? '');
$duration = (int)($_POST['duration'] ?? 0);
$capacity = (int)($_POST['capacity'] ?? 0);
$description = trim($_POST['description'] ?? '');

$session_date = trim($_POST['session_date'] ?? '');
$room = trim($_POST['room'] ?? '');
$start_time = trim($_POST['start_time'] ?? '');
$end_time = trim($_POST['end_time'] ?? '');

// Validação Básica
if ($name === '' || $type === '' || $duration <= 0 || $capacity <= 0 || $session_date === '' || $start_time === '') {
    setFlashMessages('errors', ['Por favor, preenche todos os campos obrigatórios.']);
    header('Location: ../pages/publish_class.php');
    exit;
}

try {
    $db = getDB();
    
    // 3. Obter o ID de treinador associado ao utilizador que está a publicar
    $stmt = $db->prepare('SELECT id FROM trainer WHERE user_id = ?');
    $stmt->execute([getUserId()]);
    $trainerId = $stmt->fetchColumn();

    if (!$trainerId) {
        setFlashMessages('errors', ['O teu perfil de treinador não está configurado.']);
        header('Location: ../pages/publish_class.php');
        exit;
    }

    // Usar transação para garantir que Aula e Sessão são criadas em conjunto
    $db->beginTransaction();

    // 4. Inserir a Aula Principal
    $stmt = $db->prepare('
        INSERT INTO class (trainer_id, name, description, type, intensity, duration, capacity) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$trainerId, $name, $description, $type, $intensity, $duration, $capacity]);
    $classId = (int) $db->lastInsertId();

    // 5. Inserir a primeira Sessão dessa Aula
    $stmt = $db->prepare('
        INSERT INTO class_session (class_id, session_date, start_time, end_time, room) 
        VALUES (?, ?, ?, ?, ?)
    ');
    $stmt->execute([$classId, $session_date, $start_time, $end_time, $room]);

    $db->commit();

    setFlashMessages('success', ['A tua aula foi publicada com sucesso!']);
    header('Location: ../pages/classes.php');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    setFlashMessages('errors', ['Ocorreu um erro ao publicar a aula. Tenta novamente.']);
    header('Location: ../pages/publish_class.php');
    exit;
}