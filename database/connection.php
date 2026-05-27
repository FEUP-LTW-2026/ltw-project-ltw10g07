<?php
declare(strict_types=1);

function getDB(): PDO {
    $path = __DIR__ . '/ltw.db';

    $db = new PDO('sqlite:' . $path);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA foreign_keys = ON;');
    ensureEquipmentSchema($db);

    return $db;
}

function ensureEquipmentSchema(PDO $db): void {
    $stmt = $db->query('PRAGMA table_info(equipment)');
    $columns = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');

    if (!in_array('maintenance_quantity', $columns, true)) {
        $db->exec('ALTER TABLE equipment ADD COLUMN maintenance_quantity INTEGER NOT NULL DEFAULT 0 CHECK (maintenance_quantity >= 0)');
    }

    if (!in_array('total_quantity', $columns, true)) {
        $db->exec('ALTER TABLE equipment ADD COLUMN total_quantity INTEGER NOT NULL DEFAULT 1 CHECK (total_quantity >= 0)');
        if (in_array('quantity', $columns, true)) {
            $db->exec('UPDATE equipment SET total_quantity = quantity');
        }
    }

    if (!in_array('rental_available', $columns, true)) {
        $db->exec('ALTER TABLE equipment ADD COLUMN rental_available INTEGER NOT NULL DEFAULT 0 CHECK (rental_available IN (0, 1))');
        if (in_array('rentable', $columns, true)) {
            $db->exec('UPDATE equipment SET rental_available = rentable');
        }
    }

    $db->exec('
        CREATE TABLE IF NOT EXISTS equipment_rentals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            equipment_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            start_time DATETIME NOT NULL,
            end_time DATETIME NOT NULL,
            status TEXT NOT NULL CHECK (status IN (\'active\', \'cancelled\', \'completed\')),
            FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        )
    ');

    $db->exec('
        UPDATE equipment_rentals
        SET status = \'completed\'
        WHERE status = \'active\'
          AND DATE(start_time) < DATE(\'now\', \'localtime\')
    ');

    $db->exec('
        UPDATE equipment_rentals
        SET status = \'cancelled\'
        WHERE status = \'active\'
          AND id NOT IN (
              SELECT MIN(id)
              FROM equipment_rentals
              WHERE status = \'active\'
              GROUP BY user_id, equipment_id, DATE(start_time)
          )
    ');

    $db->exec('DROP INDEX IF EXISTS one_active_equipment_rental_per_user_day');

    $db->exec('
        CREATE UNIQUE INDEX IF NOT EXISTS one_active_equipment_rental_per_user_equipment_day
        ON equipment_rentals(user_id, equipment_id, DATE(start_time))
        WHERE status = \'active\'
    ');
}
