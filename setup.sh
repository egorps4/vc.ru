#!/bin/bash

set -e

echo "Installing Composer dependencies..."
docker-compose exec -T app composer install --no-interaction --optimize-autoloader

echo "Creating database..."
docker-compose exec -T app php bin/console doctrine:database:create --if-not-exists

echo "Running migrations..."
docker-compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction

echo "Loading fixtures..."
docker-compose exec -T app php bin/console doctrine:fixtures:load --no-interaction

echo "Running tests..."
docker-compose exec -T app php bin/phpunit

echo "Setup completed successfully!"