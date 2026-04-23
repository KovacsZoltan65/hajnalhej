const endpoint = '/conversion-events';

const safeNowIso = () => new Date().toISOString();
const resolveCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

export const useConversionTracking = () => {
    const send = (payload) => {
        const body = JSON.stringify({
            ...payload,
            occurred_at: payload.occurred_at ?? safeNowIso(),
        });

        if (typeof window !== 'undefined' && typeof window.fetch === 'function') {
            window.fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': resolveCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body,
                keepalive: true,
                credentials: 'same-origin',
            }).catch(() => {
                // Tracking must never break UX.
            });

            return;
        }

        if (typeof window === 'undefined' || !window.axios) {
            return;
        }

        window.axios.post(endpoint, JSON.parse(body), { headers: { 'Content-Type': 'application/json' } })
            .catch(() => {
                // Tracking must never break UX.
            });
    };

    const trackCtaClick = (ctaId, options = {}) => {
        send({
            event_key: 'cta.click',
            funnel: options.funnel ?? 'landing',
            step: options.step ?? 'click',
            cta_id: ctaId,
            hero_variant: options.heroVariant ?? null,
            metadata: options.metadata ?? {},
        });
    };

    const trackFunnel = (eventKey, options = {}) => {
        send({
            event_key: eventKey,
            funnel: options.funnel ?? null,
            step: options.step ?? null,
            cta_id: options.ctaId ?? null,
            hero_variant: options.heroVariant ?? null,
            metadata: options.metadata ?? {},
        });
    };

    return {
        send,
        trackCtaClick,
        trackFunnel,
    };
};
