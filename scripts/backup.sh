#!/usr/bin/env bash
# =============================================================================
# Sauvegarde Hope Media RDC : base de données + fichiers envoyés + .env
#
#   bash scripts/backup.sh
#
# Les sauvegardes vont HORS du dossier du site (par défaut ~/hope-media-backups),
# pour survivre même si le dossier du site est supprimé ou recloné.
# Variables facultatives :
#   BACKUP_DIR=/chemin/des/sauvegardes   (défaut : ~/hope-media-backups)
#   KEEP=30                              (nombre de sauvegardes conservées)
# =============================================================================
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKUP_DIR="${BACKUP_DIR:-$HOME/hope-media-backups}"
KEEP="${KEEP:-30}"
STAMP="$(date +%Y%m%d-%H%M%S)"

[ -f "$APP_DIR/.env" ] || { echo "ERREUR : fichier .env introuvable dans $APP_DIR" >&2; exit 1; }
mkdir -p "$BACKUP_DIR"
chmod 700 "$BACKUP_DIR"

# Lit une valeur du .env (sans guillemets). Renvoie une chaîne vide si la clé est absente.
env_get() {
    { grep -E "^$1=" "$APP_DIR/.env" || true; } | tail -n 1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//" | tr -d '\r'
}

# Message clair si une étape échoue
trap 'echo "ERREUR : la sauvegarde a échoué (ligne $LINENO). Rien n a été supprimé." >&2' ERR

CONNECTION="$(env_get DB_CONNECTION)"
CONNECTION="${CONNECTION:-sqlite}"

# --- 1. Base de données -------------------------------------------------------
case "$CONNECTION" in
    sqlite)
        DB_FILE="$(env_get DB_DATABASE)"
        DB_FILE="${DB_FILE:-$APP_DIR/database/database.sqlite}"
        [[ "$DB_FILE" == /* ]] || DB_FILE="$APP_DIR/$DB_FILE"
        [ -f "$DB_FILE" ] || { echo "ERREUR : base SQLite introuvable : $DB_FILE" >&2; exit 1; }
        DB_OUT="$BACKUP_DIR/db-$STAMP.sqlite"
        if command -v sqlite3 >/dev/null 2>&1; then
            # Copie cohérente même si le site écrit au même moment
            sqlite3 "$DB_FILE" ".backup '$DB_OUT'"
        else
            cp "$DB_FILE" "$DB_OUT"
        fi
        ;;
    mysql|mariadb)
        DB_OUT="$BACKUP_DIR/db-$STAMP.sql.gz"
        MYSQL_PWD="$(env_get DB_PASSWORD)" mysqldump --single-transaction --routines --no-tablespaces \
            -h "$(env_get DB_HOST)" -P "$(env_get DB_PORT)" -u "$(env_get DB_USERNAME)" "$(env_get DB_DATABASE)" \
            | gzip > "$DB_OUT"
        ;;
    *)
        echo "ERREUR : type de base « $CONNECTION » non géré par ce script." >&2
        exit 1
        ;;
esac

[ -s "$DB_OUT" ] || { echo "ERREUR : la sauvegarde de la base est vide ($DB_OUT)." >&2; rm -f "$DB_OUT"; exit 1; }
chmod 600 "$DB_OUT"

# --- 2. Fichiers envoyés depuis l'admin (images, couvertures, livres) -------
UPLOADS=()
for dir in public/images storage/app/public; do
    [ -d "$APP_DIR/$dir" ] && UPLOADS+=("$dir")
done
if [ ${#UPLOADS[@]} -gt 0 ]; then
    tar -czf "$BACKUP_DIR/fichiers-$STAMP.tar.gz" -C "$APP_DIR" "${UPLOADS[@]}"
    chmod 600 "$BACKUP_DIR/fichiers-$STAMP.tar.gz"
fi

# --- 3. Configuration (.env : mots de passe, clés API) -----------------------
cp "$APP_DIR/.env" "$BACKUP_DIR/env-$STAMP"
chmod 600 "$BACKUP_DIR/env-$STAMP"

# --- 4. Rotation : on garde les $KEEP plus récentes de chaque type -----------
for prefix in db- fichiers- env-; do
    ls -1t "$BACKUP_DIR"/"$prefix"* 2>/dev/null | tail -n +"$((KEEP + 1))" | xargs -r rm -f
done

echo "Sauvegarde terminée :"
echo "  base     : $DB_OUT ($(du -h "$DB_OUT" | cut -f1))"
[ -f "$BACKUP_DIR/fichiers-$STAMP.tar.gz" ] && echo "  fichiers : $BACKUP_DIR/fichiers-$STAMP.tar.gz ($(du -h "$BACKUP_DIR/fichiers-$STAMP.tar.gz" | cut -f1))"
echo "  .env     : $BACKUP_DIR/env-$STAMP"
