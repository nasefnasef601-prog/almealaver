#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")"

echo "== Almeaa Laravel Hostinger setup =="

if command -v composer2 >/dev/null 2>&1; then
  COMPOSER_BIN="composer2"
elif command -v composer >/dev/null 2>&1; then
  COMPOSER_BIN="composer"
else
  echo "Composer was not found. Enable Composer in Hostinger or run this from SSH." >&2
  exit 1
fi

echo "Installing PHP dependencies..."
"$COMPOSER_BIN" install --no-dev --optimize-autoloader

if [ ! -f .env ]; then
  cp .env.example .env
fi

read -r -s -p "Enter DB password for u163825415_nasef: " DB_PASSWORD
echo
export ALMEAA_DB_PASSWORD="$DB_PASSWORD"

php -r '
$path = ".env";
$env = file_get_contents($path);
$values = [
    "APP_NAME" => "Almeaa",
    "APP_ENV" => "production",
    "APP_DEBUG" => "false",
    "APP_URL" => "https://almeaa.xyz",
    "DB_CONNECTION" => "mysql",
    "DB_HOST" => "127.0.0.1",
    "DB_PORT" => "3306",
    "DB_DATABASE" => "u163825415_almeaa",
    "DB_USERNAME" => "u163825415_nasef",
    "SESSION_DRIVER" => "database",
    "CACHE_STORE" => "database",
    "QUEUE_CONNECTION" => "database",
    "MAIL_MAILER" => "log",
];
foreach ($values as $key => $value) {
    $line = $key . "=" . $value;
    if (preg_match("/^" . preg_quote($key, "/") . "=.*$/m", $env)) {
        $env = preg_replace("/^" . preg_quote($key, "/") . "=.*$/m", $line, $env);
    } else {
        $env .= PHP_EOL . $line;
    }
}
$password = getenv("ALMEAA_DB_PASSWORD");
$line = "DB_PASSWORD=" . $password;
if (preg_match("/^DB_PASSWORD=.*$/m", $env)) {
    $env = preg_replace("/^DB_PASSWORD=.*$/m", $line, $env);
} else {
    $env .= PHP_EOL . $line;
}
file_put_contents($path, $env);
'

unset ALMEAA_DB_PASSWORD DB_PASSWORD

mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache || true

php artisan key:generate --force
php artisan config:clear
php artisan cache:clear || true

echo "Checking database connection..."
php artisan migrate:status >/dev/null

echo "Running database migrations and seeders..."
php artisan migrate --seed --force
php artisan storage:link || true
php artisan optimize
php artisan filament:optimize

echo "Setup complete. Open https://almeaa.xyz"
