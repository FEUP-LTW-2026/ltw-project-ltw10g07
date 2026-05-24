<?php
declare(strict_types=1);

class Trainer {
    public static function getAll(PDO $db): array {
        $stmt = $db->query('
            SELECT
                trainer.id,
                user.name,
                user.photo,
                trainer.bio,
                trainer.specializations,
                trainer.rating
            FROM trainer
            JOIN user ON user.id = trainer.user_id
            ORDER BY trainer.rating DESC, user.name ASC
        ');

        $trainers = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $trainer) {
            $trainers[] = self::formatTrainer($trainer);
        }

        return $trainers;
    }

    public static function getFeatured(PDO $db, int $limit = 3): array {
        $stmt = $db->prepare('
            SELECT
                trainer.id,
                user.name,
                user.photo,
                trainer.bio,
                trainer.specializations,
                trainer.rating
            FROM trainer
            JOIN user ON user.id = trainer.user_id
            ORDER BY trainer.rating DESC, user.name ASC
            LIMIT ?
        ');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        $trainers = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $trainer) {
            $trainers[] = self::formatTrainer($trainer);
        }

        return $trainers;
    }

    public static function findById(PDO $db, int $id): ?array {
        $stmt = $db->prepare('
            SELECT
                trainer.id,
                user.name,
                user.photo,
                trainer.bio,
                trainer.specializations,
                trainer.certifications,
                trainer.rating,
                COUNT(class_session.id) AS sessions
            FROM trainer
            JOIN user ON user.id = trainer.user_id
            LEFT JOIN class ON class.trainer_id = trainer.id
            LEFT JOIN class_session ON class_session.class_id = class.id
            WHERE trainer.id = ?
            GROUP BY trainer.id, user.name, user.photo, trainer.bio, trainer.specializations, trainer.certifications, trainer.rating
        ');
        $stmt->execute([$id]);

        $trainer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$trainer) {
            return null;
        }

        return self::formatTrainer($trainer) + [
            'certifications' => self::splitList($trainer['certifications'] ?? ''),
            'reviews_count' => self::countReviews($db, $id),
            'classes' => self::getClasses($db, $id),
        ];
    }

    private static function formatTrainer(array $trainer): array {
        $badges = self::splitList($trainer['specializations'] ?? '');

        return [
            'id' => (int) $trainer['id'],
            'name' => $trainer['name'],
            'photo' => $trainer['photo'] ?: null,
            'initials' => self::initials($trainer['name']),
            'specialty' => $badges !== [] ? implode(' · ', array_slice($badges, 0, 2)) : 'Personal Trainer',
            'rating' => (float) $trainer['rating'],
            'sessions' => isset($trainer['sessions']) ? (int) $trainer['sessions'] : 0,
            'experience' => 'Por definir',
            'badges' => $badges,
            'bio' => $trainer['bio'] ?? '',
        ];
    }

    private static function getClasses(PDO $db, int $trainerId): array {
        $stmt = $db->prepare('
            SELECT
                class.id,
                class.name,
                class.duration,
                class.capacity,
                MIN(class_session.start_time) AS start_time,
                COUNT(enrollment.id) AS enrollments
            FROM class
            LEFT JOIN class_session ON class_session.class_id = class.id
            LEFT JOIN enrollment ON enrollment.session_id = class_session.id AND enrollment.status = \'active\'
            WHERE class.trainer_id = ?
            GROUP BY class.id, class.name, class.duration, class.capacity
            ORDER BY class.name ASC
            LIMIT 3
        ');
        $stmt->execute([$trainerId]);

        $classes = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $class) {
            $classes[] = [
                'id' => (int) $class['id'],
                'name' => $class['name'],
                'time' => $class['start_time'] ? substr($class['start_time'], 0, 5) : 'Por marcar',
                'duration' => ((int) $class['duration']) . ' min',
                'spots' => max(0, (int) $class['capacity'] - (int) $class['enrollments']),
            ];
        }

        return $classes;
    }

    private static function countReviews(PDO $db, int $trainerId): int {
        $db->exec('
            CREATE TABLE IF NOT EXISTS trainer_review (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                trainer_id INTEGER NOT NULL,
                rating INTEGER NOT NULL CHECK (rating BETWEEN 0 AND 5),
                comment TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
                FOREIGN KEY (trainer_id) REFERENCES trainer(id) ON DELETE CASCADE,
                UNIQUE(user_id, trainer_id)
            )
        ');

        $stmt = $db->prepare('SELECT COUNT(*) FROM trainer_review WHERE trainer_id = ?');
        $stmt->execute([$trainerId]);

        return (int) $stmt->fetchColumn();
    }

    private static function splitList(?string $value): array {
        return array_values(array_filter(array_map('trim', explode(',', $value ?? ''))));
    }

    private static function initials(string $name): string {
        $parts = preg_split('/\s+/', trim($name));
        $initials = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            $initials .= function_exists('mb_substr')
                ? mb_strtoupper(mb_substr($part, 0, 1))
                : strtoupper(substr($part, 0, 1));

            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials !== '' ? $initials : '?';
    }
}
