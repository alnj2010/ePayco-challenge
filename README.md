docker compose up -d
docker compose exec soap-service-container php artisan doctrine:migrations:migrate