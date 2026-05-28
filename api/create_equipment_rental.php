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

function rollbackAndRespond(PDO $db, int $statusCode, bool $success, string $message): void {
    $db->exec('ROLLBACK');

    respond($statusCode, $success, $message);
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

function hasActiveRentalForEquipmentToday(PDO $db, int $userId, int $equipmentId, string $today): bool {
    $stmt = $db->prepare('
        SELECT COUNT(*)
        FROM equipment_rentals
        WHERE user_id = ?
          AND equipment_id = ?
          AND status = "active"
          AND DATE(start_time) = ?
    ');
    $stmt->execute([$userId, $equipmentId, $today]);

    return (int) $stmt->fetchColumn() > 0;
}

if (!isLoggedIn()) {
    respond(403, false, 'Inicia sessão para alugares equipamentos.');
}

if (!in_array(getUserRole(), ['member', 'trainer'], true)) {
    respond(403, false, 'Apenas membros e treinadores podem alugar equipamentos.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, false, 'Método inválido.');
}

try {
    date_default_timezone_set('Europe/Lisbon');

    $db = getDB();
    $userId = getUserId();
    $equipmentId = isset($_POST['equipment_id']) ? (int) $_POST['equipment_id'] : 0;
    $startTimeInput = trim($_POST['start_time'] ?? '');
    $endTimeInput = trim($_POST['end_time'] ?? '');

    if ($userId === null || $equipmentId <= 0 || $startTimeInput === '' || $endTimeInput === '') {
        respond(400, false, 'Dados inválidos.');
    }

    $start = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startTimeInput);
    $startErrors = DateTimeImmutable::getLastErrors();
    $end = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $endTimeInput);
    $endErrors = DateTimeImmutable::getLastErrors();

    if (
        !$start
        || !$end
        || ($startErrors !== false && ($startErrors['warning_count'] > 0 || $startErrors['error_count'] > 0))
        || ($endErrors !== false && ($endErrors['warning_count'] > 0 || $endErrors['error_count'] > 0))
    ) {
        respond(400, false, 'Horário inválido.');
    }

    $startTime = $start->format('Y-m-d H:i:s');
    $endTime = $end->format('Y-m-d H:i:s');
    $now = new DateTimeImmutable('now');
    $today = $now->format('Y-m-d');

    if ($start->format('Y-m-d') !== $today || $end->format('Y-m-d') !== $today || $end <= $start) {
        respond(400, false, 'O aluguer tem de pertencer ao dia atual.');
    }

    if ($start <= $now) {
        respond(400, false, 'Seleciona um horário que ainda não tenha começado.');
    }

    $slotDuration = $end->getTimestamp() - $start->getTimestamp();
    $startHour = (int) $start->format('G');
    $startMinute = (int) $start->format('i');
    $endMinute = (int) $end->format('i');

    if ($slotDuration !== 3600 || $startHour < 9 || $startHour > 21 || $startMinute !== 0 || $endMinute !== 0) {
        respond(400, false, 'Seleciona uma slot horária válida.');
    }

    $db->exec('BEGIN IMMEDIATE');

    if (hasActiveRentalForEquipmentToday($db, $userId, $equipmentId, $today)) {
        rollbackAndRespond($db, 409, false, 'Já alugaste este equipamento hoje.');
    }

    $stmt = $db->prepare('
        SELECT id, total_quantity, maintenance_quantity, rental_available
        FROM equipment
        WHERE id = ?
    ');
    $stmt->execute([$equipmentId]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipment) {
        rollbackAndRespond($db, 404, false, 'Equipamento não encontrado.');
    }

    if ((int) $equipment['rental_available'] !== 1) {
        rollbackAndRespond($db, 400, false, 'Este equipamento não permite aluguer.');
    }

    $baseAvailable = max(0, (int) $equipment['total_quantity'] - (int) $equipment['maintenance_quantity']);
    $activeRentals = countActiveRentals($db, $equipmentId, $startTime, $endTime);

    if ($baseAvailable - $activeRentals <= 0) {
        rollbackAndRespond($db, 409, false, 'Este horário já não se encontra disponível.');
    }

    $stmt = $db->prepare('
        INSERT INTO equipment_rentals (equipment_id, user_id, start_time, end_time, status)
        VALUES (?, ?, ?, ?, "active")
    ');
    $stmt->execute([$equipmentId, $userId, $startTime, $endTime]);
    $db->exec('COMMIT');

    echo json_encode(['success' => true, 'message' => 'Aluguer confirmado com sucesso.']);
} catch (Throwable $exception) {
    if (isset($db) && $db instanceof PDO) {
        try {
            $db->exec('ROLLBACK');
        } catch (Throwable $rollbackException) {
        }
    }

    respond(500, false, 'Erro ao criar aluguer.');
}
