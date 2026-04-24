# Deployment

## Production deploy lépések

1. Kód frissítése.
2. Composer dependency telepítés.
3. NPM build.
4. Laravel cache-ek frissítése.
5. Migrációk futtatása.
6. Queue worker újraindítás, ha használatban van.
7. Smoke teszt.

## Parancs minta

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## Queue

Ha e-mail, riport vagy háttérfolyamat queue-t használ, deploy után queue worker restart szükséges.

## Rollback terv

1. Release visszaállítása előző verzióra.
2. Ha migráció érintett, rollback csak előzetes adatmentési tervvel.
3. Cache clear.
4. Smoke teszt:
   - login,
   - admin dashboard,
   - rendelés lista,
   - beszerzés lista,
   - inventory dashboard.

## Adatbázis mentés

Production migráció előtt adatbázis backup kötelező, különösen inventory és pénzügyi mezőket érintő változtatásnál.

