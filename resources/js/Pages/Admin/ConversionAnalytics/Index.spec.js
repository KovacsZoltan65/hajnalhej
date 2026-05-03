import { mount } from '@vue/test-utils';
import IndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    router: { get: vi.fn() },
}));

const translations = {
    'nav.conversion_analytics': 'Conversion Analytics',
    'conversion_analytics.description': 'Conversion rates, time-series trends, hero variant comparison and funnel drop-off points.',
    'conversion_analytics.real_conversion_rates': 'Real conversion rates',
    'conversion_analytics.revenue_and_basket_value_trend': 'Revenue and basket value trend',
    'conversion_analytics.top_product_revenue': 'Top product revenue',
    'conversion_analytics.hero_variant_comparison': 'Hero variant comparison',
    'conversion_analytics.checkout_session_rate': 'Checkout session rate',
    'conversion_analytics.reg_session_rate': 'Registration session rate',
    'conversion_analytics.top_funnel_drop_off_points': 'Top funnel drop-off points',
    'conversion_analytics.funnel_steps': 'Funnel steps',
    'common.all_events': 'All events',
    'common.cta_click': 'CTA click',
    'common.checkout_closing': 'Checkout closing',
    'common.registration_complete': 'Registration complete',
    'common.income': 'Income',
    'common.avg_basket_value': 'Average basket value',
    'common.business_indicators': 'Business indicators',
    'common.order_number': 'Order number',
    'common.individual_customers': 'Individual customers',
    'common.returning_customer_rate': 'Returning customer rate',
    'common.ltv_periodic': 'LTV (periodic)',
    'common.date': 'Date',
    'common.order': 'Order',
    'common.product': 'Product',
    'common.piece': 'Piece',
    'common.time_series_trends': 'Time series trends',
    'common.cta': 'CTA',
    'common.add_to_card': 'Add to cart',
    'common.checkout_submit': 'Checkout submit',
    'common.checkout_completed': 'Checkout completed',
    'common.reg_completed': 'Reg. completed',
    'common.submit_complete': 'Submit->Complete',
    'common.variant': 'Variant',
    'common.view': 'View',
    'common.view_share': 'View share',
    'common.cta_ctr': 'CTA CTR',
    'common.reg_ctr': 'Reg CTR',
    'common.funnel': 'Funnel',
    'common.step_change': 'Step change',
    'common.fall': 'Drop',
    'common.pcs': 'pcs',
    'common.percent_code': '%',
    'common.cta_top_click': 'Top CTA clicks',
    'common.cta_id': 'CTA ID',
    'common.click': 'Click',
};

vi.mock('laravel-vue-i18n', () => ({
    currentLocale: { value: 'en' },
    transChoice: (key, count, replacements = {}) => {
        if (key !== 'common.day_count') {
            return key;
        }

        return `${replacements.count ?? count} ${count === 1 ? 'day' : 'days'}`;
    },
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/select', () => ({
    default: {
        name: 'Select',
        props: ['modelValue', 'options'],
        emits: ['update:model-value'],
        template: '<select><option v-for="option in options" :key="option.value">{{ option.label }}</option></select>',
    },
}));

describe('Admin Conversion Analytics page', () => {
    it('renders conversion rate, trend and drop-off sections', () => {
        const wrapper = mount(IndexPage, {
            global: {
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
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
                    commerce: {
                        revenue_total: 15000,
                        orders_count: 3,
                        unique_customers: 2,
                        average_cart_value: 5000,
                        repeat_customers: 1,
                        repeat_customer_rate: 50,
                        ltv: 7500,
                    },
                    commerce_trend: {
                        points: [
                            {
                                date: '2026-04-23',
                                revenue: 6000,
                                orders_count: 2,
                                average_cart_value: 3000,
                            },
                        ],
                    },
                    top_product_revenue: [
                        { product_name: 'Kovászos cipó', revenue: 12000, quantity: 4, orders: 2 },
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

        expect(wrapper.text()).toContain('Real conversion rates');
        expect(wrapper.text()).toContain('Business indicators');
        expect(wrapper.text()).toContain('Revenue and basket value trend');
        expect(wrapper.text()).toContain('Top product revenue');
        expect(wrapper.text()).toContain('LTV (periodic)');
        expect(wrapper.text()).toContain('Time series trends');
        expect(wrapper.text()).toContain('Hero variant comparison');
        expect(wrapper.text()).toContain('Top funnel drop-off points');
        expect(wrapper.text()).toContain('20.00%');
        expect(wrapper.text()).toContain('1 day');
        expect(wrapper.text()).toContain('7 days');
        expect(wrapper.text()).toMatch(/HUF\s*15,000/);
    });
});
