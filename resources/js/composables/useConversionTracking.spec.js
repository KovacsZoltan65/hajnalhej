import { useConversionTracking } from './useConversionTracking';

describe('useConversionTracking', () => {
    const originalFetch = global.fetch;
    const originalQuerySelector = document.querySelector;

    beforeEach(() => {
        global.fetch = vi.fn(() => Promise.resolve({ ok: true }));
        document.querySelector = vi.fn(() => ({
            getAttribute: () => 'csrf-token-value',
        }));
    });

    afterEach(() => {
        global.fetch = originalFetch;
        document.querySelector = originalQuerySelector;
        vi.restoreAllMocks();
    });

    it('sends cta click event payload to backend endpoint', () => {
        const { trackCtaClick } = useConversionTracking();

        trackCtaClick('hero.register_primary', {
            funnel: 'landing',
            step: 'click',
            heroVariant: 'artisan_story',
            metadata: { href: '/register' },
        });

        expect(global.fetch).toHaveBeenCalledTimes(1);

        const [url, options] = global.fetch.mock.calls[0];

        expect(url).toBe('/conversion-events');
        expect(options.method).toBe('POST');

        const parsedBody = JSON.parse(options.body);
        expect(parsedBody.event_key).toBe('cta.click');
        expect(parsedBody.cta_id).toBe('hero.register_primary');
        expect(parsedBody.hero_variant).toBe('artisan_story');
    });
});

