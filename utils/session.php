<?php
declare(strict_types=1);

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function setFlashMessages(string $type, array $messages): void {
    startSession();
    $_SESSION['flash'][$type] = $messages;
}

function getFlashMessages(string $type): array {
    startSession();

    $messages = $_SESSION['flash'][$type] ?? [];
    unset($_SESSION['flash'][$type]);

    return $messages;
}

function setOldInput(array $input): void {
    startSession();
    $_SESSION['old_input'] = $input;
}

function getOldInput(array $defaults = []): array {
    startSession();

    $oldInput = $_SESSION['old_input'] ?? [];
    unset($_SESSION['old_input']);

    return array_merge($defaults, $oldInput);
}

function loginUser(array $user): void {
    startSession();
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
}

function logoutUser(): void {
    startSession();
    session_unset();
    session_destroy();
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']);
}

function getUserName(): ?string {
    startSession();
    return $_SESSION['user_name'] ?? null;
}

function getUserId(): ?int {
    startSession();
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function getUserRole(): ?string {
    startSession();
    return $_SESSION['user_role'] ?? null;
}
