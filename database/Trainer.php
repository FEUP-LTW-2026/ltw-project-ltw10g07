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

    private static function formatTrainer(array $trainer): array {
        $badges = array_filter(array_map('trim', explode(',', $trainer['specializations'] ?? '')));

        return [
            'id' => (int) $trainer['id'],
            'name' => $trainer['name'],
            'photo' => $trainer['photo'] ?: '../img/logo.jpg',
            'specialty' => $badges !== [] ? implode(' · ', array_slice($badges, 0, 2)) : 'Personal Trainer',
            'rating' => (float) $trainer['rating'],
            'sessions' => 0,
            'experience' => 'Por definir',
            'badges' => $badges,
            'bio' => $trainer['bio'] ?? '',
        ];
    }
}
