<?php

namespace App\Controller;

use App\Service\AnalyticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class StatsController extends AbstractController
{
    #[Route('/api/v1/stats/distances', name: 'stats_distances', methods: ['GET'])]
    public function getAnalyticDistances(
        Request $request,
        AnalyticsService $analyticsService
    ): JsonResponse {
        $fromParam   = $request->query->get('from');   // ex: 2025-01-01
        $toParam     = $request->query->get('to');     // ex: 2025-01-31
        $groupBy     = $request->query->get('groupBy', 'none');

        $allowedGroupBy = ['day', 'month', 'year', 'none'];
        if (!in_array($groupBy, $allowedGroupBy, true)) {
            return $this->errorResponse(
                'INVALID_PARAMETERS',
                'Valeur groupBy invalide.',
                ['groupBy doit prendre l\'une des valeurs suivantes : jour, mois, année, aucun.'],
                400
            );
        }

        $fromDate = null;
        $toDate   = null;
        $details  = [];

        if ($fromParam) {
            $fromDate = \DateTimeImmutable::createFromFormat('Y-m-d', $fromParam);
            if (!$fromDate) {
                $details[] = sprintf('Date de début invalide : "%s". Format attendu : YYYY-MM-DD.', $fromParam);
            }
        }

        if ($toParam) {
            $toDate = \DateTimeImmutable::createFromFormat('Y-m-d', $toParam);
            if (!$toDate) {
                $details[] = sprintf('Date de fin invalide : "%s". Format attendu : YYYY-MM-DD.', $toParam);
            }
        }

        if (!empty($details)) {
            return $this->errorResponse(
                'INVALID_PARAMETERS',
                'Certains paramètres de requête sont invalides.',
                $details,
                400
            );
        }

        if ($fromDate && $toDate && $fromDate > $toDate) {
            return $this->errorResponse(
                'INVALID_PARAMETERS',
                'La date de début ne peut pas être postérieure à la date de fin.',
                [],
                400
            );
        }

        $result = $analyticsService->getAnalyticDistances($fromDate, $toDate, $groupBy);

        return new JsonResponse($result, 200);
    }

    private function errorResponse(string $code, string $message, array $details, int $status): JsonResponse
    {
        return new JsonResponse(
            [
                'code'    => $code,
                'message' => $message,
                'details' => $details,
            ],
            $status
        );
    }
}