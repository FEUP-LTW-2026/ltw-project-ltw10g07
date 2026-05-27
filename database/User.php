<?php
declare(strict_types=1);

class User {
    public static function getAll(PDO $db): array {
        $stmt = $db->query('
            SELECT id, name, username, email, role, photo, is_active, created_at
            FROM user
            ORDER BY role ASC, name ASC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function searchForAdmin(PDO $db, string $query): array {
        $query = trim($query);

        if ($query === '') {
            return self::getAll($db);
        }

        $term = '%' . $query . '%';
        $stmt = $db->prepare('
            SELECT id, name, username, email, role, photo, is_active, created_at
            FROM user
            WHERE name LIKE ?
               OR username LIKE ?
               OR email LIKE ?
               OR role LIKE ?
            ORDER BY role ASC, name ASC
        ');
        $stmt->execute([$term, $term, $term, $term]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

    public static function createWithStatus(PDO $db, string $name, string $username, string $email, string $passwordHash, string $role, int $isActive): int {
        $stmt = $db->prepare('
            INSERT INTO user (name, username, email, password_hash, role, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$name, $username, $email, $passwordHash, $role, $isActive]);

        return (int) $db->lastInsertId();
    }

    public static function updateProfile(PDO $db, int $id, string $name, string $username, string $email): void {
        $stmt = $db->prepare('
            UPDATE user
            SET name = ?, username = ?, email = ?
            WHERE id = ?
        ');
        $stmt->execute([$name, $username, $email, $id]);
    }

    public static function updateAdminProfile(PDO $db, int $id, string $name, string $username, string $email, string $role, int $isActive): void {
        $stmt = $db->prepare('
            UPDATE user
            SET name = ?, username = ?, email = ?, role = ?, is_active = ?
            WHERE id = ?
        ');
        $stmt->execute([$name, $username, $email, $role, $isActive, $id]);
    }

    public static function updatePassword(PDO $db, int $id, string $passwordHash): void {
        $stmt = $db->prepare('
            UPDATE user
            SET password_hash = ?
            WHERE id = ?
        ');
        $stmt->execute([$passwordHash, $id]);
    }

    public static function ensureTrainerProfile(PDO $db, int $userId): void {
        $stmt = $db->prepare('
            INSERT OR IGNORE INTO trainer (user_id, bio, specializations, certifications, rating)
            VALUES (?, \'\', \'\', \'\', 0)
        ');
        $stmt->execute([$userId]);
    }

    public static function deleteTrainerProfile(PDO $db, int $userId): void {
        $stmt = $db->prepare('DELETE FROM trainer WHERE user_id = ?');
        $stmt->execute([$userId]);
    }
}
