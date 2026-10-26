# Mettre à jour le site sur le VPS sans perdre de données

Ce qui est protégé :

| Donnée | Où elle se trouve | Protection |
|---|---|---|
| Base de données | `database/database.sqlite` (ou MySQL) | Exclue de Git ; sauvegardée avant chaque mise à jour |
| Images et fichiers envoyés depuis l'admin | `public/images/{posts,news,hero,resources}`, `storage/app/public` | Exclus de Git ; sauvegardés avant chaque mise à jour |
| Configuration (mots de passe, clés API) | `.env` | Exclue de Git ; sauvegardée avant chaque mise à jour |

En plus, en production, Laravel **refuse** `migrate:fresh`, `migrate:refresh`, `migrate:reset` et `db:wipe` (commandes qui vident la base).

---

## Une seule fois, sur le VPS

1. Dans le `.env` du serveur, vérifiez :
   ```
   APP_ENV=production
   APP_DEBUG=false
   ```
   C'est `APP_ENV=production` qui active le blocage des commandes destructrices.

2. Installez `sqlite3` (pour des sauvegardes cohérentes même pendant l'utilisation du site) :
   ```
   sudo apt install sqlite3
   ```

3. Tâches automatiques : `crontab -e`, puis ajoutez ces deux lignes :
   ```
   * * * * * cd /chemin/du/site && php artisan schedule:run >> /dev/null 2>&1
   0 2 * * * cd /chemin/du/site && bash scripts/backup.sh >> storage/logs/backup.log 2>&1
   ```
   - La première fait tourner la **synchronisation automatique des réseaux sociaux** (YouTube, Facebook, Instagram, TikTok, X), à la fréquence choisie par l'admin. Sans elle, seule la synchronisation manuelle (bouton « Synchroniser maintenant ») fonctionne. La page Admin → Réseaux sociaux indique si elle tourne bien.
   - La seconde sauvegarde la base chaque nuit à 2 h.

Les sauvegardes sont rangées dans `~/hope-media-backups` (en dehors du dossier du site). Les 30 plus récentes sont conservées.

4. **Ne changez jamais `APP_KEY`** dans le `.env` du serveur. Les accès aux réseaux sociaux saisis dans l'admin sont chiffrés avec cette clé : si elle change, il faut les ressaisir. (Le `.env` est inclus dans chaque sauvegarde.)

---

## Réseaux sociaux

Les accès (clés d'API, jetons) se saisissent dans **Admin → Réseaux sociaux**, réseau par réseau, avec un guide pour les obtenir. Pour chaque réseau : « Tester la connexion », puis « Synchroniser maintenant », puis cochez « Synchroniser automatiquement ».

Les valeurs du `.env` (`YOUTUBE_API_KEY`, `FACEBOOK_PAGE_ACCESS_TOKEN`…) restent utilisées tant que l'admin n'en saisit pas d'autres.

En ligne de commande : `php artisan social:sync youtube` (un réseau), ou `php artisan social:sync` (tous ceux qui sont dus). Journal : `storage/logs/social-sync.log`.

---

## À chaque mise à jour

Sur votre PC : enregistrez et envoyez vos changements (`git add`, `git commit`, `git push`).
Si vous avez modifié le CSS ou le JavaScript, lancez d'abord `npm run build`.

Puis sur le VPS :
```
cd /chemin/du/site
bash scripts/deploy.sh
```

Le script sauvegarde tout, met le site en maintenance, récupère le code, applique uniquement les **nouvelles** migrations (les données existantes restent), reconstruit les caches et remet le site en ligne. En cas d'erreur, il remet le site en ligne et n'efface rien.

S'il refuse de démarrer parce que « des fichiers du code ont été modifiés directement sur le serveur », c'est une protection : ne modifiez pas le code sur le VPS, faites-le sur votre PC puis envoyez-le avec Git.

---

## Ne jamais lancer sur le VPS

| Commande | Effet |
|---|---|
| `php artisan migrate:fresh` / `migrate:refresh` / `migrate:reset` / `db:wipe` | Vide la base (bloqué en production) |
| `php artisan db:seed` / `migrate --seed` | Peut recréer des données par défaut |
| `git reset --hard` / `git clean -fd` | Peut supprimer les fichiers envoyés |
| Supprimer ou remplacer `database/database.sqlite` | Perte de toutes les données |

---

## Restaurer une sauvegarde

Listez les sauvegardes :
```
ls -lt ~/hope-media-backups
```

Base SQLite (remplacez la date par celle choisie) :
```
cd /chemin/du/site
php artisan down
cp database/database.sqlite database/database.avant-restauration.sqlite
cp ~/hope-media-backups/db-20261026-020000.sqlite database/database.sqlite
php artisan up
```

Base MySQL :
```
gunzip -c ~/hope-media-backups/db-20261026-020000.sql.gz | mysql -u UTILISATEUR -p NOM_DE_LA_BASE
```

Images et fichiers :
```
tar -xzf ~/hope-media-backups/fichiers-20261026-020000.tar.gz -C /chemin/du/site
```

Conseil : copiez de temps en temps le dossier `~/hope-media-backups` hors du VPS (sur votre PC ou un stockage en ligne), au cas où le serveur lui-même serait perdu.
