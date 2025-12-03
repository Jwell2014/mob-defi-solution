# 🚆 MOB – Défi Full-Stack : Routage de train & Statistiques

**Backend : Symfony 7 (PHP 8.4)**
**Frontend : Vue 3 + Vuetify**
**Docker / Typescript**

Cette solution répond à l’intégralité du défi technique proposé par **MOB**, incluant :

* un **calculateur de trajectoire ferroviaire** (Dijkstra),
* une **API REST** conforme à l’OpenAPI,
* une **persistance des trajets** pour les statistiques,
* une **UI complète** Vue 3 + Vuetify,
* une **visualisation graphique** des données (Chart.js),
* un **déploiement Docker** en une seule commande.

---

## Vue d’ensemble

* **Routage ferroviaire** : calcul du plus court chemin entre stations à partir des fichiers `stations.json` & `distances.json`.
* **API REST** : sécurisée par Bearer Token (`API_BEARER_TOKEN`), conforme à `infra/openapi.yml`.
* **Persistance** : chaque trajet créé via `/api/v1/routes` est sauvegardé dans `var/routes-log.json`.
* **Statistiques** : endpoint bonus avec filtre par période (`none`, `day`, `month`, `year`).
* **Frontend complet** : formulaire, timeline, statistiques filtrées, graphique.
* **Docker Compose** : backend & frontend orchestrés, lancement en une commande.
* **Tests** : PHPUnit côté backend, Vitest côté frontend.

---

## Architecture rapide

## Backend (`backend/api`)

* Framework : **Symfony 7**
* Services clés :

  * `RailNetwork` → chargement du graphe ferroviaire
  * `RouteCalculator` → implémentation Dijkstra
  * `AnalyticsService` → agrégation statistique
  * `RouteStorage` → persistance JSON
  * `ApiAuthSubscriber` → validation du Bearer Token

* Endpoints :

  * `POST /api/v1/routes`
  * `GET /api/v1/stats/distances`

* Données réseau montées dans l’image Docker via `backend/data`.

## Frontend (`frontend`)

* Vue 3 + TypeScript + Vuetify 3
* Fonctionnalités :

  * Formulaire de trajet (stations, code analytique)
  * Timeline des stations traversées
  * Bloc statistiques (dates + regroupement)
  * Graphique en barres (Chart.js)

* Appels fetch sécurisés via `Authorization: Bearer dev-secret-token`.

## Infrastructure (`docker-compose.yml`)

* Services :

  * `backend` (PHP 8.4 + Apache, port 8000)
  * `frontend` (Vite DevServer ou build + Nginx, port 5173)

* Proxy `/api` → backend via `frontend/nginx.conf`

---

## Démarrage rapide (Docker)

Prérequis : **Docker 25+**

```bash
docker compose up --build
```

| Service  | URL                                                          |
| -------- | ------------------------------------------------------------ |
| Frontend | [http://localhost:5173](http://localhost:5173)               |
| Backend  | [http://localhost:8000/api/v1](http://localhost:8000/api/v1) |

Token par défaut :
`Authorization: Bearer dev-secret-token`

---

## Exécution locale sans Docker

## Backend

```bash
cd backend/api
composer install
API_BEARER_TOKEN=dev-secret-token php -S 0.0.0.0:8000 -t public
```

ou :

```bash
symfony server:start
```

## Frontend

```bash
cd frontend
npm install
npm run dev -- --host
```

---

## Tests

## Backend (PHPUnit)

```bash
cd backend/api
./vendor/bin/phpunit
```

## Frontend (Vitest)

```bash
cd frontend
npm run test
```

---

## Endpoints principaux

## POST `/api/v1/routes`

Calcule un trajet + distance + chemin + métadonnées.

**Body :**

```json
{
  "fromStationId": "MX",
  "toStationId": "ZW",
  "analyticCode": "ANA-123"
}
```

## GET `/api/v1/stats/distances`

Agrégation statistique avec filtres.

**Exemple :**

```url
/api/v1/stats/distances?from=2025-01-01&to=2025-12-31&groupBy=month
```

**Réponse :**

```json
{
  "from": "2025-01-01",
  "to": "2025-12-31",
  "groupBy": "month",
  "items": [
    { "analyticCode": "ANA-123", "totalDistanceKm": 312.15, "group": "2025-12" }
  ]
}
```

Spéc complète : `infra/openapi.yml`

---

## Hypothèses et choix techniques

* **Pas de base SQL** : fichier JSON pour persister les trajets → suffisant pour le défi.
* **Algorithme Dijkstra** : exécution en mémoire à partir du graphe JSON.
* **Bearer Token simple** : sécurité minimale adaptée au contexte.
* **Données réseau embarquées dans l’image Docker** pour simplifier le déploiement.

---

## Roadmap & axes d’amélioration

* Couleur unique par code analytique dans le graphique
* Ajout d’une légende dynamique pour les graphes
* Augmenter la couverture tests PHPUnit
* CI GitHub Actions (lint, tests, build, coverage)
* Passage à JWT + rotation de clés (prod-like)
* HTTPS via Traefik ou Caddy
* Migrer la persistance vers une vraie base (PostgreSQL)

---

## Conclusion

Cette solution apporte :

* une **architecture claire** et structurée,
* une **API solide**, testée et validée,
* un **frontend moderne** et ergonomique,
* une **visualisation statistique complète**,
* un **routage ferroviaire conforme** via Dijkstra,
* un **démarrage Docker ultra simple**,
* un code maintenable, propre et extensible.

Merci pour ce défi : il m’a donné l’occasion de développer une application complète que je pourrai ajouter à mon répertoire et faire évoluer à l’avenir.
