#  MOB – Défi Full-Stack : Routage de train & Statistiques  
### Symfony 7 · Vue 3 · Vuetify 3 · TypeScript · Docker · Dijkstra · Chart.js

![Made with Symfony](https://img.shields.io/badge/Symfony-7.0-000000?logo=symfony&style=for-the-badge)
![Vue.js](https://img.shields.io/badge/Vue.js-3-42b883?logo=vuedotjs&style=for-the-badge)
![Vuetify](https://img.shields.io/badge/Vuetify-3-1867c0?logo=vuetify&style=for-the-badge)
![Docker](https://img.shields.io/badge/Docker-25+-2496ED?logo=docker&style=for-the-badge)
![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit-blue?style=for-the-badge)
![Vitest](https://img.shields.io/badge/Tests-Vitest-6E9F18?logo=vitest&style=for-the-badge)


---

#  Table des matières

- [MOB – Défi Full-Stack : Routage de train \& Statistiques](#mob--défi-full-stack--routage-de-train--statistiques)
    - [Symfony 7 · Vue 3 · Vuetify 3 · TypeScript · Docker · Dijkstra · Chart.js](#symfony-7--vue-3--vuetify-3--typescript--docker--dijkstra--chartjs)
- [Table des matières](#table-des-matières)
- [Présentation](#présentation)
- [Vue d’ensemble](#vue-densemble)
    - [Fonctionnalités principales](#fonctionnalités-principales)
- [Architecture](#architecture)
- [Backend – Symfony 7](#backend--symfony-7)
    - [Services principaux](#services-principaux)
    - [Endpoints](#endpoints)
- [Frontend – Vue 3 + Vuetify](#frontend--vue-3--vuetify)
    - [Pages \& composants](#pages--composants)
    - [UI/UX améliorée](#uiux-améliorée)
- [Infrastructure Docker](#infrastructure-docker)
    - [`docker-compose.yml`](#docker-composeyml)
- [Démarrage rapide (Docker)](#démarrage-rapide-docker)
- [Exécution locale sans Docker](#exécution-locale-sans-docker)
    - [Backend](#backend)
    - [Frontend](#frontend)
- [Endpoints API](#endpoints-api)
  - [POST `/api/v1/routes`](#post-apiv1routes)
  - [GET `/api/v1/stats/distances`](#get-apiv1statsdistances)
- [Statistiques et visualisation](#statistiques-et-visualisation)
- [Hypothèses techniques](#hypothèses-techniques)
- [Roadmap (améliorations futures)](#roadmap-améliorations-futures)
    - [Frontend](#frontend-1)
    - [Backend](#backend-1)
    - [DevOps](#devops)
- [Conclusion](#conclusion)

---

#  Présentation

Ce dépôt contient la solution complète au **défi full-stack de MOB**, mettant en œuvre :

- un backend **Symfony 7 / PHP 8.4**,
- un frontend moderne **Vue 3 + Vuetify 3 + TypeScript**,
- un **algorithme Dijkstra** pour le routage ferroviaire,
- une **API REST sécurisée** conforme à l’OpenAPI,
- une interface offrant **statistiques + graphiques**,
- un environnement **Docker** démarrable en une commande.

---

#  Vue d’ensemble

### Fonctionnalités principales

-  Calcul du plus court chemin entre deux stations ferroviaires  
-  Algorithme Dijkstra implémenté en PHP  
-  API sécurisée par Bearer Token (`API_BEARER_TOKEN`)  
-  Persistance des trajets (JSON) pour statistiques  
-  Statistiques par code analytique (none/day/month/year)  
-  Graphique dynamique via Chart.js  
-  Interface ergonomique Vue 3 + Vuetify  
-  Docker Compose pour orchestrer backend / frontend

---

# Architecture

# Backend – Symfony 7

### Services principaux

| Service | Rôle |
|--------|------|
| `RailNetwork` | Charge le graphe ferroviaire depuis JSON |
| `RouteCalculator` | Implémente Dijkstra |
| `RouteStorage` | Persistance des trajets dans `var/routes-log.json` |
| `AnalyticsService` | Calcul des stats groupées |
| `ApiAuthSubscriber` | Authentification Bearer |

### Endpoints

```url
POST /api/v1/routes
GET  /api/v1/stats/distances
````

---

# Frontend – Vue 3 + Vuetify

### Pages & composants

- `Home.vue` → navigation + présentation
- `CalculateRoute.vue` → formulaire + timeline + debug réseau
- `StatsAnalytics.vue` → filtres + calendrier intelligent + graphique

### UI/UX améliorée

- Timeline Vuetify
- Date-picker basé sur le groupBy (day / month / year)
- Normalisation automatique des dates pour l’API
- Chart.js avec couleurs dynamiques par code analytique
- Layout global (Navbar + Footer)
- Application responsive

---

# Infrastructure Docker

### `docker-compose.yml`

- Service `backend` (PHP 8.4 + Apache)
- Service `frontend` (Vite DevServer ou build Nginx)
- Proxy API → backend via `frontend/nginx.conf`

---

# Démarrage rapide (Docker)

Prérequis : **Docker 25+**

```bash
docker compose up --build
````

| Service  | URL                                                          |
| -------- | ------------------------------------------------------------ |
| Frontend | [http://localhost:5173](http://localhost:5173)               |
| Backend  | [http://localhost:8000/api/v1](http://localhost:8000/api/v1) |

Token par défaut :

```bash
Authorization: Bearer dev-secret-token
```

---

# Exécution locale sans Docker

### Backend

```bash
cd backend/api
composer install
API_BEARER_TOKEN=dev-secret-token php -S 0.0.0.0:8000 -t public
```

### Frontend

```bash
cd frontend
npm install
npm run dev -- --host
```

---

# Endpoints API

## POST `/api/v1/routes`

```json
{
  "fromStationId": "MX",
  "toStationId": "ZW",
  "analyticCode": "ANA-123"
}
```

Réponse : distance, chemin, horodatage, debug réseau.

---

## GET `/api/v1/stats/distances`

Exemple :

```url
/api/v1/stats/distances?from=2025-01-01&to=2025-12-31&groupBy=month
```

Réponse :

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

Spécification complète : `infra/openapi.yml`

---

# Statistiques et visualisation

* Filtre par période (from / to)
* Regroupement `none | day | month | year`
* Date-picker intelligent selon période choisie
* Graphique Chart.js avec :

  * couleurs par code analytique
  * labels personnalisés
  * légende dynamique
* Tableau Vuetify en complément des graphes

---

# Hypothèses techniques

* Pas de base SQL → persistance JSON
* Sécurité Bearer simplifiée (défi technique)
* Dijkstra calculé en mémoire
* Données réseau intégrées à l’image Docker
* Typage strict côté frontend
* Architecture orientée services pour la testabilité

---

# Roadmap (améliorations futures)

### Frontend

* Theme light/dark switch
* Animation douce sur les barres du graphique
* Page “À propos”
* Responsive plus “app mobile”

### Backend

* JWT + rotation de clés
* Base PostgreSQL pour historisation enrichie
* Nettoyage / rotation de `routes-log.json`

### DevOps

* GitHub Actions (build, lint, tests, coverage, scan sécurité)
* Publication automatique des images Docker
* HTTPS via Traefik ou Caddy

---

# Conclusion

Cette solution délivre :

* ✔ une **API robuste**, sécurisée et conforme
* ✔ un **frontend moderne**, ergonomique et extensible
* ✔ un **algorithme de routage complet** (Dijkstra)
* ✔ une **visualisation statistique avancée**
* ✔ un **environnement Docker clé-en-main**
* ✔ un code clair, maintenable et structuré

Merci à MOB pour ce défi stimulant — il m’a permis de créer une application complète, que je pourrai enrichir et intégrer à mon portfolio professionnel.