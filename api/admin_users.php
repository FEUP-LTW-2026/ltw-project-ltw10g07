<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';

header('Content-Type: application/json; charset=utf-8');

if (!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit;
}

$query = trim($_GET['q'] ?? '');
$users = User::searchForAdmin(getDB(), $query);

echo json_encode([
    'users' => array_map(static function (array $user): array {
        return [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_active' => (int) $user['is_active'] === 1,
        ];
    }, $users),
]);
