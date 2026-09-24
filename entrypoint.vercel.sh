#!/bin/sh

echo "[entrypoint] env DB_PORT=${DB_PORT:-unset} DB_CONNECTION=${DB_CONNECTION:-unset} DB_URL_set=$([ -n "${DB_URL:-}" ] && echo yes || echo no) DB_POSTGRES_URL_set=$([ -n "${DB_POSTGRES_URL:-}" ] && echo yes || echo no)" >&2
echo "[entrypoint] .env_exists=$([ -f /app/.env ] && echo yes || echo no)" >&2

# Neon: pakai non-pooling endpoint untuk migrasi/seed (pooler/pgbouncer gagal untuk DDL)
if [ -n "${DB_POSTGRES_URL_NON_POOLING:-}" ]; then
    export DB_URL="$DB_POSTGRES_URL_NON_POOLING"
    echo "[entrypoint] using non-pooling URL for migrate" >&2
fi

# Buang .env lokal yang mungkin bocor ke image agar tidak override env Vercel
if [ -f /app/.env ]; then
    echo "[entrypoint] removing /app/.env to avoid local override" >&2
    rm -f /app/.env
fi

(
    echo "[entrypoint] migrate status start" >&2
    php artisan migrate:status 2>&1 | tail -n 30 >&2
    echo "[entrypoint] migrate start" >&2
    php artisan migrate --force
    echo "[entrypoint] migrate exit=$?" >&2
    echo "[entrypoint] seed start" >&2
    php artisan db:seed --class=ProductionSeeder --force
    echo "[entrypoint] seed exit=$?" >&2
) &

echo "[entrypoint] starting frankenphp PORT=${PORT:-80}" >&2
exec env PORT="${PORT:-80}" frankenphp run --config /etc/frankenphp/Caddyfile
