# Audit log

## Alap

A rendszer Spatie Activitylog csomagot használ. Az audit célja, hogy üzleti és biztonsági műveletek visszakövethetők legyenek.

## Log domainek

Jellemző domainek:

- user activity,
- orders,
- inventory,
- authorization,
- security.

## Event naming convention

Ajánlott forma:

```text
domain.action
```

Példák:

- `purchase.created`
- `purchase.posted`
- `inventory.adjusted`
- `order.status_updated`
- `user.login`

## Mit logolunk?

- rendelés létrehozás,
- rendelés státusz módosítás,
- beszerzés létrehozás,
- beszerzés könyvelés,
- készletkorrekció,
- selejt,
- jogosultság módosítás,
- kritikus security esemény.

## Mit nem logolunk?

- minden dashboard megtekintést,
- minden listázást,
- zajos read-only műveleteket.

## Retention javaslat

- Operatív audit: legalább 12 hónap.
- Jogosultsági és security audit: legalább 24 hónap.
- Archiválás előtt export vagy hideg tárolás javasolt.

