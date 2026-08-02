# 🙏 Hope Media DRC - Plateforme Officielle de l'Église Adventiste

## 📋 Vue d'ensemble

**Hope Media DRC** est une plateforme web moderne conçue pour diffuser les enseignements bibliques, les vidéos, les audios et les ressources spirituelles de l'Église Adventiste du 7ème Jour (Union du Congo Ouest).

### Objectifs
- 📖 Diffuser des enseignements bibliques (textes, images, vidéos, audios)
- 🙏 Permettre les demandes de prière communautaires
- 💬 Faciliter la communication avec l'église
- 📱 Interface responsive et moderne (Mobile-First)
- ⚡ Performance optimale et SEO-friendly

---

## 🛠 Stack Technique

| Composant | Technologie |
|-----------|-------------|
| **Framework** | Laravel 11 (PHP 8.2+) |
| **Frontend** | Blade + Tailwind CSS |
| **Base de données** | MySQL 8.0+ |
| **Authentification** | Laravel Auth (futur: Filament Admin) |
| **Gestion des slugs** | Eloquent Sluggable |
| **Serveur** | Apache/Nginx |

---

## 🚀 Installation et Configuration

### 1️⃣ Prérequis

```bash
# Vérifions vos versions
php -v          # Doit être 8.2 ou supérieur
composer -v     # Composer
mysql --version # MySQL 8.0+
```

### 2️⃣ Installation du projet

```bash
# 1. Cloner ou ouvrir le projet
cd hope-media-drc

# 2. Installer les dépendances PHP
composer install

# 3. Créer le fichier .env
cp .env.example .env

# 4. Générer la clé application
php artisan key:generate

# 5. Configurer la base de données dans .env
nano .env
```

### 3️⃣ Configuration .env

```env
APP_NAME="Hope Media DRC"
APP_URL=http://localhost

# Base de données
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hope_media_drc
DB_USERNAME=root
DB_PASSWORD=

# Mail (pour notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=hopemediasociaux@gmail.com
```

### 4️⃣ Créer la base de données

```bash
# Créer la base de données MySQL
mysql -u root -p
mysql> CREATE DATABASE hope_media_drc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
mysql> EXIT;

# Exécuter les migrations
php artisan migrate

# Remplir avec les données de test (optionnel)
php artisan db:seed
```

### 5️⃣ Démarrer l'application

```bash
# Serveur de développement
php artisan serve

# L'application sera accessible sur: http://localhost:8000
```

---

## 📁 Structure du Projet

```
hope-media-drc/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── HomeController.php
│   │       ├── PostController.php
│   │       └── ContactController.php
│   ├── Models/
│   │   ├── Category.php
│   │   ├── Post.php
│   │   ├── Media.php
│   │   └── Contact.php
│   └── ...
├── database/
│   └── migrations/
│       ├── *_create_categories_table.php
│       ├── *_create_posts_table.php
│       ├── *_create_medias_table.php
│       └── *_create_contacts_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php (Layout principal)
│       ├── home.blade.php
│       ├── posts/
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   ├── category.blade.php
│       │   ├── by-type.blade.php
│       │   └── search.blade.php
│       └── contact/
│           ├── create.blade.php
│           └── prayer.blade.php
├── routes/
│   └── web.php (Routes publiques)
├── public/
│   └── storage/ (Stockage des fichiers)
└── ...
```

---

## 🗄 Schéma de Base de Données

### Table: `categories`
```sql
CREATE TABLE categories (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) UNIQUE NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description TEXT,
  type ENUM('enseignement', 'service') DEFAULT 'enseignement',
  icon VARCHAR(255),
  color VARCHAR(255),
  order INT DEFAULT 0,
  is_active BOOLEAN DEFAULT TRUE,
  timestamps
);
```

### Table: `posts`
```sql
CREATE TABLE posts (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  category_id BIGINT NOT NULL FOREIGN KEY references categories(id),
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  content LONGTEXT,
  excerpt TEXT,
  content_type ENUM('text', 'video', 'audio', 'image') DEFAULT 'text',
  featured_image VARCHAR(255),
  author VARCHAR(255),
  author_email VARCHAR(255),
  status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
  published_at DATETIME,
  teaching_date DATE,
  meta_title VARCHAR(255),
  meta_description TEXT,
  meta_keywords TEXT,
  views INT DEFAULT 0,
  likes INT DEFAULT 0,
  order INT DEFAULT 0,
  is_featured BOOLEAN DEFAULT FALSE,
  timestamps
);
```

### Table: `medias`
```sql
CREATE TABLE medias (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  post_id BIGINT NOT NULL FOREIGN KEY references posts(id),
  media_type ENUM('local', 'youtube', 'vimeo') DEFAULT 'local',
  title VARCHAR(255),
  description TEXT,
  url VARCHAR(255),
  duration INT,
  file_size BIGINT,
  mime_type VARCHAR(255),
  thumbnail VARCHAR(255),
  order INT DEFAULT 0,
  is_active BOOLEAN DEFAULT TRUE,
  timestamps
);
```

### Table: `contacts`
```sql
CREATE TABLE contacts (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  request_type ENUM('prayer', 'question', 'testimony', 'contact', 'feedback') DEFAULT 'contact',
  subject VARCHAR(255),
  message LONGTEXT,
  attachments JSON,
  status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
  admin_reply LONGTEXT,
  replied_at DATETIME,
  admin_notes TEXT,
  ip_address IPADDRESS,
  accepted_terms BOOLEAN DEFAULT FALSE,
  subscribed_to_newsletter BOOLEAN DEFAULT FALSE,
  timestamps
);
```

---

