<?php
declare(strict_types=1);

class User {
    public static function findById(PDO $db, int $id): ?array {
        $stmt = $db->prepare('
            SELECT id, name, username, email, password_hash, role, photo, is_active, created_at
            FROM user
            WHERE id = ?
        ');
        $stmt->execute([$id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function findByEmail(PDO $db, string $email): ?array {
        $stmt = $db->prepare('
            SELECT id, name, username, email, password_hash, role, is_active
            FROM user
            WHERE email = ?
        ');
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function findByUsername(PDO $db, string $username): ?array {
        $stmt = $db->prepare('
            SELECT id, name, username, email, password_hash, role, is_active
            FROM user
            WHERE username = ?
        ');
        $stmt->execute([$username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public static function create(PDO $db, string $name, string $username, string $email, string $passwordHash, string $role = 'member'): void {
        $stmt = $db->prepare('
            INSERT INTO user (name, username, email, password_hash, role)
            VALUES (?, ?, ?, ?, ?)
        ');
        $stmt->execute([$name, $username, $email, $passwordHash, $role]);
    }

    public static function updateProfile(PDO $db, int $id, string $name, string $username, string $email): void {
        $stmt = $db->prepare('
            UPDATE user
            SET name = ?, username = ?, email = ?
            WHERE id = ?
        ');
        $stmt->execute([$name, $username, $email, $id]);
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
