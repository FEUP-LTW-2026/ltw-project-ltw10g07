<?php
declare(strict_types=1);

class Equipment {
    public static function getAll(PDO $db): array {
        $stmt = $db->query('
            SELECT id, name, category, status, location, quantity, maintenance_quantity, rentable
            FROM equipment
            ORDER BY category ASC, name ASC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(PDO $db, string $name, string $category, string $status, string $location, int $quantity, int $maintenanceQuantity, int $rentable): void {
        $stmt = $db->prepare('
            INSERT INTO equipment (name, category, status, location, quantity, maintenance_quantity, rentable)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$name, $category, $status, $location, $quantity, $maintenanceQuantity, $rentable]);
    }

    public static function update(PDO $db, int $id, string $name, string $category, string $status, string $location, int $quantity, int $maintenanceQuantity, int $rentable): void {
        $stmt = $db->prepare('
            UPDATE equipment
            SET name = ?, category = ?, status = ?, location = ?, quantity = ?, maintenance_quantity = ?, rentable = ?
            WHERE id = ?
        ');
        $stmt->execute([$name, $category, $status, $location, $quantity, $maintenanceQuantity, $rentable, $id]);
    }

    public static function delete(PDO $db, int $id): void {
        $stmt = $db->prepare('DELETE FROM equipment WHERE id = ?');
        $stmt->execute([$id]);
    }
}
