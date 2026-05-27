<?php
declare(strict_types=1);

class ClassReview {
    public static function memberHasEnrolledInClass(PDO $db, int $userId, int $classId): bool {
    $stmt = $db->prepare("
        SELECT 1
        FROM enrollment
        JOIN class_session ON class_session.id = enrollment.session_id
        WHERE enrollment.user_id = ?
          AND class_session.class_id = ?
          AND enrollment.status = 'active'
          AND datetime(class_session.session_date || ' ' || class_session.end_time) < datetime('now')
        LIMIT 1
    ");
    $stmt->execute([$userId, $classId]);
    return (bool) $stmt->fetch();
}

    public static function findByUserAndClass(PDO $db, int $userId, int $classId): ?array {
        $stmt = $db->prepare('
            SELECT id, rating, comment, created_at
            FROM review
            WHERE user_id = ? AND class_id = ?
        ');
        $stmt->execute([$userId, $classId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public static function upsert(PDO $db, int $userId, int $classId, int $rating, string $comment): void {
        $stmt = $db->prepare('
            INSERT INTO review (user_id, class_id, rating, comment)
            VALUES (?, ?, ?, ?)
            ON CONFLICT(user_id, class_id) DO UPDATE SET
                rating  = excluded.rating,
                comment = excluded.comment
        ');
        $stmt->execute([$userId, $classId, $rating, $comment]);
    }

    public static function getForClass(PDO $db, int $classId): array {
        $stmt = $db->prepare('
            SELECT
                review.rating,
                review.comment,
                review.created_at,
                user.name   AS author,
                user.photo  AS author_photo
            FROM review
            JOIN user ON user.id = review.user_id
            WHERE review.class_id = ?
            ORDER BY review.created_at DESC
        ');
        $stmt->execute([$classId]);

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $rows[] = [
                'author'        => $r['author'],
                'author_photo'  => $r['author_photo'],
                'author_initials' => self::initials($r['author']),
                'rating'        => (int) $r['rating'],
                'comment'       => $r['comment'],
                'created_at'    => $r['created_at'],
            ];
        }
        return $rows;
    }

    public static function averageRating(PDO $db, int $classId): float {
        $stmt = $db->prepare('SELECT AVG(rating) FROM review WHERE class_id = ?');
        $stmt->execute([$classId]);
        return round((float) ($stmt->fetchColumn() ?? 0), 1);
    }

    public static function delete(PDO $db, int $userId, int $classId): void {
        $stmt = $db->prepare('DELETE FROM review WHERE user_id = ? AND class_id = ?');
        $stmt->execute([$userId, $classId]);
    }

    private static function initials(string $name): string {
        $parts = preg_split('/\s+/', trim($name));
        $initials = '';
        foreach ($parts as $part) {
            if ($part === '') continue;
            $initials .= function_exists('mb_substr')
                ? mb_strtoupper(mb_substr($part, 0, 1))
                : strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) break;
        }
        return $initials !== '' ? $initials : '?';
    }
}
