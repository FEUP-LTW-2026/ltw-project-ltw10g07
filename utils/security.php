<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrfToken(): string {
    startSession();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrfInput(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '" />';
}

function verifyCsrfToken(string $redirectTo): void {
    startSession();

    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($token) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
        setFlashMessages('errors', ['Pedido inválido. Tenta novamente.']);
        header('Location: ' . $redirectTo);
        exit;
    }
}
