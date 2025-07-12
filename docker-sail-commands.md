# Docker Sail Commands for CollaborInbox

## Starting and Stopping Services

### Start all services
```bash
docker compose up -d
```

### Stop all services
```bash
docker compose down
```

### Restart services
```bash
docker compose restart
```

## Laravel Artisan Commands

### Run migrations
```bash
docker compose exec laravel.test php artisan migrate
```

### Clear caches
```bash
docker compose exec laravel.test php artisan cache:clear
docker compose exec laravel.test php artisan config:clear
docker compose exec laravel.test php artisan route:clear
docker compose exec laravel.test php artisan view:clear
```

### Run tinker
```bash
docker compose exec laravel.test php artisan tinker
```

## Accessing Services

- **Laravel Application**: http://localhost:8000
- **Mailpit Email Interface**: http://localhost:8025
- **MySQL Database**: 
  - Host: localhost
  - Port: 3307
  - Database: collaborinbox
  - Username: sail
  - Password: password
- **Redis**: 
  - Host: localhost
  - Port: 6379

## Logs

### View Laravel logs
```bash
docker compose exec laravel.test tail -f storage/logs/laravel.log
```

### View container logs
```bash
docker compose logs -f laravel.test
docker compose logs -f mysql
docker compose logs -f redis
```

## Database Management

### Access MySQL CLI
```bash
docker compose exec mysql mysql -u sail -ppassword collaborinbox
```

### Import database dump
```bash
docker compose exec -T mysql mysql -u sail -ppassword collaborinbox < dump.sql
```

### Export database
```bash
docker compose exec mysql mysqldump -u sail -ppassword collaborinbox > backup.sql
```

## Composer Commands

### Install dependencies
```bash
docker compose exec laravel.test composer install
```

### Update dependencies
```bash
docker compose exec laravel.test composer update
```

## Testing

### Run tests
```bash
docker compose exec laravel.test php artisan test
```

### Run specific test
```bash
docker compose exec laravel.test php artisan test --filter TestName
```

## Troubleshooting

### Check container status
```bash
docker ps
```

### Rebuild containers
```bash
docker compose build --no-cache
docker compose up -d
```

### Reset everything
```bash
docker compose down -v  # Remove volumes too
docker compose up -d
docker compose exec laravel.test php artisan migrate:fresh --seed
```
