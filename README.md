# B2B Sipariş Yönetimi API

## Çalıştırma
1. `.env.example` kopyala: `cp .env.example .env`
2. Docker ile: `docker-compose up -d`
3. `docker exec -it app php artisan key:generate`
4. `docker exec -it app php artisan migrate --seed`

## Örnek Kullanıcılar
- **Admin:** admin@demo.com / password
- **Customer:** user@demo.com / password

## Testler
```bash
docker exec -it app php artisan test
