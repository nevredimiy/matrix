
#text/x-generic deploy.sh ( ASCII text )
#text/x-generic deploy.sh ( Bourne-Again shell script, ASCII text executable )
#!/bin/bash

set -e

echo "Starting deployment..."
git pull origin main
php8.4 artisan down
php8.4 composer.phar install --no-dev --optimize-autoloader
php8.4 artisan migrate --force
php8.4 artisan config:cache
php8.4 artisan route:cache
php8.4 artisan view:cache
php8.4 artisan up
echo "Deployment completed successfully."