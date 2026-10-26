# 🚀 Quick Start Guide - Hope Media DRC

> ⚠️ **Serveur de production (VPS)** : ce guide concerne l'installation sur un poste de développement.
> Sur le VPS, ne lancez jamais `migrate:refresh`, `migrate:fresh` ni `db:seed` : ces commandes vident ou modifient la base.
> Pour mettre à jour le site en ligne sans perdre de données, suivez [DEPLOIEMENT.md](DEPLOIEMENT.md) (`bash scripts/deploy.sh`).

## ⚡ Démarrage Rapide (5 minutes)

### 1. Préparation de l'environnement

```bash
# Naviguer dans le répertoire du projet
cd g:\self\hope\ media\ drc

# Copier le fichier .env
copy .env.example .env

# Ou si vous êtes sur Linux/Mac
cp .env.example .env
```

### 2. Configuration de la base de données

```bash
# Créer la base de données MySQL
mysql -u root -p
# Puis dans le terminal MySQL:
CREATE DATABASE hope_media_drc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Ou utiliser phpMyAdmin si vous préférez
```

### 3. Configuration du fichier .env

Éditez `g:\self\hope media drc\.env` et configurez:

```env
# Application
APP_NAME="Hope Media DRC"
APP_URL=http://localhost:8000

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hope_media_drc
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Installation et initialisation

```bash
# Installer les dépendances PHP
composer install

# Générer la clé application
php artisan key:generate

# Exécuter les migrations
php artisan migrate

# Charger les données de test
php artisan db:seed
```

### 5. Lancer l'application

```bash
# Démarrer le serveur de développement
php artisan serve

# L'application sera accessible sur:
# 🌐 http://localhost:8000
```

---

## 📍 Accès aux Pages

| Page | URL | Description |
|------|-----|-------------|
| 🏠 Accueil | http://localhost:8000 | Page d'accueil avec catégories |
| 📖 Enseignements | http://localhost:8000/enseignements | Liste de tous les posts |
| 🎥 Vidéos | http://localhost:8000/enseignements/type/video | Filtre par vidéos |
| 🎵 Audios | http://localhost:8000/enseignements/type/audio | Filtre par audios |
| 📚 Lectures | http://localhost:8000/enseignements/type/text | Filtre par textes |
| 🖼 Images | http://localhost:8000/enseignements/type/image | Filtre par images |
| 💬 Contact | http://localhost:8000/contact | Formulaire de contact |
| 🙏 Prière | http://localhost:8000/contact/priere | Demandes de prière |

---

## 🗄 Gestion de la Base de Données

### Voir les données dans Tinker (Console interactive)

```bash
# Lancer la console interactive
php artisan tinker

# Exemples de commandes:
>>> App\Models\Post::all()
>>> App\Models\Category::with('posts')->get()
>>> App\Models\Contact::where('status', 'new')->get()
```

### Réinitialiser la base de données

```bash
# Supprimer toutes les données et refaire les migrations
php artisan migrate:refresh

# Ou avec les seeders
php artisan migrate:refresh --seed
```

---

## 📊 Visualiser les Données

### Filament Admin (À ajouter)

```bash
# Installation de Filament
composer require filament/filament

# Générer les ressources
php artisan make:filament-resource Post
php artisan make:filament-resource Category

# Accès: /admin/login
```

---

## 🔍 Dépannage

### ❌ Erreur: SQLSTATE[HY000]: General error

```bash
# Solution: Réinitialiser la base
php artisan migrate:refresh --seed
```

### ❌ Erreur: Class 'Cviebrock\EloquentSluggable\Sluggable' not found

```bash
# Solution: Réinstaller les dépendances
composer install
composer dump-autoload
```

### ❌ Erreur: The base path does not exist

```bash
# Solution: Vérifier le chemin du projet
cd /chemin/correct/hope-media-drc
```

### ❌ Port 8000 déjà utilisé

```bash
# Solution: Utiliser un autre port
php artisan serve --port=8001

