#!/bin/bash
# Deploiement Laravel sur alwaysdata - script a executer VIA SSH sur le serveur
# 1er lancement : cree .env depuis .env.example puis demande de l'editer
# Lancements suivants : git pull + install + migrate + build + caches
#
# Un meme code, plusieurs boutiques : chaque boutique a son dossier (et son .env, sa base).
#   ./deploy-alwaysdata.sh                 -> boutique du dossier ~/mon-projet
#   ./deploy-alwaysdata.sh boutique-kone   -> autre commercant, dossier ~/boutique-kone
set -euo pipefail

APP="$HOME/${1:-mon-projet}"
REPO="https://github.com/ABOUBAKAR6/Mon-projet-Afritex.git"
BRANCH="${BRANCH:-master}"

export NODEJS_VERSION=22

if [ ! -d "$APP/.git" ]; then
    git clone --branch "$BRANCH" "$REPO" "$APP"
fi
cd "$APP"

git fetch origin
git reset --hard "origin/$BRANCH"

echo "=== 1/4 Composer ==="
composer2 install --no-dev --optimize-autoloader --no-interaction

if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate --force
    echo "=========================================================="
    echo ">>> EDITE maintenant $APP/.env :"
    echo "    - APP_ENV=production, APP_DEBUG=false"
    echo "    - APP_URL=https://TON_COMPTE.alwaysdata.net"
    echo "    - DB_DATABASE / DB_USERNAME / DB_PASSWORD (MySQL alwaysdata)"
    echo "    - cles Stripe / Fedapay / mail"
    echo "    - APP_NAME, BOUTIQUE_NOM, BOUTIQUE_SLOGAN, BOUTIQUE_EMAIL_CONTACT, couleurs BOUTIQUE_COULEUR_*"
    echo "    - WHATSAPP_NUMERO, et une ESPACE_CLE propre a cette boutique (acces admin secret)"
    echo ">>> PUIS relance ce script."
    echo "=========================================================="
    exit 0
fi

mkdir -p storage/framework/{cache/data,views,sessions,testing} storage/logs storage/app/public

grep -q '^APP_KEY=' .env || echo "APP_KEY=" >> .env
# Cle generee UNE seule fois : la regenerer a chaque deploiement deconnectait tout le monde
# et rendait illisibles les donnees chiffrees avec l'ancienne cle.
if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

echo "=== 2/4 Base de donnees ==="
php artisan migrate --force
php artisan storage:link || true

echo "=== 3/4 Build front (Vite) ==="
npm ci --no-audit --no-fund
# Sur alwaysdata, npm ne crée pas node_modules/.bin : « npm run build » répond « vite: not found ».
node node_modules/vite/bin/vite.js build
rm -rf node_modules

echo "=== 4/4 Caches ==="
php artisan config:cache
php artisan event:cache
php artisan route:cache
php artisan view:cache || true

echo "DEPLOIEMENT OK - $(date)"
echo "Premier deploiement d'une boutique ? Une seule fois :"
echo "  php artisan db:seed --class=UserSeeder --force            (compte admin, SEED_ADMIN_* du .env)"
echo "  php artisan db:seed --class=CatalogueDepartSeeder --force  (catalogue et photos de depart)"
