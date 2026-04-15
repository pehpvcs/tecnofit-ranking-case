<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\Response;
use App\Repository\MovementRepository;
use App\Service\RankingService;

final class MovementController
{
    private RankingService $rankingService;

    public function __construct(private readonly Response $response)
    {
        $this->rankingService = new RankingService(new MovementRepository());
    }

    /**
     * GET /movements/{identifier}/ranking
     */
    public function ranking(string $identifier): void
    {
        try {
            $data = $this->rankingService->getRankingByIdentifier($identifier);
            $this->response->json($data);

        } catch (\InvalidArgumentException $e) {
            $this->response->badRequest($e->getMessage());

        } catch (\DomainException $e) {
            $this->response->notFound($e->getMessage());

        } catch (\RuntimeException $e) {
            error_log('[RankingController] ' . $e->getMessage());
            $this->response->internalError();
        }
    }
}
