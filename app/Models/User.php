<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\PermissionRegistry;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $locale
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, DailyBriefing> $acknowledgedDailyBriefings
 * @property-read int|null $acknowledged_daily_briefings_count
 * @property-read Collection<int, BranchTransfer> $completedBranchTransfers
 * @property-read int|null $completed_branch_transfers_count
 * @property-read Collection<int, Purchase> $createdPurchases
 * @property-read int|null $created_purchases_count
 * @property-read Collection<int, ForecastRun> $forecastRuns
 * @property-read int|null $forecast_runs_count
 * @property-read Collection<int, DailyBriefing> $generatedDailyBriefings
 * @property-read int|null $generated_daily_briefings_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, PurchaseRecommendation> $purchaseRecommendations
 * @property-read int|null $purchase_recommendations_count
 * @property-read Collection<int, PurchaseReceipt> $receivedPurchaseReceipts
 * @property-read int|null $received_purchase_receipts_count
 * @property-read Collection<int, BranchTransfer> $requestedBranchTransfers
 * @property-read int|null $requested_branch_transfers_count
 * @property-read Collection<int, ProcurementAlert> $resolvedProcurementAlerts
 * @property-read int|null $resolved_procurement_alerts_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, SupplierNegotiation> $supplierNegotiations
 * @property-read int|null $supplier_negotiations_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, ?string $guard = null, bool $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, ?string $guard = null)
 *
 * @mixin \Eloquent
 */
#[Fillable(['name', 'email', 'locale', 'phone', 'password', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable {
        HasRoles::hasPermissionTo as spatieHasPermissionTo;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locale' => 'string',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(PermissionRegistry::ROLE_ADMIN);
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(PermissionRegistry::ROLE_CUSTOMER);
    }

    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->spatieHasPermissionTo($permission, $guardName)) {
            return true;
        }

        $permissionName = \is_string($permission) ? $permission : ($permission->name ?? null);

        if ($permissionName === null) {
            return false;
        }

        return $this->temporaryPermissions()
            ->currentlyValid()
            ->where('permission_name', $permissionName)
            ->exists();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function temporaryPermissions(): HasMany
    {
        return $this->hasMany(UserTemporaryPermission::class);
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public static function statuses(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_INACTIVE];
    }

    public function createdPurchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'created_by');
    }

    public function receivedPurchaseReceipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class, 'received_by');
    }

    public function resolvedProcurementAlerts(): HasMany
    {
        return $this->hasMany(ProcurementAlert::class, 'resolved_by');
    }

    public function forecastRuns(): HasMany
    {
        return $this->hasMany(ForecastRun::class, 'created_by');
    }

    public function purchaseRecommendations(): HasMany
    {
        return $this->hasMany(PurchaseRecommendation::class, 'created_by');
    }

    public function requestedBranchTransfers(): HasMany
    {
        return $this->hasMany(BranchTransfer::class, 'requested_by');
    }

    public function completedBranchTransfers(): HasMany
    {
        return $this->hasMany(BranchTransfer::class, 'completed_by');
    }

    public function supplierNegotiations(): HasMany
    {
        return $this->hasMany(SupplierNegotiation::class, 'owner_id');
    }

    public function generatedDailyBriefings(): HasMany
    {
        return $this->hasMany(DailyBriefing::class, 'generated_by');
    }

    public function acknowledgedDailyBriefings(): HasMany
    {
        return $this->hasMany(DailyBriefing::class, 'acknowledged_by');
    }
}
