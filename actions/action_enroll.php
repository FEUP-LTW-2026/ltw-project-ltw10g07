<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

// 1. Validar se o utilizador tem sessão iniciada e se é um membro
if (!isLoggedIn()) {
    setFlashMessages('errors', ['Precisas de iniciar sessão para te inscreveres em aulas.']);
    header('Location: ../pages/login.php');
    exit;
}

// Opcional: Se quiseres deixar os PTs/Admins testarem as inscrições, comenta este bloco!
if (getUserRole() !== 'member') {
    setFlashMessages('errors', ['Apenas membros do ginásio se podem inscrever em aulas.']);
    // Mandar de volta para a mesma página, em vez da lista geral
    $classId = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 1;
    $sessionId = isset($_POST['session_id']) ? (int) $_POST['session_id'] : 0;
    header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/classes.php');
    exit;
}

verifyCsrfToken('../pages/classes.php');

// 2. Recolher parâmetros do formulário
$sessionId = isset($_POST['session_id']) ? (int) $_POST['session_id'] : 0;
$classId   = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 1;
$action    = $_POST['action'] ?? 'enroll';

if ($sessionId <= 0) {
    setFlashMessages('errors', ['Sessão inválida.']);
    header('Location: ../pages/classes.php');
    exit;
}

try {
    $db = getDB();
    $userId = getUserId();

    $stmt = $db->prepare('
        SELECT cs.id, c.capacity, c.name
        FROM class_session cs
        JOIN class c ON c.id = cs.class_id
        WHERE cs.id = ?
    ');
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        setFlashMessages('errors', ['A sessão selecionada já não existe.']);
        header('Location: ../pages/class.php?id=' . $classId);
        exit;
    }

    $stmt = $db->prepare('SELECT id, status FROM enrollment WHERE user_id = ? AND session_id = ?');
    $stmt->execute([$userId, $sessionId]);
    $existingEnrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($action === 'enroll') {
        if ($existingEnrollment && $existingEnrollment['status'] === 'active') {
            setFlashMessages('errors', ['Já estás inscrito nesta sessão!']);
            header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
            exit;
        }

        $stmt = $db->prepare('SELECT COUNT(*) FROM enrollment WHERE session_id = ? AND status = \'active\'');
        $stmt->execute([$sessionId]);
        $currentEnrollments = (int) $stmt->fetchColumn();

        if ($currentEnrollments >= (int) $session['capacity']) {
            setFlashMessages('errors', ['Lamento, mas esta aula já atingiu a capacidade máxima de vagas.']);
            header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
            exit;
        }

        if ($existingEnrollment && $existingEnrollment['status'] === 'cancelled') {
            $stmt = $db->prepare('UPDATE enrollment SET status = \'active\', created_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$existingEnrollment['id']]);
        } else {
            $stmt = $db->prepare('INSERT INTO enrollment (user_id, session_id, status) VALUES (?, ?, \'active\')');
            $stmt->execute([$userId, $sessionId]);
        }

        setFlashMessages('success', ['Inscrição confirmada na aula de ' . $session['name'] . '!']);
    }
    elseif ($action === 'cancel') {
        if (!$existingEnrollment || $existingEnrollment['status'] === 'cancelled') {
            setFlashMessages('errors', ['Não tens nenhuma inscrição ativa nesta sessão para poder cancelar.']);
            header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
            exit;
        }

        $stmt = $db->prepare('UPDATE enrollment SET status = \'cancelled\' WHERE id = ?');
        $stmt->execute([$existingEnrollment['id']]);
        setFlashMessages('success', ['Inscrição cancelada com sucesso.']);
    }

    // REDIRECIONAMENTO CORRIGIDO: Agora mantém-te na sessão exata onde clicaste!
    header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
    exit;

} catch (Exception $e) {
    setFlashMessages('errors', ['Ocorreu um erro ao processar a tua inscrição. Tenta novamente.']);
    header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
    exit;
}