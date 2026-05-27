<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

header('Content-Type: application/json');

try {
    $db = getDB();

    // 1. Apanhar os filtros novos (agora com Data e Hora)
    $search    = trim($_GET['q'] ?? '');
    $intensity = trim($_GET['intensity'] ?? 'Todas as Intensidades');
    $trainerId = isset($_GET['trainer_id']) ? (int)$_GET['trainer_id'] : 0;
    $date      = trim($_GET['date'] ?? '');
    $timeOfDay = trim($_GET['time'] ?? 'all');

    // 2. Query focada em SESSÕES (Horário) e não apenas no catálogo de aulas
    $query = '
        SELECT 
            class_session.id AS session_id,
            class_session.session_date,
            class_session.start_time,
            class_session.room,
            class.id AS class_id,
            class.name,
            class.type,
            class.intensity,
            class.duration,
            class.image,
            class.capacity,
            user.name AS trainer_name,
            (SELECT COUNT(*) FROM enrollment WHERE session_id = class_session.id AND status = "active") AS active_enrollments
        FROM class_session
        JOIN class ON class.id = class_session.class_id
        LEFT JOIN trainer ON trainer.id = class.trainer_id
        LEFT JOIN user ON user.id = trainer.user_id
        WHERE class_session.session_date >= CURRENT_DATE
    ';

    $params = [];

    // Filtros Antigos
    if ($search !== '') {
        $query .= ' AND (class.name LIKE ? OR class.type LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    if ($intensity !== 'Todas as Intensidades') {
        $query .= ' AND class.intensity = ?';
        $params[] = $intensity;
    }
    if ($trainerId > 0) {
        $query .= ' AND class.trainer_id = ?';
        $params[] = $trainerId;
    }

    // NOVOS FILTROS: Dia e Hora
    if ($date !== '') {
        $query .= ' AND class_session.session_date = ?';
        $params[] = $date;
    }

    if ($timeOfDay === 'morning') {
        $query .= ' AND class_session.start_time < "12:00:00"';
    } elseif ($timeOfDay === 'afternoon') {
        $query .= ' AND class_session.start_time >= "12:00:00" AND class_session.start_time < "18:00:00"';
    } elseif ($timeOfDay === 'evening') {
        $query .= ' AND class_session.start_time >= "18:00:00"';
    }

    // Ordenar cronologicamente como um horário real
    $query .= ' ORDER BY class_session.session_date ASC, class_session.start_time ASC';

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $sessions]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erro ao carregar o horário.']);
}