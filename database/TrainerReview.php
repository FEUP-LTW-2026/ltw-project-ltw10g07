<?php
declare(strict_types=1);

class TrainerReview {
    public static function ensureSchema(PDO $db): void {
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
    }

    public static function upsert(PDO $db, int $userId, int $trainerId, int $rating, string $comment): void {
        self::ensureSchema($db);

        $stmt = $db->prepare('
            INSERT INTO trainer_review (user_id, trainer_id, rating, comment)
            VALUES (?, ?, ?, ?)
            ON CONFLICT(user_id, trainer_id) DO UPDATE SET
                rating = excluded.rating,
                comment = excluded.comment,
                updated_at = CURRENT_TIMESTAMP
        ');
        $stmt->execute([$userId, $trainerId, $rating, $comment]);
    }

    public static function findByUserAndTrainer(PDO $db, int $userId, int $trainerId): ?array {
        self::ensureSchema($db);

        $stmt = $db->prepare('
            SELECT rating, comment
            FROM trainer_review
            WHERE user_id = ? AND trainer_id = ?
        ');
        $stmt->execute([$userId, $trainerId]);

        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        return $review ?: null;
    }

    public static function getForTrainer(PDO $db, int $trainerId): array {
        self::ensureSchema($db);

        $stmt = $db->prepare('
            SELECT
                trainer_review.rating,
                trainer_review.comment,
                trainer_review.created_at,
                user.name AS author,
                user.photo AS author_photo
            FROM trainer_review
            JOIN user ON user.id = trainer_review.user_id
            WHERE trainer_review.trainer_id = ?
            ORDER BY trainer_review.updated_at DESC, trainer_review.created_at DESC
        ');
        $stmt->execute([$trainerId]);

        $reviews = [];

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $review) {
            $reviews[] = [
                'author' => $review['author'],
                'author_photo' => $review['author_photo'],
                'author_initials' => self::initials($review['author']),
                'rating' => (int) $review['rating'],
                'comment' => $review['comment'],
                'created_at' => $review['created_at'],
            ];
        }

        return $reviews;
    }

    public static function refreshTrainerRating(PDO $db, int $trainerId): void {
        self::ensureSchema($db);

        $stmt = $db->prepare('
            SELECT AVG(rating) AS average_rating
            FROM trainer_review
            WHERE trainer_id = ?
        ');
        $stmt->execute([$trainerId]);
        $average = $stmt->fetchColumn();

        $rating = $average !== null ? round((float) $average, 1) : 0;

        $update = $db->prepare('
            UPDATE trainer
            SET rating = ?
            WHERE id = ?
        ');
        $update->execute([$rating, $trainerId]);
    }

    public static function initials(string $name): string {
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
