<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Inicia sessão para veres os equipamentos.']);
    exit;
}

try {
    $db = getDB();
    $search = trim($_GET['q'] ?? '');
    $category = trim($_GET['category'] ?? 'all');
    $rentable = trim($_GET['rentable'] ?? 'all');

    $query = '
        SELECT
            id,
            name,
            category,
            status,
            location,
            quantity,
            maintenance_quantity,
            rentable,
            MAX(0, quantity - maintenance_quantity) AS available_units
        FROM equipment
        WHERE status = "available"
          AND quantity - maintenance_quantity > 0
    ';
    $params = [];

    if ($search !== '') {
        $query .= ' AND (name LIKE ? OR category LIKE ? OR location LIKE ?)';
        $term = '%' . $search . '%';
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
    }

    if ($category !== '' && $category !== 'all') {
        $query .= ' AND category = ?';
        $params[] = $category;
    }

    if ($rentable === 'yes') {
        $query .= ' AND rentable = 1';
    } elseif ($rentable === 'no') {
        $query .= ' AND rentable = 0';
    }

    $query .= ' ORDER BY category ASC, name ASC';

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    echo json_encode([
        'status' => 'success',
        'data' => array_map(static function (array $item): array {
            return [
                'id' => (int) $item['id'],
                'name' => $item['name'],
                'category' => $item['category'],
                'status' => $item['status'],
                'location' => $item['location'] ?: 'Por definir',
                'quantity' => (int) $item['quantity'],
                'maintenance_quantity' => (int) $item['maintenance_quantity'],
                'rentable' => (int) $item['rentable'] === 1,
                'available_units' => (int) $item['available_units'],
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC)),
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao carregar equipamentos.']);
}
