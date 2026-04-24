<?php

namespace App\Support;

use App\Services\Audit\AuthorizationAuditService;
use App\Services\Audit\InventoryAuditService;
use App\Services\Audit\OrderAuditService;
use App\Services\Audit\UserActivityAuditService;

class AuditEventRegistry
{
    /**
     * @return array<int, string>
     */
    public static function eventKeys(): array
    {
        return array_values(array_unique(array_merge(
            AuthorizationAuditService::eventKeys(),
            InventoryAuditService::eventKeys(),
            OrderAuditService::eventKeys(),
            UserActivityAuditService::eventKeys(),
        )));
    }

    /**
     * @return array<string, string>
     */
    public static function eventLabels(): array
    {
        return [
            AuthorizationAuditService::ROLE_CREATED => 'Szerepkör létrehozva',
            AuthorizationAuditService::ROLE_UPDATED => 'Szerepkör frissítve',
            AuthorizationAuditService::ROLE_DELETED => 'Szerepkör törölve',
            AuthorizationAuditService::ROLE_UPDATE_BLOCKED => 'Szerepkör frissítés tiltva',
            AuthorizationAuditService::ROLE_DELETE_BLOCKED => 'Szerepkör törlés tiltva',
            AuthorizationAuditService::ROLE_PERMISSIONS_SYNCED => 'Szerepkör jogosultságok szinkronizálva',
            AuthorizationAuditService::ROLE_PERMISSIONS_SYNC_BLOCKED => 'Szerepkör jogosultság szinkron tiltva',
            AuthorizationAuditService::USER_ROLES_SYNCED => 'Felhasználó szerepkörök szinkronizálva',
            AuthorizationAuditService::USER_ROLES_SYNC_BLOCKED => 'Felhasználó szerepkör szinkron tiltva',

            UserActivityAuditService::USER_LOGIN => 'Felhasznalo belepett',
            UserActivityAuditService::USER_LOGOUT => 'Felhasznalo kijelentkezett',
            UserActivityAuditService::USER_REGISTERED => 'Felhasznalo regisztralt',
            UserActivityAuditService::USER_EMAIL_VERIFIED => 'Email cim megerositve',

            OrderAuditService::ORDER_PLACED => 'Rendelés leadva',
            OrderAuditService::ORDER_STATUS_UPDATED => 'Rendelés statusz frissítve',
            OrderAuditService::ORDER_CANCELLED => 'Rendelés torolve',
            OrderAuditService::ORDER_INTERNAL_NOTE_CREATED => 'Belso jegyzet létrehozva',
            OrderAuditService::ORDER_INTERNAL_NOTE_UPDATED => 'Belso jegyzet frissítve',
            OrderAuditService::ORDER_PICKUP_UPDATED => 'Átvételi adatok frissítve',

            InventoryAuditService::SUPPLIER_CREATED => 'Beszállító létrehozva',
            InventoryAuditService::SUPPLIER_UPDATED => 'Beszállító frissítve',
            InventoryAuditService::SUPPLIER_DELETED => 'Beszállító törölve',
            InventoryAuditService::PURCHASE_CREATED => 'Beszerzés létrehozva',
            InventoryAuditService::PURCHASE_POSTED => 'Beszerzés könyvelve',
            InventoryAuditService::PURCHASE_CANCELLED => 'Beszerzés stornózva',
            InventoryAuditService::INVENTORY_ADJUSTED => 'Készlet korrekció rögzítve',
            InventoryAuditService::WASTE_RECORDED => 'Selejt rögzítve',
            InventoryAuditService::STOCK_COUNT_CLOSED => 'Leltár lezárva',
            InventoryAuditService::INVENTORY_SHORTAGE_DETECTED => 'Készlethiány észlelve',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function logNameLabels(): array
    {
        return [
            AuthorizationAuditService::LOG_NAME => 'Jogosultságkezelés',
            InventoryAuditService::LOG_NAME => 'Készlet és beszerzés',
            UserActivityAuditService::LOG_NAME => 'Felhasználói aktivitás',
            OrderAuditService::LOG_NAME => 'Rendelések',
        ];
    }
}

