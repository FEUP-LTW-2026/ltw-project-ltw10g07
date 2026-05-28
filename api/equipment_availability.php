<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Inicia sessão para alugares equipamentos.']);
    exit;
}

if (!in_array(getUserRole(), ['member', 'trainer'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Apenas membros e treinadores podem alugar equipamentos.']);
    exit;
}

function countActiveRentals(PDO $db, int $equipmentId, string $startTime, string $endTime): int {
    $stmt = $db->prepare('
        SELECT COUNT(*)
        FROM equipment_rentals
        WHERE equipment_id = ?
          AND status = "active"
          AND start_time < ?
          AND end_time > ?
    ');
    $stmt->execute([$equipmentId, $endTime, $startTime]);

    return (int) $stmt->fetchColumn();
}

function getActiveRentalToday(PDO $db, int $userId, int $equipmentId, string $today): ?array {
    $stmt = $db->prepare('
        SELECT
            equipment_rentals.id,
            equipment_rentals.equipment_id,
            equipment_rentals.start_time,
            equipment_rentals.end_time,
            equipment.name AS equipment_name
        FROM equipment_rentals
        JOIN equipment ON equipment.id = equipment_rentals.equipment_id
        WHERE equipment_rentals.user_id = ?
          AND equipment_rentals.equipment_id = ?
          AND equipment_rentals.status = "active"
          AND DATE(equipment_rentals.start_time) = ?
        ORDER BY equipment_rentals.start_time ASC, equipment_rentals.id ASC
        LIMIT 1
    ');
    $stmt->execute([$userId, $equipmentId, $today]);
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        return null;
    }

    return [
        'id' => (int) $rental['id'],
        'equipment_id' => (int) $rental['equipment_id'],
        'equipment_name' => $rental['equipment_name'],
        'start' => $rental['start_time'],
        'end' => $rental['end_time'],
    ];
}

try {
    date_default_timezone_set('Europe/Lisbon');

    $db = getDB();
    $equipmentId = isset($_GET['equipment_id']) ? (int) $_GET['equipment_id'] : 0;

    if ($equipmentId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Equipamento inválido.']);
        exit;
    }

    $stmt = $db->prepare('
        SELECT id, name, total_quantity, maintenance_quantity, rental_available
        FROM equipment
        WHERE id = ?
    ');
    $stmt->execute([$equipmentId]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Equipamento não encontrado.']);
        exit;
    }

    if ((int) $equipment['rental_available'] !== 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Este equipamento não permite aluguer.']);
        exit;
    }

    $now = new DateTimeImmutable('now');
    $today = $now->format('Y-m-d');
    $userId = getUserId();
    $baseAvailable = max(0, (int) $equipment['total_quantity'] - (int) $equipment['maintenance_quantity']);
    $slots = [];

    for ($hour = 9; $hour < 22; $hour++) {
        $startTime = sprintf('%s %02d:00:00', $today, $hour);
        $endTime = sprintf('%s %02d:00:00', $today, $hour + 1);
        $slotStart = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startTime);

        if ($slotStart === false || $slotStart <= $now) {
            continue;
        }

        $activeRentals = countActiveRentals($db, $equipmentId, $startTime, $endTime);

        $slots[] = [
            'start' => $startTime,
            'end' => $endTime,
            'available' => max(0, $baseAvailable - $activeRentals),
        ];
    }

    echo json_encode([
        'success' => true,
        'equipment' => [
            'id' => (int) $equipment['id'],
            'name' => $equipment['name'],
        ],
        'current_rental' => $userId === null ? null : getActiveRentalToday($db, $userId, $equipmentId, $today),
        'slots' => $slots,
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao carregar disponibilidade.']);
}
