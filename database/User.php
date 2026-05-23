<?php
declare(strict_types=1);

class User {
    public static function findById(PDO $db, int $id): ?array {
        $stmt = $db->prepare('
            SELECT id, name, email, password_hash, role, photo, is_active, created_at
            FROM user
            WHERE id = ?
        ');
        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

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

    public static function updateProfile(PDO $db, int $id, string $name, string $email): void {
        $stmt = $db->prepare('
            UPDATE user
            SET name = ?, email = ?
            WHERE id = ?
        ');
        $stmt->execute([$name, $email, $id]);
    }

    public static function updatePassword(PDO $db, int $id, string $passwordHash): void {
        $stmt = $db->prepare('
            UPDATE user
            SET password_hash = ?
            WHERE id = ?
        ');
        $stmt->execute([$passwordHash, $id]);
    }
}
