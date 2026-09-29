# СУПЕРВИЗОР НА ПРОДЕ

## Шаг 1: Установка Supervisor
Supervisor — это системная утилита для Linux (не Laravel-пакет). Устанавливается через пакетный менеджер сервера.

Для Ubuntu/Debian:

```
sudo apt-get update
sudo apt-get install -y supervisor
```

## Шаг 2: Конфигурационные файлы для воркеров
Supervisor читает конфиги из /etc/supervisor/conf.d/. 
Тебе нужно два файла — по одному на каждую очередь (deepseek и telegram), потому что они имеют разные таймауты и приоритеты.

### Файл 1: /etc/supervisor/conf.d/story-deepseek-worker.conf

```
[program:story-deepseek-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --queue=deepseek --sleep=3 --max-time=3600 --tries=3 --timeout=120
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker-deepseek.log
stopwaitsecs=150
```

### Файл 2: /etc/supervisor/conf.d/story-telegram-worker.conf

```
[program:story-telegram-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --queue=telegram --sleep=3 --max-time=3600 --tries=3 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker-telegram.log
stopwaitsecs=90
```

## Шаг 3: Активация и запуск
После создания файлов нужно сказать Supervisor'у их прочитать:

Прочитать новые/изменённые конфиги
```
sudo supervisorctl reread
```

Применить изменения (создаст новые процессы)
```
sudo supervisorctl update
```

Запустить оба воркера
```
sudo supervisorctl start story-deepseek-worker:*
sudo supervisorctl start story-telegram-worker:*
```

## Шаг 4: Проверка

Статус всех процессов
```
sudo supervisorctl status
```

Статус конкретного
```
sudo supervisorctl status story-deepseek-worker:*
```

Ожидаемый вывод:
```
story-deepseek-worker:story-deepseek-worker_00   RUNNING   pid 12345, uptime 0:00:10
story-telegram-worker:story-telegram-worker_00   RUNNING   pid 12346, uptime 0:00:10
```
Если увидишь FATAL или EXITED — смотри лог воркера (worker-deepseek.log или worker-telegram.log).

## Шаг 5: Важный момент для деплоя

Каждый раз после обновления кода на продакшене нужно перезапускать воркеры. Иначе они продолжат выполнять старый код из памяти.
```
sudo supervisorctl restart story-deepseek-worker:*
sudo supervisorctl restart story-telegram-worker:*
```