## 🌐 Routes Publiques

### Posts (Enseignements)
```
GET  /                                    # Accueil
GET  /enseignements                       # Liste de tous les posts
GET  /enseignements/{slug}                # Détail d'un post
GET  /enseignements/categorie/{slug}      # Posts d'une catégorie
GET  /enseignements/type/{type}           # Posts par type (video, audio, text, image)
GET  /recherche?q={query}                 # Résultats de recherche
```

### Contact et Prière
```
GET  /contact                             # Formulaire de contact
POST /contact                             # Soumettre un contact
GET  /contact/priere                      # Page de demande de prière
POST /contact/priere                      # Soumettre une demande de prière
```

---

## 📊 Caractéristiques Principales

### ✨ Frontend
- ✅ **Navigation moderne** avec menu responsive
- ✅ **Section Héros** avec CTA puissants
- ✅ **Grille de cartes** avec icônes par format
- ✅ **Pagination** intégrée
- ✅ **Recherche** full-text
- ✅ **Partage social** (Facebook, Twitter, WhatsApp, Email)
- ✅ **Design Mobile-First** avec Tailwind CSS
- ✅ **SEO optimisé** avec métadonnées

### 📚 Backend
- ✅ **Modèles avec relations** (Category hasMany Posts, Post hasMany Medias)
- ✅ **Slugs automatiques** avec Eloquent Sluggable
- ✅ **Compteurs** (vues, likes)
- ✅ **Filtrage** par type de contenu et catégorie
- ✅ **Validation** côté serveur
- ✅ **Comptage de vues** automatique

### 👤 Gestion du Contenu
- 📖 Textes bibliques
- 🎥 Vidéos (YouTube, Vimeo, fichiers locaux)
- 🎵 Audios (fichiers locaux, streaming)
- 🖼 Images avec galeries
- 🙏 Demandes de prière
- 💬 Messages de contact

---

## 🔧 Administration (À Implémenter)

### Option 1: Filament Panel (Recommandé)
```bash
composer require filament/filament
php artisan filament:install --panel=admin
php artisan make:filament-resource Post --generate
php artisan make:filament-resource Category --generate
```

### Option 2: Admin Manuel
Routes suggérées:
- `GET  /admin/login` - Connexion
- `POST /admin/login` - Authentification
- `GET  /admin/dashboard` - Tableau de bord
- `GET  /admin/posts` - Gestion des posts
- `POST /admin/posts` - Créer un post
- `GET  /admin/categories` - Gestion des catégories
- `GET  /admin/contacts` - Gestion des contacts

---

## 🔐 Sécurité

### Recommandations
- [ ] Activer HTTPS en production
- [ ] Utiliser des variables d'environnement sensibles
- [ ] Valider toutes les entrées utilisateur
- [ ] Implémenter le Rate Limiting
- [ ] Ajouter la protection CSRF (incluse par défaut)
- [ ] Sécuriser les uploads de fichiers
- [ ] Utiliser des autorisations appropriées
- [ ] Mettre en place l'audit des logs

```php
// Exemple: Middleware d'authentification
Route::middleware(['auth:sanctum'])->group(function () {
    // Routes protégées ici
});
```

---

## 📝 Conventions de Code

### PSR-12 & Clean Code
```php
// ✅ BON
class PostController extends Controller
{
    /**
     * Afficher la liste paginée des posts
     */
    public function index()
    {
        $posts = Post::published()->paginate(12);
        return view('posts.index', compact('posts'));
    }
}

// ❌ MAUVAIS
class postcontroller extends Controller {
    public function index(){$posts = Post::published()->paginate(12); return view('posts.index', compact('posts'));}
}
```

### Commentaires
```php
/**
 * Description courte
 * 
 * Description détaillée si nécessaire
 * 
 * @param string $slug
 * @return \Illuminate\View\View
 */
public function show($slug)
{
    // Récupérer le post publié
    $post = Post::findBySlug($slug);
    
    return view('posts.show', compact('post'));
}
```

---

## 🧪 Tests (À Implémenter)

```bash
# Tests unitaires
php artisan test

# Tests avec couverture
php artisan test --coverage
```

---

## 📦 Déploiement Production

### Serveur Recommandé
- **OS:** Ubuntu 20.04 LTS ou supérieur
- **PHP:** 8.2+ avec extensions: pdo, mbstring, gd
- **MySQL:** 8.0+
- **Serveur Web:** Nginx ou Apache

### Checklist Déploiement
```bash
# 1. Cloner le repository
git clone <url-repo> /var/www/hope-media-drc

# 2. Installer les dépendances
cd /var/www/hope-media-drc
composer install --no-dev --optimize-autoloader

# 3. Configuration
cp .env.production .env
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Base de données
php artisan migrate --force

# 5. Optimiser
php artisan optimize

# 6. Permissions
chown -R www-data:www-data /var/www/hope-media-drc
chmod -R 755 /var/www/hope-media-drc
chmod -R 755 /var/www/hope-media-drc/storage
chmod -R 755 /var/www/hope-media-drc/bootstrap/cache

# 7. SSL (Let's Encrypt)
certbot certonly --nginx -d hopemedia.cd
```

---

## 📞 Support et Contributions

Pour toute question ou contribution:
- 📧 Email: hopemediasociaux@gmail.com
- 🐛 Issues: GitHub Issues
- 🔄 Pull Requests: Bienvenues!

---

## 📄 Licence

Copyright © 2024 Église Adventiste du 7ème Jour - Union du Congo Ouest. Tous droits réservés.

---

**Version:** 1.0.0  
**Dernière mise à jour:** Mai 2024  
**Statut:** ✅ En développement actif