# Ou arrêter le processus existant
# Windows: netstat -ano | findstr :8000
# Linux/Mac: lsof -i :8000
```

---

## 🎨 Structure des Fichiers Clés

```
app/
├── Models/
│   ├── Category.php ✅ Catégories
│   ├── Post.php ✅ Enseignements
│   ├── Media.php ✅ Fichiers multimédia
│   └── Contact.php ✅ Demandes de contact

database/
├── migrations/
│   ├── *_create_categories_table.php ✅
│   ├── *_create_posts_table.php ✅
│   ├── *_create_medias_table.php ✅
│   └── *_create_contacts_table.php ✅
└── seeders/
    └── DatabaseSeeder.php ✅ Données de test

resources/views/
├── layouts/
│   └── app.blade.php ✅ Layout principal
├── home.blade.php ✅ Accueil
├── posts/
│   ├── index.blade.php ✅ Liste
│   ├── show.blade.php ✅ Détail
│   ├── category.blade.php ✅ Par catégorie
│   ├── by-type.blade.php ✅ Par type
│   └── search.blade.php ✅ Recherche
└── contact/
    ├── create.blade.php ✅ Formulaire
    └── prayer.blade.php ✅ Prière

routes/
└── web.php ✅ Routes publiques
```

---

## 📝 Commandes Utiles

```bash
# 🔧 Maintenance
php artisan optimize              # Optimiser l'app
php artisan config:cache          # Cacher la config
php artisan route:cache           # Cacher les routes

# 🧹 Nettoyage
php artisan cache:clear           # Vider le cache
php artisan view:clear            # Vider le cache des vues
php artisan config:clear          # Vider le cache de config

# 🎯 Tinker (Console interactive)
php artisan tinker                # Lancer Tinker

# 📊 Base de données
php artisan migrate               # Exécuter les migrations
php artisan migrate:rollback      # Annuler les migrations
php artisan migrate:refresh       # Refaire les migrations
php artisan db:seed               # Charger les seeders
php artisan migrate:refresh --seed # Refaire + seeder

# ✉️ Queue (pour emails)
php artisan queue:work            # Traiter les jobs
```

---

## 🚀 Prochaines Étapes

### ✅ Déjà Fait
- ✅ Structure Laravel 11
- ✅ Migrations de base de données
- ✅ Modèles avec relations
- ✅ Contrôleurs publics
- ✅ Templates Blade avec Tailwind
- ✅ Routes publiques
- ✅ Données de test

### 📋 À Faire
- 🔲 Admin panel (Filament)
- 🔲 Authentification (Login/Logout)
- 🔲 Upload de fichiers
- 🔲 Email notifications
- 🔲 API REST
- 🔲 Tests unitaires
- 🔲 Déploiement production

---

## 💡 Conseils

1. **Développement:** Utilisez `php artisan serve` pour le développement local
2. **Données:** Utilisez `php artisan tinker` pour tester les requêtes
3. **Debugging:** Activez `APP_DEBUG=true` dans .env
4. **Git:** Créez un `.gitignore` approprié (déjà inclus)
5. **Variables:** Ne commitez jamais votre `.env`

---

## 📞 Ressources

- 📘 [Documentation Laravel](https://laravel.com/docs)
- 🎨 [Tailwind CSS Docs](https://tailwindcss.com/docs)
- 🛡️ [Security Best Practices](https://laravel.com/docs/11.x/security)
- 🧪 [Laravel Testing](https://laravel.com/docs/11.x/testing)

---

## 🆘 Besoin d'Aide?

Si vous rencontrez des problèmes:

1. Vérifiez que PHP 8.2+ est installé: `php -v`
2. Assurez-vous que MySQL fonctionne
3. Lisez le fichier SETUP_GUIDE.md pour plus de détails
4. Consultez les logs: `storage/logs/laravel.log`

---

**Bonne chance et merci d'utiliser Hope Media DRC! 🙏**
