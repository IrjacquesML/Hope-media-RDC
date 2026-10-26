#!/usr/bin/env bash
# =============================================================================
# Mise à jour de Hope Media RDC sur le VPS, SANS perte de données.
#
#   cd /chemin/du/site && bash scripts/deploy.sh
#
# Ce que fait le script, dans l'ordre :
#   1. vérifie qu'aucun fichier du code n'a été modifié à la main sur le serveur
#   2. sauvegarde la base, les fichiers envoyés et le .env (scripts/backup.sh)
#   3. met le site en maintenance
#   4. récupère le nouveau code (git pull --ff-only : jamais de reset ni de clean)
#   5. installe les dépendances PHP
#   6. applique UNIQUEMENT les nouvelles migrations (migrate --force)
#   7. vide et reconstruit les caches, puis remet le site en ligne
#
# Il ne lance jamais migrate:fresh / migrate:refresh / db:seed / git reset / git clean.
# En cas d'erreur, le site est remis en ligne et la sauvegarde reste disponible.
# =============================================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"
export BACKUP_DIR="${BACKUP_DIR:-$HOME/hope-media-backups}"

echo "== 1/7 Vérifications"
[ -f .env ] || { echo "ERREUR : .env introuvable. Mise à jour annulée, rien n'a été modifié." >&2; exit 1; }
if ! grep -qE '^APP_ENV=production' .env; then
    echo "ATTENTION : APP_ENV n'est pas « production » dans .env."
    echo "            Les commandes qui vident la base ne sont bloquées qu'en production."
fi
if [ -n "$(git status --porcelain --untracked-files=no)" ]; then
    echo "ERREUR : des fichiers du code ont été modifiés directement sur le serveur :" >&2
    git status --short --untracked-files=no >&2
    echo "Mise à jour annulée, rien n'a été modifié. Enregistrez ces changements dans Git, ou annulez-les avec" >&2
    echo "« git checkout -- <fichier> » (cela ne touche pas à la base de données)." >&2
    exit 1
fi

echo "== 2/7 Sauvegarde"
bash scripts/backup.sh

echo "== 3/7 Mise en maintenance"
php artisan down --retry=30 || true
trap 'echo ""; echo "ÉCHEC de la mise à jour. Le site est remis en ligne ; la base n est pas effacée."; echo "Sauvegardes : $BACKUP_DIR"; php artisan up || true' ERR

echo "== 4/7 Récupération du code"
git pull --ff-only

echo "== 5/7 Dépendances PHP"
composer install --no-dev --optimize-autoloader --no-interaction

echo "== 6/7 Nouvelles migrations (les données existantes sont conservées)"
php artisan migrate --force

echo "== 7/7 Caches et remise en ligne"
php artisan storage:link >/dev/null 2>&1 || true
php artisan optimize:clear
php artisan optimize
php artisan up

trap - ERR
echo ""
echo "Mise à jour terminée. Sauvegarde de sécurité dans : $BACKUP_DIR"
