#!/bin/bash

# Путь к проекту
PROJECT_DIR=~/www/crtm-dinara.david-freedman.com.ua

cd $PROJECT_DIR || exit

echo "🚀 Запускаем Laravel Queue Worker..."

nohup php artisan queue:work --sleep=3 --tries=3 > storage/logs/worker.log 2>&1 &

echo "✅ Worker запущен. Логи пишутся в storage/logs/worker.log"
