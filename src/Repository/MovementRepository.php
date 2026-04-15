<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\Connection;
use PDO;

final class MovementRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
    }

    /**
     * @return array{id: int, name: string}|null
     */
    
    public function findByIdentifier(string $identifier): ?array
    {
        if (ctype_digit($identifier)) {
            $stmt = $this->pdo->prepare(
                'SELECT id, name FROM movement WHERE id = :id LIMIT 1'
            );
            $stmt->execute([':id' => (int) $identifier]);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT id, name FROM movement WHERE name = :name LIMIT 1'
            );
            $stmt->execute([':name' => $identifier]);
        }

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    /**
     * @return array<int, array{user_name: string, personal_record: float, position: int, record_date: string}>
     */

    public function getRanking(int $movementId): array
    {
        $sql = '
            WITH user_best_value AS (
                SELECT
                    user_id,
                    movement_id,
                    MAX(value) AS best_value
                FROM personal_record
                WHERE movement_id = :movement_id
                GROUP BY user_id, movement_id
            ),
            user_best_with_date AS (
                SELECT
                    ubv.user_id,
                    ubv.movement_id,
                    ubv.best_value,
                    MIN(pr.date) AS record_date
                FROM user_best_value ubv
                JOIN personal_record pr
                    ON  pr.user_id     = ubv.user_id
                    AND pr.movement_id = ubv.movement_id
                    AND pr.value       = ubv.best_value
                GROUP BY ubv.user_id, ubv.movement_id, ubv.best_value
            )
            SELECT
                u.name                                              AS user_name,
                ubd.best_value                                      AS personal_record,
                DENSE_RANK() OVER (ORDER BY ubd.best_value DESC)   AS position,
                ubd.record_date
            FROM user_best_with_date ubd
            JOIN user u ON u.id = ubd.user_id
            ORDER BY position ASC, u.name ASC
        ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':movement_id' => $movementId]);

        return $stmt->fetchAll();
    }
}
