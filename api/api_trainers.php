<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = getDB();
    $query = trim($_GET['q'] ?? '');
    $trainers = $query === '' ? Trainer::getAll($db) : Trainer::search($db, $query);

    echo json_encode([
        'status' => 'success',
        'data' => array_map(static function (array $trainer): array {
            return [
                'id' => (int) $trainer['id'],
                'name' => $trainer['name'],
                'photo' => $trainer['photo'],
                'initials' => $trainer['initials'],
                'specialty' => $trainer['specialty'],
                'rating' => (float) $trainer['rating'],
                'sessions' => (int) $trainer['sessions'],
                'experience' => $trainer['experience'],
                'badges' => $trainer['badges'],
                'bio' => $trainer['bio'],
            ];
        }, $trainers),
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Erro ao carregar treinadores.']);
}
