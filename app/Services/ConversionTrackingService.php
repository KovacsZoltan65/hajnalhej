<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\ConversionEventRepository;
use App\Support\ConversionEventRegistry;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ConversionTrackingService
{
    public function __construct(private readonly ConversionEventRepository $repository)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function trackFromRequest(array $payload, Request $request): void
    {
        $this->track(
            eventKey: (string) $payload['event_key'],
            source: 'frontend',
            user: $request->user(),
            sessionId: $request->session()->getId(),
            path: (string) $request->path(),
            url: $request->fullUrl(),
            referrer: (string) $request->headers->get('referer'),
            userAgent: (string) $request->userAgent(),
            ip: (string) $request->ip(),
            funnel: $this->nullableString($payload['funnel'] ?? null),
            step: $this->nullableString($payload['step'] ?? null),
            ctaId: $this->nullableString($payload['cta_id'] ?? null),
            heroVariant: $this->nullableString($payload['hero_variant'] ?? null),
            metadata: $this->normalizeMetadata($payload['metadata'] ?? []),
            occurredAt: $this->resolveOccurredAt($payload['occurred_at'] ?? null),
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function trackBackendEvent(
        string $eventKey,
        Request $request,
        ?User $user = null,
        ?string $funnel = null,
        ?string $step = null,
        ?string $ctaId = null,
        ?string $heroVariant = null,
        array $metadata = [],
    ): void {
        $this->track(
            eventKey: $eventKey,
            source: 'backend',
            user: $user ?? $request->user(),
            sessionId: $request->session()->getId(),
            path: (string) $request->path(),
            url: $request->fullUrl(),
            referrer: (string) $request->headers->get('referer'),
            userAgent: (string) $request->userAgent(),
            ip: (string) $request->ip(),
            funnel: $funnel,
            step: $step,
            ctaId: $ctaId,
            heroVariant: $heroVariant,
            metadata: $this->normalizeMetadata($metadata),
            occurredAt: now(),
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function trackSystemEvent(
        string $eventKey,
        ?User $user = null,
        ?string $sessionId = null,
        ?string $funnel = null,
        ?string $step = null,
        ?string $ctaId = null,
        ?string $heroVariant = null,
        array $metadata = [],
    ): void {
        $this->track(
            eventKey: $eventKey,
            source: 'backend',
            user: $user,
            sessionId: $sessionId,
            path: null,
            url: null,
            referrer: null,
            userAgent: null,
            ip: null,
            funnel: $funnel,
            step: $step,
            ctaId: $ctaId,
            heroVariant: $heroVariant,
            metadata: $this->normalizeMetadata($metadata),
            occurredAt: now(),
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function track(
        string $eventKey,
        string $source,
        ?User $user,
        ?string $sessionId,
        ?string $path,
        ?string $url,
        ?string $referrer,
        ?string $userAgent,
        ?string $ip,
        ?string $funnel,
        ?string $step,
        ?string $ctaId,
        ?string $heroVariant,
        array $metadata,
        Carbon $occurredAt,
    ): void {
        if (! \in_array($eventKey, ConversionEventRegistry::eventKeys(), true)) {
            return;
        }

        $this->repository->create([
            'event_key' => $eventKey,
            'funnel' => $funnel,
            'step' => $step,
            'cta_id' => $ctaId,
            'hero_variant' => $heroVariant,
            'source' => $source,
            'user_id' => $user?->id,
            'session_id' => $this->nullableString($sessionId),
            'path' => $this->trimToLength($path, 255),
            'url' => $this->trimToLength($url, 500),
            'referrer' => $this->trimToLength($referrer, 500),
            'ip_hash' => $ip === null || trim($ip) === '' ? null : hash('sha256', $ip),
            'user_agent' => $this->trimToLength($userAgent, 500),
            'metadata' => $metadata,
            'occurred_at' => $occurredAt,
        ]);
    }

    private function nullableString(mixed $value): ?string
    {
        if (! \is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    private function normalizeMetadata(array $metadata): array
    {
        $safe = [];

        foreach ($metadata as $key => $value) {
            if (! \is_string($key) || trim($key) === '') {
                continue;
            }

            if (\is_scalar($value) || $value === null) {
                $safe[$key] = $value;
                continue;
            }

            if (\is_array($value)) {
                $safe[$key] = $value;
            }
        }

        return $safe;
    }

    private function resolveOccurredAt(mixed $value): Carbon
    {
        if (! \is_string($value) || trim($value) === '') {
            return now();
        }

        try {
            $parsed = Carbon::parse($value);
        } catch (Throwable) {
            return now();
        }

        return $parsed->isFuture() ? now() : $parsed;
    }

    private function trimToLength(?string $value, int $maxLength): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            return null;
        }

        return Str::limit($normalized, $maxLength, '');
    }
}
