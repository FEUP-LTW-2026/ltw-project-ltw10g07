<?php
declare(strict_types=1);

class ClassCatalog {
    public static function getAll(PDO $db): array {
        $stmt = $db->query('
            SELECT
                class.id,
                class.trainer_id,
                class.name,
                class.description,
                class.type,
                class.intensity,
                class.duration,
                class.capacity,
                class.image,
                user.name AS trainer_name
            FROM class
            LEFT JOIN trainer ON trainer.id = class.trainer_id
            LEFT JOIN user ON user.id = trainer.user_id
            ORDER BY class.name ASC
        ');

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById(PDO $db, int $id): ?array {
        $stmt = $db->prepare('
            SELECT
                class.id,
                class.trainer_id,
                class.name,
                class.description,
                class.type,
                class.intensity,
                class.duration,
                class.capacity,
                class.image,
                user.name AS trainer_name,
                user.photo AS trainer_photo
            FROM class
            LEFT JOIN trainer ON trainer.id = class.trainer_id
            LEFT JOIN user ON user.id = trainer.user_id
            WHERE class.id = ?
        ');
        $stmt->execute([$id]);

        $class = $stmt->fetch(PDO::FETCH_ASSOC);

        return $class ?: null;
    }

    public static function create(PDO $db, ?int $trainerId, string $name, string $description, string $type, string $intensity, int $duration, int $capacity, string $image): void {
        $stmt = $db->prepare('
            INSERT INTO class (trainer_id, name, description, type, intensity, duration, capacity, image)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$trainerId, $name, $description, $type, $intensity, $duration, $capacity, $image]);
    }

    public static function createWithSession(PDO $db, ?int $trainerId, string $name, string $description, string $type, string $intensity, int $duration, int $capacity, string $image, string $sessionDate, string $startTime, string $endTime, string $room): void {
        $db->beginTransaction();

        try {
            self::create($db, $trainerId, $name, $description, $type, $intensity, $duration, $capacity, $image);
            $classId = (int) $db->lastInsertId();

            $stmt = $db->prepare('
                INSERT INTO class_session (class_id, session_date, start_time, end_time, room)
                VALUES (?, ?, ?, ?, ?)
            ');
            $stmt->execute([$classId, $sessionDate, $startTime, $endTime, $room]);

            $db->commit();
        } catch (Throwable $exception) {
            $db->rollBack();
            throw $exception;
        }
    }

    public static function update(PDO $db, int $id, ?int $trainerId, string $name, string $description, string $type, string $intensity, int $duration, int $capacity, string $image): void {
        $stmt = $db->prepare('
            UPDATE class
            SET trainer_id = ?, name = ?, description = ?, type = ?, intensity = ?, duration = ?, capacity = ?, image = ?
            WHERE id = ?
        ');
        $stmt->execute([$trainerId, $name, $description, $type, $intensity, $duration, $capacity, $image, $id]);
    }

    public static function delete(PDO $db, int $id): void {
        $stmt = $db->prepare('DELETE FROM class WHERE id = ?');
        $stmt->execute([$id]);
    }
}
