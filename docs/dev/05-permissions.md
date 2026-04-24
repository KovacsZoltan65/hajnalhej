# Permissions

## Spatie Permission

A rendszer Spatie Permission csomagot használ role és permission kezelésre.

Alap szerepek:

- `admin`
- `customer`

## PermissionRegistry

`app/Support/PermissionRegistry.php` a rendszer jogosultsági definíciós forrása.

Tartalmazza:

- permission name,
- module,
- label,
- description,
- dangerous flag,
- audit_sensitive flag,
- sort.

## Policy minták

Példa:

```php
public function create(User $user): bool
{
    return $user->can(PermissionRegistry::PURCHASES_MANAGE);
}
```

## Route middleware

Admin route-ok route middleware-rel is védettek:

```php
Route::post('/purchases', 'store')
    ->middleware('permission:purchases.manage');
```

## UI auth.can

Frontend oldalon a jogosultság csak UX segédlet. Gombot lehet elrejteni, de minden műveletet backend oldalon is védeni kell.

## Új permission hozzáadás lépései

1. Konstans `PermissionRegistry`-ben.
2. Definíció hozzáadása.
3. Policy használat.
4. Route middleware, ha admin route.
5. Seeder vagy sync futtatás.
6. Teszt admin és customer hozzáférésre.

