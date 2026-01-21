# TaskManager

Application de gestion de tâches construite avec Symfony 8 et PHP 8.4.

## Fonctionnalités

- **Authentification** : Inscription, connexion, déconnexion avec "Remember Me"
- **Gestion des tâches** : CRUD complet avec statuts, priorités et dates d'échéance
- **Catégories** : Organisation des tâches par catégories personnalisées avec couleurs
- **Filtres** : Recherche et filtrage par statut, catégorie
- **Profil utilisateur** : Modification du profil et avatar (VichUploader)
- **Administration** : Gestion des utilisateurs (ROLE_ADMIN uniquement)
- **Sécurité** : Voters pour l'autorisation, protection CSRF

## Stack technique

- **Framework** : Symfony 8.0
- **PHP** : 8.4+
- **Base de données** : PostgreSQL (Neon)
- **Tests** : PHPUnit (97 tests, 204 assertions)
- **CSS** : Tailwind CSS (CDN)
- **Upload** : VichUploaderBundle

## Installation locale

### Prérequis

- PHP 8.4+
- Composer
- Extension PDO PostgreSQL (`pdo_pgsql`)

### Installation

```bash
# Cloner le repository
git clone https://github.com/votre-username/TaskManager.git
cd TaskManager

# Installer les dépendances
composer install

# Configurer l'environnement
cp .env .env.local
# Éditer .env.local avec vos paramètres de base de données

# Créer la base de données et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Charger les données de test (optionnel)
php bin/console doctrine:fixtures:load

# Lancer le serveur de développement
php -S localhost:8000 -t public
```

### Utilisateurs de test

Après avoir chargé les fixtures :

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| `test@example.com` | `password123` | Utilisateur |
| `admin@example.com` | `admin123` | Administrateur |

## Tests

```bash
# Exécuter tous les tests
php bin/phpunit

# Avec détails
php bin/phpunit --testdox
```

## Déploiement sur Render.com

### Configuration automatique

1. Connecter le repository GitHub à Render.com
2. Render détectera automatiquement le fichier `render.yaml`
3. Configurer la variable d'environnement `DATABASE_URL` avec votre connexion Neon

### Variables d'environnement requises

| Variable | Description |
|----------|-------------|
| `DATABASE_URL` | URL de connexion PostgreSQL (Neon) |
| `APP_SECRET` | Généré automatiquement |
| `APP_ENV` | `prod` |
| `APP_DEBUG` | `0` |

### Structure du déploiement

```
Dockerfile          # Image PHP 8.4 + Nginx
docker/
  nginx.conf        # Configuration Nginx
  php.ini           # Configuration PHP production
  supervisord.conf  # Gestion des processus
render.yaml         # Blueprint Render.com
```

## Architecture

```
src/
├── Controller/       # Contrôleurs (Task, Category, Profile, Admin, Security)
├── Entity/           # Entités Doctrine (User, Task, Category)
├── Enum/             # Enums PHP 8 (TaskStatus, TaskPriority)
├── Form/             # Types de formulaires
├── Repository/       # Repositories Doctrine
├── Security/
│   └── Voter/        # Voters d'autorisation
└── DataFixtures/     # Fixtures de données

tests/
├── Unit/             # Tests unitaires (Entity, Enum)
├── Functional/       # Tests fonctionnels (Controller)
└── Integration/      # Tests d'intégration (Repository, Voter)
```

## Licence

MIT
