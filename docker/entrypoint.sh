#!/usr/bin/env sh

set -eu

if [ ! -f .env ]; then
    cp .env.example .env
fi

current_lock_hash="$(sha256sum composer.lock | cut -d ' ' -f 1)"
installed_lock_hash="$(cat vendor/.composer-lock-hash 2>/dev/null || true)"

if [ ! -f vendor/autoload.php ] || [ "$current_lock_hash" != "$installed_lock_hash" ]; then
    composer install --no-interaction --no-progress --prefer-dist
    printf '%s\n' "$current_lock_hash" > vendor/.composer-lock-hash
fi

if ! grep -Eq '^APP_KEY=base64:.+' .env; then
    php artisan key:generate --force
fi

php artisan migrate --force

exec "$@"
