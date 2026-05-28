<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

if (!isLoggedIn()) {
    setFlashMessages('errors', ['Precisas de iniciar sessão para te inscreveres em aulas.']);
    header('Location: ../pages/login.php');
    exit;
}

if (getUserRole() !== 'member') {
    setFlashMessages('errors', ['Apenas membros do ginásio se podem inscrever em aulas.']);
    $classId   = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 1;
    $sessionId = isset($_POST['session_id']) ? (int) $_POST['session_id'] : 0;
    header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/classes.php');
    exit;
}

verifyCsrfToken('../pages/classes.php');

$sessionId = isset($_POST['session_id']) ? (int) $_POST['session_id'] : 0;
$classId   = isset($_POST['class_id']) ? (int) $_POST['class_id'] : 1;
$action    = $_POST['action'] ?? 'enroll';

if ($sessionId <= 0) {
    setFlashMessages('errors', ['Sessão inválida.']);
    header('Location: ../pages/classes.php');
    exit;
}

try {
    $db     = getDB();
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

    $stmt = $db->prepare('SELECT id FROM waitlist WHERE user_id = ? AND session_id = ?');
    $stmt->execute([$userId, $sessionId]);
    $waitlistEntry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($action === 'enroll') {
        if ($existingEnrollment && $existingEnrollment['status'] === 'active') {
            setFlashMessages('errors', ['Já estás inscrito nesta sessão!']);
            header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
            exit;
        }

        if ($waitlistEntry) {
            setFlashMessages('errors', ['Já estás na lista de espera desta sessão.']);
            header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
            exit;
        }

        $stmt = $db->prepare('SELECT COUNT(*) FROM enrollment WHERE session_id = ? AND status = \'active\'');
        $stmt->execute([$sessionId]);
        $currentEnrollments = (int) $stmt->fetchColumn();

        if ($currentEnrollments >= (int) $session['capacity']) {
            $stmt = $db->prepare('INSERT INTO waitlist (user_id, session_id) VALUES (?, ?)');
            $stmt->execute([$userId, $sessionId]);

            $stmt = $db->prepare('
                SELECT COUNT(*) FROM waitlist
                WHERE session_id = ? AND created_at <= (
                    SELECT created_at FROM waitlist WHERE user_id = ? AND session_id = ?
                )
            ');
            $stmt->execute([$sessionId, $userId, $sessionId]);
            $position = (int) $stmt->fetchColumn();

            setFlashMessages('success', ['Adicionado à lista de espera em ' . $position . '.º lugar para a aula de ' . $session['name'] . '.']);
        } else {
            if ($existingEnrollment && $existingEnrollment['status'] === 'cancelled') {
                $stmt = $db->prepare('UPDATE enrollment SET status = \'active\', created_at = CURRENT_TIMESTAMP WHERE id = ?');
                $stmt->execute([$existingEnrollment['id']]);
            } else {
                $stmt = $db->prepare('INSERT INTO enrollment (user_id, session_id, status) VALUES (?, ?, \'active\')');
                $stmt->execute([$userId, $sessionId]);
            }
            setFlashMessages('success', ['Inscrição confirmada na aula de ' . $session['name'] . '!']);
        }
    } elseif ($action === 'cancel') {
        if ($waitlistEntry) {
            $stmt = $db->prepare('DELETE FROM waitlist WHERE id = ?');
            $stmt->execute([$waitlistEntry['id']]);
            setFlashMessages('success', ['Removido da lista de espera com sucesso.']);
        } elseif ($existingEnrollment && $existingEnrollment['status'] === 'active') {
            $stmt = $db->prepare('UPDATE enrollment SET status = \'cancelled\' WHERE id = ?');
            $stmt->execute([$existingEnrollment['id']]);

            // Promover o primeiro da lista de espera
            $stmt = $db->prepare('
                SELECT id, user_id FROM waitlist
                WHERE session_id = ?
                ORDER BY created_at ASC
                LIMIT 1
            ');
            $stmt->execute([$sessionId]);
            $next = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($next) {
                $stmt = $db->prepare('SELECT id FROM enrollment WHERE user_id = ? AND session_id = ?');
                $stmt->execute([$next['user_id'], $sessionId]);
                $promotedEnrollment = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($promotedEnrollment) {
                    $stmt = $db->prepare('UPDATE enrollment SET status = \'active\' WHERE id = ?');
                    $stmt->execute([$promotedEnrollment['id']]);
                } else {
                    $stmt = $db->prepare('INSERT INTO enrollment (user_id, session_id, status) VALUES (?, ?, \'active\')');
                    $stmt->execute([$next['user_id'], $sessionId]);
                }

                $stmt = $db->prepare('DELETE FROM waitlist WHERE id = ?');
                $stmt->execute([$next['id']]);
            }

            setFlashMessages('success', ['Inscrição cancelada com sucesso.']);
        } else {
            setFlashMessages('errors', ['Não tens nenhuma inscrição ativa nesta sessão para poder cancelar.']);
            header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
            exit;
        }
    }

    header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
    exit;

} catch (Exception $e) {
    setFlashMessages('errors', ['Ocorreu um erro ao processar a tua inscrição. Tenta novamente.']);
    header('Location: ../pages/class.php?id=' . $classId . '&session_id=' . $sessionId);
    exit;
}
