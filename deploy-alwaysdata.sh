#!/bin/bash
# Deploiement Laravel sur alwaysdata - script a executer VIA SSH sur le serveur
# 1er lancement : cree .env depuis .env.example puis demande de l'editer
# Lancements suivants : git pull + install + migrate + build + caches
set -euo pipefail

APP="$HOME/mon-projet"
REPO="https://github.com/ABOUBAKAR6/Mon-projet-Afritex.git"
BRANCH="master"

export PHP_VERSION=8.4
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
    echo ">>> PUIS relance ce script."
    echo "=========================================================="
    exit 0
fi

echo "=== 2/4 Base de donnees ==="
php artisan migrate --force
php artisan storage:link || true

echo "=== 3/4 Build front (Vite) ==="
npm ci --no-audit --no-fund
npm run build
rm -rf node_modules

echo "=== 4/4 Caches ==="
php artisan optimize

echo "DEPLOIEMENT OK - $(date)"
