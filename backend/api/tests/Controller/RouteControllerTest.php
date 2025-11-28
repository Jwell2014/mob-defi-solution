<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RouteControllerTest extends WebTestCase
{
    /**
     * Vérifie qu'un appel POST /api/v1/routes avec des données valides
     * renvoie un statut HTTP 201 et un objet Route bien formé.
     */
    public function testCreateRouteReturns201OnSuccess(): void
    {
        $client = static::createClient();

        $payload = [
            'fromStationId' => 'MX',
            'toStationId'   => 'ZW',
            'analyticCode'  => 'ANA-123',
        ];

        $client->request(
            'POST',
            '/api/v1/routes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        // Statut HTTP
        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($client->getResponse()->getContent(), true);

        // Structure de la réponse
        $this->assertIsArray($data);
        $this->assertSame('MX', $data['fromStationId'] ?? null);
        $this->assertSame('ZW', $data['toStationId'] ?? null);
        $this->assertSame('ANA-123', $data['analyticCode'] ?? null);

        $this->assertArrayHasKey('distanceKm', $data);
        $this->assertArrayHasKey('path', $data);
        $this->assertIsFloat($data['distanceKm']);
        $this->assertIsArray($data['path']);
        $this->assertNotEmpty($data['path']);
    }


    /**
     * Vérifie qu'un body JSON incomplet ou invalide
     * renvoie une erreur 400 avec un objet Error.
     */
    public function testCreateRouteReturns400WhenBodyIsInvalid(): void
    {
        $client = static::createClient();

        // Body JSON invalide : champ manquant + string vide
        $payload = [
            'fromStationId' => '',
            // 'toStationId' manquant
            'analyticCode'  => 'ANA-123',
        ];

        $client->request(
            'POST',
            '/api/v1/routes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('INVALID_REQUEST', $data['code'] ?? null);
        $this->assertArrayHasKey('details', $data);
        $this->assertNotEmpty($data['details']);
    }


    /**
     * Vérifie qu'une station inconnue (ex: "XXX")
     * renvoie une erreur 422 avec le code UNKNOWN_STATION.
     */
    public function testCreateRouteReturns422ForUnknownStation(): void
    {
        $client = static::createClient();

        $payload = [
            'fromStationId' => 'XXX', // n’existe pas
            'toStationId'   => 'ZW',
            'analyticCode'  => 'ANA-123',
        ];

        $client->request(
            'POST',
            '/api/v1/routes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('UNKNOWN_STATION', $data['code'] ?? null);
        $this->assertArrayHasKey('details', $data);
        $this->assertNotEmpty($data['details']);
    }

    /**
     * Vérifie que lorsque deux stations existent mais qu'aucun chemin n'est possible
     * (ex: CAUX -> MX), l'API renvoie une erreur 422 avec le code NO_ROUTE.
     */
    public function testCreateRouteReturns422WhenNoRouteExists(): void
    {
        $client = static::createClient();

        $payload = [
            'fromStationId' => 'CAUX',
            'toStationId'   => 'MX',
            'analyticCode'  => 'ANA-TEST',
        ];

        $client->request(
            'POST',
            '/api/v1/routes',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertResponseStatusCodeSame(422);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertSame('NO_ROUTE', $data['code'] ?? null);
        $this->assertArrayHasKey('details', $data);
        $this->assertNotEmpty($data['details']);
    }
}