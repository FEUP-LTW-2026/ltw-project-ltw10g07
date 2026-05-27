<?php
declare(strict_types=1);

class Equipment {
    public static function getAll(PDO $db): array {
        $stmt = $db->query('
            SELECT id, name, category, status, location
            FROM equipment
            ORDER BY category ASC, name ASC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(PDO $db, string $name, string $category, string $status, string $location): void {
        $stmt = $db->prepare('
            INSERT INTO equipment (name, category, status, location)
            VALUES (?, ?, ?, ?)
        ');
        $stmt->execute([$name, $category, $status, $location]);
    }

    public static function update(PDO $db, int $id, string $name, string $category, string $status, string $location): void {
        $stmt = $db->prepare('
            UPDATE equipment
            SET name = ?, category = ?, status = ?, location = ?
            WHERE id = ?
        ');
        $stmt->execute([$name, $category, $status, $location, $id]);
    }

    public static function delete(PDO $db, int $id): void {
        $stmt = $db->prepare('DELETE FROM equipment WHERE id = ?');
        $stmt->execute([$id]);
    }
}
