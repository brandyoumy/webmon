# Webmon Coolify Deployment

## Deployment Shape

- App type: Dockerfile
- Web container port: `80`
- Database: Coolify MySQL/MariaDB resource
- Scheduler: Coolify scheduled task running every minute
- Persistent app data: database is the important persistent layer

## Before Deployment

1. Confirm the target Coolify project and environment.
2. Confirm the production domain, for example `monitor.example.com`.
3. Create or confirm a MySQL/MariaDB database in Coolify.
4. If replacing an existing Webmon install, back up the existing database first.
5. Generate a stable Laravel app key and save it in Coolify as `APP_KEY`.

Generate the key locally from this project:

```bash
php artisan key:generate --show
```

## Coolify App Settings

Use these values when creating the application:

- Build Pack: Dockerfile
- Dockerfile: `Dockerfile`
- Port: `80`
- Domain: your production domain
- HTTPS: enabled after DNS points to the VPS

## Required Environment Variables

Set these in Coolify. Replace placeholder values with your real production values.

```env
APP_NAME=Webmon
APP_ENV=production
APP_KEY=base64:REPLACE_WITH_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://REPLACE_WITH_DOMAIN
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

LOG_CHANNEL=stderr
RUN_MIGRATIONS=true
RUN_OPTIMIZE=true

DB_CONNECTION=mysql
DB_HOST=REPLACE_WITH_COOLIFY_DATABASE_HOST
DB_PORT=3306
DB_DATABASE=REPLACE_WITH_DATABASE_NAME
DB_USERNAME=REPLACE_WITH_DATABASE_USER
DB_PASSWORD=REPLACE_WITH_DATABASE_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=REPLACE_WITH_SMTP_HOST
MAIL_PORT=587
MAIL_USERNAME=REPLACE_WITH_SMTP_USERNAME
MAIL_PASSWORD=REPLACE_WITH_SMTP_PASSWORD
MAIL_FROM_ADDRESS=REPLACE_WITH_FROM_EMAIL
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

After the first successful deployment, you may set `RUN_MIGRATIONS=false` if you prefer migrations to be run manually during future updates.

## Scheduler

Create a Coolify scheduled task for the Webmon application:

- Schedule: every minute
- Command:

```bash
php artisan schedule:run
```

This triggers the application scheduler. The actual monitoring interval is controlled inside Webmon settings.

## First Login

If you deploy with a fresh empty database, create the first admin user once after migrations are complete.

The existing seeder creates an admin user and demo websites, so only run it if demo data is acceptable:

```bash
php artisan db:seed --force
```

If you restore an existing Webmon database, do not run the seeder.

## Verification Checklist

1. Coolify build completes successfully.
2. Container starts without `APP_KEY` or database connection errors.
3. The domain opens the Webmon login page.
4. Login works.
5. `php artisan schedule:run` completes from the Coolify scheduled task.
6. A monitored website check creates a new uptime log.
7. Email alert delivery works with the configured SMTP mailbox.

## Rollback

- If the new deployment fails before database migration, redeploy the previous version.
- If database migration has already run, restore the database backup before rolling back code.
- Do not delete the previous app/database resource until the new deployment is verified.
