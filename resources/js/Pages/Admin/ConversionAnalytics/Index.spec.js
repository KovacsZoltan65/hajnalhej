import { mount } from '@vue/test-utils';
import IndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    router: { get: vi.fn() },
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/select', () => ({
    default: {
        name: 'Select',
        props: ['modelValue'],
        emits: ['update:model-value'],
        template: '<select><slot /></select>',
    },
}));

describe('Admin Conversion Analytics page', () => {
    it('renders conversion rate, trend and drop-off sections', () => {
        const wrapper = mount(IndexPage, {
            props: {
                filters: { days: 30 },
                analytics: {
                    summary: {
                        total_events: 100,
                        cta_clicks: 40,
                        checkout_completions: 10,
                        registration_completions: 8,
                    },
                    conversion_rates: [
                        { id: 'hero_to_register', label: 'Hero -> regisztráció kattintás', rate: 20, numerator: 20, denominator: 100 },
                    ],
                    trend: {
                        points: [
                            {
                                date: '2026-04-23',
                                cta_clicks: 4,
                                cart_adds: 3,
                                checkout_submitted: 2,
                                checkout_completed: 1,
                                registration_completed: 1,
                                checkout_submit_to_complete_rate: 50,
                            },
                        ],
                    },
                    hero_comparison: [
                        {
                            variant: 'artisan_story',
                            views: 50,
                            view_share: 62.5,
                            cta_ctr: 20,
                            register_ctr: 10,
                            checkout_session_rate: 4,
                            registration_session_rate: 2,
                        },
                    ],
                    drop_off_top: [
                        { funnel: 'Kosár funnel', from: 'Kosár oldal', to: 'Checkout oldal', drop_count: 5, drop_rate: 25 },
                    ],
                    funnel_stats: [],
                    cta_top: [],
                },
            },
        });

        expect(wrapper.text()).toContain('Valódi konverziós arányok');
        expect(wrapper.text()).toContain('Idősoros trendek');
        expect(wrapper.text()).toContain('Hero variáns összehasonlítás');
        expect(wrapper.text()).toContain('Top funnel drop-off pontok');
        expect(wrapper.text()).toContain('20.00%');
    });
});

