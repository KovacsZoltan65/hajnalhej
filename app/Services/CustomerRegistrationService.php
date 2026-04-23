<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CustomerRegistrationRepository;
use App\Services\Audit\UserActivityAuditService;
use App\Support\ConversionEventRegistry;
use App\Support\PermissionRegistry;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerRegistrationService
{
    public function __construct(
        private readonly CustomerRegistrationRepository $repository,
        private readonly UserActivityAuditService $auditService,
        private readonly ConversionTrackingService $conversionTrackingService,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function register(array $payload, ?string $sessionId = null): User
    {
        $user = DB::transaction(function () use ($payload): User {
            $user = $this->repository->createCustomer([
                'name' => trim((string) $payload['name']),
                'email' => mb_strtolower(trim((string) $payload['email'])),
                'password' => (string) $payload['password'],
            ]);

            $user->syncRoles([PermissionRegistry::ROLE_CUSTOMER]);

            event(new Registered($user));

            return $user;
        });

        Auth::login($user);

        $this->auditService->logRegistered($user, [
            'operation' => 'auth.register',
            'source' => 'public.register',
        ]);

        $this->conversionTrackingService->trackSystemEvent(
            eventKey: ConversionEventRegistry::REGISTRATION_COMPLETED,
            user: $user,
            sessionId: $sessionId,
            funnel: 'registration',
            step: 'complete',
            metadata: [
                'email_verified' => $user->hasVerifiedEmail(),
            ],
        );

        return $user;
    }
}
