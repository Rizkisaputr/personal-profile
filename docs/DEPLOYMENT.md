# Production deployment with Docker Compose

This deployment runs Laravel in one application container using PHP 8.3 and FrankenPHP. Public HTTPS terminates at Nginx on the VPS host. Nginx proxies internally over HTTP to `http://127.0.0.1:${APP_PORT:-8081}` (port `8081` by default), which Compose maps to container HTTP port `80`.

FrankenPHP/Caddy internal automatic HTTPS is intentionally disabled because TLS is handled by host Nginx. Compose fixes `SERVER_NAME=:80`, and the Caddyfile uses the explicit `:80` HTTP site address with `auto_https off`. The localhost-only binding means container HTTP is not exposed publicly. Set `APP_PORT=8081` in the server `.env`. The host Nginx configuration is intentionally outside this repository.

The Compose stack does not include a database, queue worker, or scheduler. Production uses the external TiDB Cloud database configured through the server `.env`. `DB_HOST` must be the TiDB Cloud endpoint reachable from inside Docker; never commit that endpoint or any credential.

Use these non-secret production settings alongside the server-only credentials:

```dotenv
APP_PORT=8081
TZ=Asia/Jakarta
DB_CONNECTION=mysql
DB_PORT=4000
MYSQL_ATTR_SSL_CA=/etc/ssl/certs/ca-certificates.crt
```

`MYSQL_ATTR_SSL_CA` is optional for local Laragon development. Leaving it empty prevents Laravel from adding the PDO SSL CA option.

## First deployment

Run these commands from `/opt/apps/personal-profile`:

```bash
git clone <repository-url> /opt/apps/personal-profile
cd /opt/apps/personal-profile
cp .env.example .env
chmod 600 .env
```

Edit `.env` and set production values, including `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `LOG_CHANNEL=stderr`, mail settings, and the external database connection. Do not commit this file.

Generate an application key without writing it into the image, then paste the printed value into `APP_KEY` in the host `.env`:

```bash
docker compose build
docker compose run --rm app php artisan key:generate --show
```

Start and initialize the application explicitly:

```bash
docker compose up -d
docker compose ps
docker compose exec app php artisan migrate --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan optimize
curl --fail http://127.0.0.1:8081/up
```

Only after the local health check succeeds should host Nginx proxy the domain to `http://127.0.0.1:8081`.

## Updates

```bash
cd /opt/apps/personal-profile
git pull
docker compose build
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize
docker compose ps
curl --fail http://127.0.0.1:8081/up
```

Never use `migrate:fresh`, `db:wipe`, or production dummy seeders during deployment.

## Operations

```bash
docker compose logs -f app
docker compose exec app php artisan about
docker compose exec app php artisan optimize:clear
docker compose down
```

`docker compose down` preserves the named `personal-profile-storage` volume. Do not use `docker compose down -v` in production because it deletes persisted public uploads and private documents.

## Persistent storage and backups

The `personal-profile-storage` named volume contains the complete Laravel `storage` directory:

- public profile, hero, login, project, certificate, settings, and CV uploads;
- private documents under `storage/app/private`, which are not under the web root;
- framework runtime files and logs.

The image includes the `public/storage` symlink. Running `php artisan storage:link` after first startup is safe and verifies the link. Back up both the external database and the `app-storage` volume before significant updates.

Laravel logs default to `stderr` in the production image and are available through `docker compose logs`. Secrets remain only in the host `.env`; they are not copied into the image.

## Rebuilding from scratch

```bash
docker compose up -d --build
```

Migrations and optimization remain explicit deployment steps and are never run automatically by the image.
