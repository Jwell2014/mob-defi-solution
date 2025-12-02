<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiAuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $apiBearerToken,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // On ne protège que les endpoints d'API v1
        if (!str_starts_with($request->getPathInfo(), '/api/v1')) {
            return;
        }

        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $event->setResponse($this->unauthorizedResponse('Missing or invalid Authorization header.'));
            return;
        }

        $token = substr($authHeader, 7); // après "Bearer "

        if ($token !== $this->apiBearerToken) {
            $event->setResponse($this->unauthorizedResponse('Invalid bearer token.'));
            return;
        }

        // sinon → on laisse passer la requête vers le contrôleur
    }

    private function unauthorizedResponse(string $detail): JsonResponse
    {
        return new JsonResponse(
            [
                'code'    => 'UNAUTHORIZED',
                'message' => 'Authentication required.',
                'details' => [$detail],
            ],
            401
        );
    }
}