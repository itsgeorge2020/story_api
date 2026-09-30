# Деплой story_api

Инфраструктура: nginx + php-fpm + три воркера очередей (`generate_text`, `generate_image`, `telegram`) + MySQL + Redis. Код монтируется в контейнеры через bind-mount, поэтому деплой выполняется вручную на сервере.

Все artisan/composer-команды выполняются внутри работающего контейнера `php` через `docker compose exec php ...`.

## Команда запуска

- **Локально:** `docker compose up -d` — автоматически подхватывает `docker-compose.override.yml` (открытые порты MySQL/Redis, локальный nginx-конфиг).
- **Сервер:** `docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d` — override не подключается, порты БД/Redis наружу не выставляются, TLS через certbot.

Ниже `COMPOSE` — сокращение для серверного набора флагов:

```bash
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
```

## Первый подъём (однократно на новом сервере)

```bash
git pull
cp .env.example .env
nano .env                      # APP_ENV=production, APP_DEBUG=false, ключи, доступы к БД

$COMPOSE up -d --build

$COMPOSE exec php composer install --no-dev --optimize-autoloader
$COMPOSE exec php php artisan key:generate      # если APP_KEY ещё не задан
$COMPOSE exec php php artisan storage:link      # симлинк на public/generated_images
$COMPOSE exec php php artisan migrate --force
$COMPOSE exec php php artisan optimize          # кэш config/route/event
```

Healthcheck-и MySQL/Redis настроены в compose, поэтому `php` и воркеры стартуют только после готовности БД и Redis — гонок при первом подъёме не будет.

## Рутинный деплой

```bash
git pull

# только если менялся Dockerfile:
$COMPOSE up -d --build

# только если менялся composer.lock:
$COMPOSE exec php composer install --no-dev --optimize-autoloader

$COMPOSE exec php php artisan migrate --force

$COMPOSE exec php php artisan optimize:clear     # сброс старого кэша
$COMPOSE exec php php artisan optimize           # пересборка config/route/event кэша

$COMPOSE exec php php artisan queue:restart      # воркеры подхватят новый код
```

## Важные нюансы

1. **`queue:restart` обязателен после каждого деплоя.** Воркеры — отдельные долгоживущие контейнеры; без этой команды они продолжают выполнять старый код до самопересборки (до часа по `--max-time`). `queue:restart` даёт воркеру доработать текущую джобу и мягко перезапуститься, после чего Docker поднимает его заново (`restart: unless-stopped`).

2. **Порядок кэша: сначала `optimize:clear`, потом `optimize`.** Если поменялись `.env` или конфиги, а конфиг закэширован, изменения не применятся, пока кэш не сброшен и не пересобран.

3. **Миграции — только `--force`** (в production Laravel иначе спросит подтверждение и упадёт в неинтерактивной среде).

## Полезные команды

```bash
$COMPOSE ps                          # статус контейнеров (в т.ч. health)
$COMPOSE logs -f worker_generate_text
$COMPOSE logs -f php
$COMPOSE exec php php artisan queue:failed        # упавшие джобы
$COMPOSE exec php php artisan queue:retry all     # перезапустить все упавшие
$COMPOSE down                        # остановить (данные MySQL в volume сохраняются)
```
