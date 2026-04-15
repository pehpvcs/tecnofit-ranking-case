<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\MovementRepository;

final class RankingService
{
    public function __construct(
        private readonly MovementRepository $repository
    ) {}

    /**
     * @return array{movement: string, ranking: list<array{name: string, personal_record: float, rank: int, record_date: string}>}
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    public function getRankingByIdentifier(string $identifier): array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            throw new \InvalidArgumentException('Movement identifier cannot be empty.');
        }

        $movement = $this->repository->findByIdentifier($identifier);

        if ($movement === null) {
            throw new \DomainException("Movement '{$identifier}' not found.");
        }

        $records = $this->repository->getRanking((int) $movement['id']);

        return [
            'movement' => $movement['name'],
            'ranking'  => array_map(
                fn(array $row): array => [
                    'name'            => $row['user_name'],
                    'personal_record' => (float) $row['personal_record'],
                    'rank'            => (int)   $row['position'],
                    'record_date'     => $row['record_date'],
                ],
                $records
            ),
        ];
    }
}
