# TaskManager - Symfony 6.4 LTS

Application de gestion de tâches complète développée avec **Symfony 6.4 LTS**, déployée sur **Render.com** avec une base de données **Neon.tech PostgreSQL**.

## Fonctionnalités

- **Authentification** : Inscription, connexion, remember me, protection brute force
- **Gestion des tâches** : CRUD complet avec statuts, priorités, dates d'échéance
- **Catégories** : Organisation des tâches par catégories colorées
- **API REST** : Endpoints JSON pour intégration externe
- **Sécurité** : Voters pour les autorisations, CSRF, rate limiting
- **Tests** : PHPUnit avec tests unitaires et fonctionnels

## Technologies

| Composant | Technologie |
|-----------|-------------|
| Framework | Symfony 6.4 LTS |
| PHP | 8.2+ |
| Base de données | PostgreSQL 16 (Neon.tech) |
| ORM | Doctrine |
| Templates | Twig + Bootstrap 5 |
| Tests | PHPUnit + Foundry |
| CI/CD | GitHub Actions |
| Hébergement | Render.com |

## Installation locale

### Prérequis

- Docker & Docker Compose
- Git

### Démarrage rapide

```bash
# Cloner le projet
git clone https://github.com/votre-user/TaskManager.git
cd TaskManager

# Démarrer les containers Docker
docker-compose up -d

# Installer les dépendances (dans le container)
docker-compose exec php composer install

# Créer la base de données et exécuter les migrations
docker-compose exec php php bin/console doctrine:database:create --if-not-exists
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction

# Charger les fixtures de démo
docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

### Accès

| Service | URL |
|---------|-----|
| Application | http://localhost:8080 |
| Adminer (BDD) | http://localhost:8081 |
| Mailpit (Emails) | http://localhost:8025 |

### Comptes de démo

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| demo@taskmanager.com | demo1234 | Utilisateur |
| admin@taskmanager.com | admin1234 | Admin |

## Configuration Neon.tech

### 1. Créer un compte et un projet

1. Aller sur [https://console.neon.tech](https://console.neon.tech)
2. Se connecter avec GitHub
3. Créer un nouveau projet (ex: `taskmanager`)
4. Copier la connection string

### 2. Configurer la connexion

La connection string Neon.tech ressemble à :
```
postgresql://username:password@ep-xxx-xxx-123456.eu-central-1.aws.neon.tech/taskmanager?sslmode=require
```

**Important** : Neon.tech utilise SSL, donc `sslmode=require` est obligatoire.

### 3. Variables d'environnement Render

Dans le dashboard Render, ajoutez :

```
DATABASE_URL=postgresql://username:password@ep-xxx.eu-central-1.aws.neon.tech/taskmanager?sslmode=require
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<généré automatiquement>
```

## Déploiement sur Render.com

### 1. Connecter le repository GitHub

1. Aller sur [https://dashboard.render.com](https://dashboard.render.com)
2. Cliquer sur "New" → "Web Service"
3. Connecter votre repository GitHub
4. Sélectionner le repository `TaskManager`

### 2. Configuration du service

| Paramètre | Valeur |
|-----------|--------|
| Name | taskmanager |
| Region | Frankfurt (EU) |
| Branch | main |
| Runtime | Docker |
| Dockerfile Path | ./Dockerfile |
| Instance Type | Free |

### 3. Variables d'environnement

Ajouter les variables suivantes dans Render :

```
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<cliquer Generate>
DATABASE_URL=<votre connection string Neon.tech>
```

### 4. Déploiement automatique

Le déploiement se fait automatiquement à chaque push sur `main` grâce à :
- GitHub Actions qui exécute les tests
- Render qui redéploie après succès des tests

### Configuration GitHub Secrets

Pour le déploiement automatique via GitHub Actions, ajoutez ces secrets dans votre repository :

- `RENDER_API_KEY` : Votre clé API Render (Dashboard → Account → API Keys)
- `RENDER_SERVICE_ID` : L'ID de votre service Render (visible dans l'URL du service)

## Tests

```bash
# Exécuter tous les tests
docker-compose exec php php bin/phpunit

# Avec couverture de code
docker-compose exec php php bin/phpunit --coverage-html coverage

# Analyse statique
docker-compose exec php vendor/bin/phpstan analyse src --level=6
```

## Structure du projet

```
TaskManager/
├── config/                 # Configuration Symfony
│   ├── packages/           # Configuration des bundles
│   └── routes/             # Routes
├── docker/                 # Configuration Docker
│   ├── nginx/              # Configuration Nginx
│   ├── php/                # Configuration PHP
│   └── supervisor/         # Configuration Supervisor
├── migrations/             # Migrations Doctrine
├── public/                 # Document root (index.php)
├── src/
│   ├── Controller/         # Controllers (Web + API)
│   ├── DataFixtures/       # Fixtures de données
│   ├── Entity/             # Entités Doctrine
│   ├── Form/               # Types de formulaires
│   ├── Repository/         # Repositories Doctrine
│   ├── Security/           # Voters, Authenticators
│   └── Service/            # Services métier
├── templates/              # Templates Twig
├── tests/                  # Tests PHPUnit
│   ├── Controller/         # Tests fonctionnels
│   ├── Entity/             # Tests unitaires
│   └── Security/           # Tests de sécurité
├── docker-compose.yml      # Config Docker Compose local
├── Dockerfile              # Dockerfile production
└── render.yaml             # Config Render.com
```

## Commandes utiles

```bash
# Créer une migration
docker-compose exec php php bin/console make:migration

# Exécuter les migrations
docker-compose exec php php bin/console doctrine:migrations:migrate

# Vider le cache
docker-compose exec php php bin/console cache:clear

# Debug des routes
docker-compose exec php php bin/console debug:router

# Debug des services
docker-compose exec php php bin/console debug:container

# Créer un utilisateur via console
docker-compose exec php php bin/console security:hash-password
```

## API REST

L'API REST est disponible sous `/api/v1/` :

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/v1/tasks` | GET | Liste des tâches |
| `/api/v1/tasks` | POST | Créer une tâche |
| `/api/v1/tasks/{id}` | GET | Détail d'une tâche |
| `/api/v1/tasks/{id}` | PUT/PATCH | Modifier une tâche |
| `/api/v1/tasks/{id}` | DELETE | Supprimer une tâche |
| `/api/v1/tasks/{id}/status` | PATCH | Changer le statut |
| `/api/v1/tasks/stats` | GET | Statistiques |

## Licence

MIT License - Voir [LICENSE](LICENSE)

## Ressources

- [Documentation Symfony 6.4](https://symfony.com/doc/6.4/index.html)
- [Neon.tech Documentation](https://neon.tech/docs)
- [Render.com Documentation](https://render.com/docs)
