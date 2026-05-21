<?php
declare(strict_types=1);

class User {
    public static function findByEmail(PDO $db, string $email): ?array {
        $stmt = $db->prepare('
            SELECT id, name, email, password_hash, role, is_active
            FROM user
            WHERE email = ?
        ');
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function create(PDO $db, string $name, string $email, string $passwordHash, string $role = 'member'): void {
        $stmt = $db->prepare('
            INSERT INTO user (name, email, password_hash, role)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$name, $email, $passwordHash, $role]);
    }
}
