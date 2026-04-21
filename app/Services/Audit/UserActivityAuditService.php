<?php

namespace App\Services\Audit;

use App\Models\User;

class UserActivityAuditService extends BaseAuditService
{
    public const LOG_NAME = 'user-activity';

    public const USER_LOGIN = 'user.login';
    public const USER_LOGOUT = 'user.logout';
    public const USER_REGISTERED = 'user.registered';
    public const USER_EMAIL_VERIFIED = 'user.email.verified';

    /**
     * @return array<int, string>
     */
    public static function eventKeys(): array
    {
        return [
            self::USER_LOGIN,
            self::USER_LOGOUT,
            self::USER_REGISTERED,
            self::USER_EMAIL_VERIFIED,
        ];
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logLogin(User $user, array $context = []): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::USER_LOGIN,
            description: 'User login',
            actor: $user,
            subject: $user,
            before: ['authenticated' => false],
            after: ['authenticated' => true],
            context: $context,
            extraProperties: [
                'target_user' => $this->userSnapshot($user),
            ],
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logLogout(User $user, array $context = []): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::USER_LOGOUT,
            description: 'User logout',
            actor: $user,
            subject: $user,
            before: ['authenticated' => true],
            after: ['authenticated' => false],
            context: $context,
            extraProperties: [
                'target_user' => $this->userSnapshot($user),
            ],
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logRegistered(User $user, array $context = []): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::USER_REGISTERED,
            description: 'User registered',
            actor: $user,
            subject: $user,
            before: ['user' => null],
            after: ['user' => $this->userSnapshot($user)],
            context: $context,
            extraProperties: [
                'target_user' => $this->userSnapshot($user),
            ],
        );
    }

    /**
     * @param array<string, mixed> $context
     */
    public function logEmailVerified(User $user, array $context = []): void
    {
        $this->log(
            logName: self::LOG_NAME,
            eventKey: self::USER_EMAIL_VERIFIED,
            description: 'User email verified',
            actor: $user,
            subject: $user,
            before: ['email_verified_at' => null],
            after: ['email_verified_at' => $user->email_verified_at?->toIso8601String()],
            context: $context,
            extraProperties: [
                'target_user' => $this->userSnapshot($user),
            ],
        );
    }
}
