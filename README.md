# MyClubHub Foundation

The public site now boots through a lightweight PHP front controller instead of the removed Laravel stack. The goal is to stabilise configuration, database connectivity, and health instrumentation before rebuilding any domain modules.

## Database Configuration

`config/database.php` reads environment variables first and falls back to the shared config constants (`shared/config/config.local.php`). Set the following variables in your hosting panel or shell before serving traffic:

- `DB_HOST` – default `localhost`
- `DB_PORT` – default `3306`
- `DB_NAME` – default `mch_os`
- `DB_USER` – default `myclubhub`
- `DB_PASS` – default placeholder `change_this_password`
- `DB_CHARSET` – default `utf8mb4`

## Health Check

Hit `/health` (e.g. `https://www.myclubhub.co.uk/health`) to verify infrastructure. The endpoint reports:

- Whether PDO can connect to the configured database
- The selected schema name
- MariaDB server version (`SELECT VERSION()`)
- Row counts for up to five tables (first five returned by `SHOW TABLES`)
- Any connection/query error message if the check fails

## Schema Files

Versioned SQL dumps live under `database/schema/`:

- `mch_os.sql` – current MyClubHub OS schema
- `lms_v4.sql` – legacy Last Man Standing schema kept for comparison

Import these files to recreate each database locally or in staging environments. Update the dumps whenever the production schema changes so the repo stays in sync.

## Storage Directory

The previous Laravel-era `public/storage` symlink pointed at a non-existent `storage/app/public` tree and caused 404s. The symlink has been removed; create explicit directories under `public/uploads/` or `storage/` when new modules require file storage.

## Next Steps

- Point env vars at the real credentials so `/health` succeeds
- Flesh out routing/controllers once authentication, portals, and POS work begins
- Keep schema dumps and configuration files updated alongside any DB migrations
