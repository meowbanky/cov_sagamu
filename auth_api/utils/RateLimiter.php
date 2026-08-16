<?php
// utils/RateLimiter.php
//
// Simple database-backed rate limiting for unauthenticated endpoints.
//
// Deliberately DB-backed rather than APCu/Redis: this runs on shared hosting
// where no in-memory cache is guaranteed, and a limiter that silently does
// nothing is worse than none at all.

class RateLimiter
{
    /** Rows older than this are pruned opportunistically. */
    const PRUNE_AFTER_SECONDS = 86400;

    /** Prune roughly 1 request in N, to keep the table from growing forever. */
    const PRUNE_PROBABILITY = 50;

    /**
     * Records a hit and throws (429) if the caller has exceeded the allowance.
     *
     * @param string $action        logical bucket, e.g. 'member_search'
     * @param int    $maxHits       allowed hits within the window
     * @param int    $windowSeconds size of the sliding window
     * @throws Exception
     */
    public static function enforce(PDO $db, $action, $maxHits, $windowSeconds)
    {
        $bucket = $action . ':' . self::clientFingerprint();

        $stmt = $db->prepare(
            'SELECT COUNT(*) AS hits
               FROM tbl_rate_limits
              WHERE bucket = :bucket
                AND hit_at > DATE_SUB(NOW(), INTERVAL :window SECOND)'
        );
        $stmt->bindParam(':bucket', $bucket);
        $stmt->bindValue(':window', (int) $windowSeconds, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && (int) $row['hits'] >= $maxHits) {
            throw new Exception('Too many requests. Please wait a moment and try again.', 429);
        }

        $stmt = $db->prepare(
            'INSERT INTO tbl_rate_limits (bucket, hit_at) VALUES (:bucket, NOW())'
        );
        $stmt->bindParam(':bucket', $bucket);
        $stmt->execute();

        self::pruneOccasionally($db);
    }

    /**
     * Identifies the caller for rate-limiting purposes.
     *
     * Hashed so the table holds no raw IP addresses. Proxy headers are only
     * trusted for the leftmost entry, and it is only a rate-limit key — never
     * an authorisation decision — so spoofing costs the attacker nothing more
     * than a fresh bucket.
     */
    private static function clientFingerprint()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $candidate = trim($parts[0]);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                $ip = $candidate;
            }
        }

        return substr(hash('sha256', $ip), 0, 32);
    }

    private static function pruneOccasionally(PDO $db)
    {
        if (random_int(1, self::PRUNE_PROBABILITY) !== 1) {
            return;
        }

        try {
            $stmt = $db->prepare(
                'DELETE FROM tbl_rate_limits WHERE hit_at < DATE_SUB(NOW(), INTERVAL :age SECOND)'
            );
            $stmt->bindValue(':age', self::PRUNE_AFTER_SECONDS, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            // Pruning is housekeeping; never fail a request over it.
            error_log('RateLimiter prune failed: ' . $e->getMessage());
        }
    }
}
