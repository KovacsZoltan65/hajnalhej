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
            AuthorizationAuditService::ROLE_CREATED => 'Role letrehozva',
            AuthorizationAuditService::ROLE_UPDATED => 'Role frissitve',
            AuthorizationAuditService::ROLE_DELETED => 'Role torolve',
            AuthorizationAuditService::ROLE_UPDATE_BLOCKED => 'Role frissites tiltva',
            AuthorizationAuditService::ROLE_DELETE_BLOCKED => 'Role torles tiltva',
            AuthorizationAuditService::ROLE_PERMISSIONS_SYNCED => 'Role jogosultsagok szinkronizalva',
            AuthorizationAuditService::ROLE_PERMISSIONS_SYNC_BLOCKED => 'Role jogosultsag szinkron tiltva',
            AuthorizationAuditService::USER_ROLES_SYNCED => 'User role-ok szinkronizalva',
            AuthorizationAuditService::USER_ROLES_SYNC_BLOCKED => 'User role szinkron tiltva',

            UserActivityAuditService::USER_LOGIN => 'Felhasznalo belepett',
            UserActivityAuditService::USER_LOGOUT => 'Felhasznalo kijelentkezett',
            UserActivityAuditService::USER_REGISTERED => 'Felhasznalo regisztralt',
            UserActivityAuditService::USER_EMAIL_VERIFIED => 'Email cim megerositve',

            OrderAuditService::ORDER_PLACED => 'Rendeles leadva',
            OrderAuditService::ORDER_STATUS_UPDATED => 'Rendeles statusz frissitve',
            OrderAuditService::ORDER_CANCELLED => 'Rendeles torolve',
            OrderAuditService::ORDER_INTERNAL_NOTE_CREATED => 'Belso jegyzet letrehozva',
            OrderAuditService::ORDER_INTERNAL_NOTE_UPDATED => 'Belso jegyzet frissitve',
            OrderAuditService::ORDER_PICKUP_UPDATED => 'Atveteli adatok frissitve',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function logNameLabels(): array
    {
        return [
            AuthorizationAuditService::LOG_NAME => 'Authorization',
            UserActivityAuditService::LOG_NAME => 'User activity',
            OrderAuditService::LOG_NAME => 'Orders',
        ];
    }
}
