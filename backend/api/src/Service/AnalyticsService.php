<?php

namespace App\Service;

class AnalyticsService
{
    public function __construct(
        private readonly RouteStorage $storage
    ) {
    }

    /**
     * Calcule les distances agrégées par code analytique.
     *
     * @param \DateTimeImmutable|null $from  Date de début (jour inclus)
     * @param \DateTimeImmutable|null $to    Date de fin   (jour inclus)
     * @param string                  $groupBy  'none' | 'day' | 'month' | 'year'
     *
     * @return array{
     *   from: ?string,
     *   to: ?string,
     *   groupBy: string,
     *   items: array<int, array{
     *     analyticCode: string,
     *     totalDistanceKm: float,
     *     periodStart?: string,
     *     periodEnd?: string,
     *     group?: string
     *   }>
     * }
     */
    public function getAnalyticDistances(
        ?\DateTimeImmutable $from,
        ?\DateTimeImmutable $to,
        string $groupBy = 'none'
    ): array {
        $routes = $this->storage->loadAll();

        /** @var array<string, array<string, float>> $aggregates */
        $aggregates = [];

        foreach ($routes as $route) {
            if (!isset($route['analyticCode'], $route['distanceKm'], $route['createdAt'])) {
                continue;
            }

            $analyticCode = (string) $route['analyticCode'];
            $distance     = (float) $route['distanceKm'];

            $createdAt = \DateTimeImmutable::createFromFormat(
                \DateTimeInterface::ATOM,
                (string) $route['createdAt']
            );

            if (!$createdAt) {
                continue;
            }

            // On compare seulement les dates (Y-m-d)
            $createdDate = $createdAt->setTime(0, 0);

            if ($from && $createdDate < $from->setTime(0, 0)) {
                continue;
            }
            if ($to && $createdDate > $to->setTime(0, 0)) {
                continue;
            }

            // Clé de groupement
            $groupKey = 'none';

            if ($groupBy === 'day') {
                $groupKey = $createdDate->format('Y-m-d');
            } elseif ($groupBy === 'month') {
                $groupKey = $createdDate->format('Y-m');
            } elseif ($groupBy === 'year') {
                $groupKey = $createdDate->format('Y');
            }

            if (!isset($aggregates[$analyticCode])) {
                $aggregates[$analyticCode] = [];
            }

            if (!isset($aggregates[$analyticCode][$groupKey])) {
                $aggregates[$analyticCode][$groupKey] = 0.0;
            }

            $aggregates[$analyticCode][$groupKey] += $distance;
        }

        // Construction du résultat conforme au schéma AnalyticDistanceList
        $items = [];

        foreach ($aggregates as $analyticCode => $groups) {
            foreach ($groups as $groupKey => $totalDistance) {
                $item = [
                    'analyticCode'    => $analyticCode,
                    'totalDistanceKm' => round($totalDistance, 2),
                ];

                if ($groupBy !== 'none') {
                    $item['group'] = $groupKey;
                }

                $items[] = $item;
            }
        }

        return [
            'from'    => $from ? $from->format('Y-m-d') : null,
            'to'      => $to ? $to->format('Y-m-d') : null,
            'groupBy' => $groupBy,
            'items'   => $items,
        ];
    }
}