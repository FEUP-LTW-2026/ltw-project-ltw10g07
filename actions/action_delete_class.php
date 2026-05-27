<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../utils/session.php';

// 1. Validar se o utilizador tem permissões (Treinador ou Admin)
if (!isLoggedIn() || !in_array(getUserRole(), ['trainer', 'admin'], true)) {
    setFlashMessages('errors', ['Não tens permissões para eliminar aulas.']);
    header('Location: ../pages/classes.php');
    exit;
}

// 2. Garantir que foi um pedido POST (por segurança, não se apagam coisas por GET/links)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/classes.php');
    exit;
}

$class_id = (int)($_POST['class_id'] ?? 0);

if ($class_id <= 0) {
    setFlashMessages('errors', ['Aula inválida.']);
    header('Location: ../pages/classes.php');
    exit;
}

try {
    $db = getDB();

    // Por segurança, abrimos uma transação.
    // Apagamos primeiro as inscrições e as sessões ligadas a esta aula, para não deixar "lixo" na BD.
    $db->beginTransaction();

    // Encontrar as sessões desta aula para apagar as inscrições
    $stmt = $db->prepare('SELECT id FROM class_session WHERE class_id = ?');
    $stmt->execute([$class_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($sessions as $s_id) {
        $stmtDelEnroll = $db->prepare('DELETE FROM enrollment WHERE session_id = ?');
        $stmtDelEnroll->execute([$s_id]);
    }

    // Apagar as sessões
    $stmtDelSessions = $db->prepare('DELETE FROM class_session WHERE class_id = ?');
    $stmtDelSessions->execute([$class_id]);

    // Finalmente, apagar a aula principal
    $stmtDelClass = $db->prepare('DELETE FROM class WHERE id = ?');
    $stmtDelClass->execute([$class_id]);

    $db->commit();

    // Sucesso! Volta para a listagem
    setFlashMessages('success', ['A aula foi eliminada para sempre.']);
    header('Location: ../pages/classes.php');
    exit;

} catch (Exception $e) {
    $db->rollBack();
    setFlashMessages('errors', ['Ocorreu um erro ao eliminar a aula.']);
    header('Location: ../pages/class.php?id=' . $class_id);
    exit;
}
