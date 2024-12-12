# Установка проекта

Склонируйте или скачайте репозиторий

Перейдите в папку с проектом и выполните команду:
```bash
composer install
```
Затем продублируйте файл .env.example в файл .env

После создания файла .env выполните команды по порядку
```bash
php artisan key:generate
php artisan migrate
php artisan make:filament-user
```

Последняя команда необходима для создания администратора. У администратора обязательно должен быть следующий логин:
```bash
admin@gmail.com
```

Далее перейдите в  фай .env и замените содержимое файла на это:

```bash
APP_NAME="Муза"
APP_ENV=local
APP_KEY=base64:vSwT0eu3hnOe4vktsJ83acaBMSTWCCES8lmkGIWXGdc=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost

APP_LOCALE=ru
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=ru_RU

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=
# DB_USERNAME=
# DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.yandex.ru
MAIL_PORT=465
MAIL_USERNAME="n1k.mochalov@yandex.ru"
MAIL_PASSWORD="cjluofwbmpscgqsl"
MAIL_FROM_ADDRESS="n1k.mochalov@yandex.ru"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

```
