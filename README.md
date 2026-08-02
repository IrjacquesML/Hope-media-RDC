# Hope Media RDC

Plateforme médiatique de l'Église Adventiste du 7ème Jour — Union du Congo Ouest.

## Fonctionnalités

- Page d'accueil (articles admin + vidéos réseaux sociaux)
- Enseignements, Écho de la mission, Ressources
- Administration complète (posts, actualités, catégories, contacts, sync YouTube/Facebook/Instagram/TikTok)
- Demandes de prière et newsletter

## Prérequis

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (par défaut) ou MySQL

## Installation

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Site : http://127.0.0.1:8000  
Admin : http://127.0.0.1:8000/auth/login

Compte seed (après `db:seed`) : voir le seeder (`hopemediasociaux@gmail.com`).

## Synchronisation YouTube

Configurer dans `.env` :

```env
YOUTUBE_API_KEY=votre_cle
YOUTUBE_CHANNEL_ID=@votre-handle
```

Puis :

```bash
php artisan youtube:sync --now
```

Ou depuis Admin → Sync Réseaux.
