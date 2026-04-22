<?php

namespace App\Support;

use App\Services\Audit\AuthorizationAuditService;
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function logNameLabels(): array
    {
        return [
            AuthorizationAuditService::LOG_NAME => 'Jogosultságkezelés',
            UserActivityAuditService::LOG_NAME => 'Felhasználói aktivitás',
            OrderAuditService::LOG_NAME => 'Rendelések',
        ];
    }
}

