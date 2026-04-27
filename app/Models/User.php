<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Support\PermissionRegistry;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
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

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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
