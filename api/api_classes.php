<?php
declare(strict_types=1);

// Incluir a ligação à base de dados
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

// Avisar o navegador que a resposta vai ser em formato JSON (e não HTML)
header('Content-Type: application/json');

try {
    $db = getDB();

    // 1. Capturar os filtros da URL (exatamente como fizemos no classes.php)
    $search    = trim($_GET['q'] ?? '');
    $intensity = trim($_GET['intensity'] ?? 'Todas as Intensidades');
    $trainerId = isset($_GET['trainer_id']) ? (int)$_GET['trainer_id'] : 0;
    $sortBy    = trim($_GET['sort'] ?? 'next');

    // 2. Construir a Query SQL Dinâmica
    $query = '
        SELECT 
            class.id,
            class.name,
            class.type,
            class.intensity,
            class.duration,
            class.image,
            user.name AS trainer_name,
            (SELECT COUNT(*) FROM enrollment WHERE session_id = (
                SELECT id FROM class_session WHERE class_id = class.id ORDER BY session_date ASC, start_time ASC LIMIT 1
            ) AND status = "active") AS active_enrollments,
            class.capacity
        FROM class
        LEFT JOIN trainer ON trainer.id = class.trainer_id
        LEFT JOIN user ON user.id = trainer.user_id
        WHERE 1=1
    ';

    $params = [];

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

    if ($sortBy === 'spots') {
        $query .= ' ORDER BY (class.capacity - active_enrollments) DESC';
    } else {
        $query .= ' ORDER BY class.id ASC';
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Pegar no array do PHP e cuspir cá para fora como JSON
    echo json_encode([
        'status' => 'success',
        'data' => $classes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Erro ao carregar as aulas da base de dados.'
    ]);
}