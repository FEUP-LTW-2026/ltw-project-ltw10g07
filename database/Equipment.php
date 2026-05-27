<?php
declare(strict_types=1);

class Equipment {
    public static function getAll(PDO $db): array {
        $stmt = $db->query('
            SELECT id, name, category, status, location, total_quantity, maintenance_quantity, rental_available
            FROM equipment
            ORDER BY category ASC, name ASC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create(PDO $db, string $name, string $category, string $status, string $location, int $totalQuantity, int $maintenanceQuantity, int $rentalAvailable): void {
        $stmt = $db->prepare('
            INSERT INTO equipment (name, category, status, location, total_quantity, maintenance_quantity, rental_available)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$name, $category, $status, $location, $totalQuantity, $maintenanceQuantity, $rentalAvailable]);
    }

    public static function update(PDO $db, int $id, string $name, string $category, string $status, string $location, int $totalQuantity, int $maintenanceQuantity, int $rentalAvailable): void {
        $stmt = $db->prepare('
            UPDATE equipment
            SET name = ?, category = ?, status = ?, location = ?, total_quantity = ?, maintenance_quantity = ?, rental_available = ?
            WHERE id = ?
        ');
        $stmt->execute([$name, $category, $status, $location, $totalQuantity, $maintenanceQuantity, $rentalAvailable, $id]);
    }

    public static function delete(PDO $db, int $id): void {
        $stmt = $db->prepare('DELETE FROM equipment WHERE id = ?');
        $stmt->execute([$id]);
    }
}
