<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

// 1. Validar se é Treinador ou Admin
if (!isLoggedIn() || !in_array(getUserRole(), ['trainer', 'admin'], true)) {
    setFlashMessages('errors', ['Não tens permissões.']);
    header('Location: ../pages/classes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/classes.php');
    exit;
}

verifyCsrfToken('../pages/classes.php');

$session_id = (int)($_POST['session_id'] ?? 0);
$class_id = (int)($_POST['class_id'] ?? 0);

if ($session_id <= 0 || $class_id <= 0) {
    setFlashMessages('errors', ['Sessão inválida.']);
    header('Location: ../pages/classes.php');
    exit;
}

try {
    $db = getDB();

    // 2. Segurança: Se for treinador, garantir que a sessão pertence a uma aula dele!
    if (getUserRole() === 'trainer') {
        $stmt = $db->prepare('
            SELECT cs.id 
            FROM class_session cs
            JOIN class c ON c.id = cs.class_id
            WHERE cs.id = ? AND c.trainer_id = (SELECT id FROM trainer WHERE user_id = ?)
        ');
        $stmt->execute([$session_id, getUserId()]);
        if (!$stmt->fetch()) {
            setFlashMessages('errors', ['Não podes apagar sessões de aulas que não te pertencem.']);
            header('Location: ../pages/class.php?id=' . $class_id . '&session_id=' . $session_id);
            exit;
        }
    }

    $db->beginTransaction();

    // 3. Apagar inscrições ligadas a esta sessão
    $stmtDelEnroll = $db->prepare('DELETE FROM enrollment WHERE session_id = ?');
    $stmtDelEnroll->execute([$session_id]);

    // 4. Apagar APENAS esta sessão
    $stmtDelSession = $db->prepare('DELETE FROM class_session WHERE id = ?');
    $stmtDelSession->execute([$session_id]);

    $db->commit();

    setFlashMessages('success', ['A sessão foi cancelada e eliminada com sucesso.']);
    
    // Volta para a página da aula geral (para mostrar a próxima sessão disponível, se houver)
    header('Location: ../pages/class.php?id=' . $class_id);
    exit;

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    setFlashMessages('errors', ['Ocorreu um erro ao eliminar a sessão.']);
    header('Location: ../pages/class.php?id=' . $class_id . '&session_id=' . $session_id);
    exit;
}