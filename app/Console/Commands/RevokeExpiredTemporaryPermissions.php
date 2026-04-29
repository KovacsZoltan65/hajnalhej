<?php

namespace App\Console\Commands;

use App\Services\UserAdminService;
use Illuminate\Console\Command;

class RevokeExpiredTemporaryPermissions extends Command
{
    protected $signature = 'users:revoke-expired-temporary-permissions';

    protected $description = 'Revokes expired user temporary permissions.';

    public function handle(UserAdminService $service): int
    {
        $count = $service->revokeExpiredTemporaryPermissions();

        $this->info("Revoked {$count} expired temporary permissions.");

        return self::SUCCESS;
    }
}
