<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

header('Content-Type: application/json; charset=utf-8');

function respond(int $statusCode, bool $success, string $message): void {
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if (!isLoggedIn()) {
    respond(403, false, 'Inicia sessão para cancelares o aluguer.');
}

if (!in_array(getUserRole(), ['member', 'trainer'], true)) {
    respond(403, false, 'Apenas membros e treinadores podem cancelar alugueres.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, false, 'Método inválido.');
}

try {
    date_default_timezone_set('Europe/Lisbon');

    $db = getDB();
    $userId = getUserId();
    $rentalId = isset($_POST['rental_id']) ? (int) $_POST['rental_id'] : 0;

    if ($userId === null || $rentalId <= 0) {
        respond(400, false, 'Aluguer inválido.');
    }

    $stmt = $db->prepare('
        SELECT id, start_time, status
        FROM equipment_rentals
        WHERE id = ?
          AND user_id = ?
    ');
    $stmt->execute([$rentalId, $userId]);
    $rental = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rental) {
        respond(404, false, 'Aluguer não encontrado.');
    }

    if ($rental['status'] !== 'active') {
        respond(400, false, 'Este aluguer já não está ativo.');
    }

    if (substr((string) $rental['start_time'], 0, 10) !== date('Y-m-d')) {
        respond(400, false, 'Só podes cancelar alugueres do dia atual.');
    }

    $stmt = $db->prepare('
        UPDATE equipment_rentals
        SET status = "cancelled"
        WHERE id = ?
          AND user_id = ?
          AND status = "active"
    ');
    $stmt->execute([$rentalId, $userId]);

    echo json_encode(['success' => true, 'message' => 'Aluguer cancelado com sucesso.']);
} catch (Throwable $exception) {
    respond(500, false, 'Erro ao cancelar aluguer.');
}
