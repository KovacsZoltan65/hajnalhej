import { mount } from '@vue/test-utils';
import IndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    router: { get: vi.fn() },
}));

const translations = {
    'nav.ceo_dashboard': 'CEO Dashboard',
    'nav.orders': 'Orders',
    'ceo_dashboard.description': 'Revenue, profit, conversion, repeat customer rate, security alerts and audit highlights on one page.',
    'ceo_dashboard.card_estimated_profit': 'Estimated profit',
    'ceo_dashboard.card_profit_rate': 'Profit Rate',
    'ceo_dashboard.card_checkout_conversion': 'Checkout conversion',
    'ceo_dashboard.card_returning_customer_rate': 'Returning customer rate',
    'ceo_dashboard.card_lifetime_value': 'Lifetime value (LTV)',
    'ceo_dashboard.conversion_overview': 'Conversion overview',
    'ceo_dashboard.checkout_funnel': 'Checkout funnel',
    'ceo_dashboard.registration_funnel': 'Registration funnel',
    'ceo_dashboard.top_products': 'Top products (based on profit)',
    'ceo_dashboard.safety_signs': 'Safety signs',
    'ceo_dashboard.critical_alerts': 'Critical alerts',
    'ceo_dashboard.orphaned_permissions': 'Orphaned permissions',
    'ceo_dashboard.dangerous_permissions': 'Dangerous permissions',
    'ceo_dashboard.high_risk_users': 'High-risk users',
    'ceo_dashboard.audit_highlights': 'Audit highlights',
    'ceo_dashboard.order_profit_trend': 'Order profit trend',
    'common.income': 'Income',
    'common.profit': 'Profit',
    'common.product': 'Product',
    'common.piece': 'Piece',
    'common.submitted': 'Submitted',
    'common.completed': 'Completed',
    'common.ratio': 'Ratio',
    'common.successful': 'Successful',
    'common.time': 'Time',
    'common.date': 'Date',
    'common.rag_green': 'Green',
    'common.rag_amber': 'Amber',
    'common.rag_red': 'Red',
    'common.wow': 'WoW',
    'common.mom': 'MoM',
};

vi.mock('laravel-vue-i18n', () => ({
    currentLocale: { value: 'en' },
    trans: (key) => translations[key] ?? key,
    transChoice: (key, count, replacements = {}) => {
        if (key !== 'common.day_count') {
            return translations[key] ?? key;
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

describe('Admin CEO Dashboard page', () => {
    it('renders ceo summary, security and audit sections', () => {
        const wrapper = mount(IndexPage, {
            global: {
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
            props: {
                filters: { days: 30 },
                dashboard: {
                    summary: {
                        revenue: 100000,
                        estimated_profit: 65000,
                        estimated_margin_rate: 65,
                        repeat_customer_rate: 45.5,
                        orders_count: 42,
                        ltv: 7800,
                        checkout_conversion_rate: 23.45,
                    },
                    kpi_insights: {
                        revenue: {
                            rag: 'green',
                            trend: 'up',
                            wow: { percent: 12.5 },
                            mom: { percent: 8.1 },
                        },
                        estimated_profit: {
                            rag: 'green',
                            trend: 'up',
                            wow: { percent: 10.5 },
                            mom: { percent: 7.1 },
                        },
                        checkout_conversion_rate: {
                            rag: 'amber',
                            trend: 'flat',
                            wow: { percent: 0.5 },
                            mom: { percent: -0.4 },
                        },
                        repeat_customer_rate: {
                            rag: 'green',
                            trend: 'up',
                            wow: { percent: 3.1 },
                            mom: { percent: 6.2 },
                        },
                        ltv: {
                            rag: 'amber',
                            trend: 'flat',
                            wow: { percent: 0.2 },
                            mom: { percent: 0.1 },
                        },
                    },
                    comparisons: { wow: {}, mom: {} },
                    conversion: {
                        checkout_submitted: 100,
                        checkout_completed: 23,
                        checkout_conversion_rate: 23,
                        registration_submitted: 50,
                        registration_completed: 15,
                        registration_conversion_rate: 30,
                    },
                    top_products: [
                        {
                            product_id: 1,
                            product_name: 'Kovászos cipó',
                            revenue: 50000,
                            estimated_cost: 20000,
                            estimated_profit: 30000,
                            margin_rate: 60,
                            quantity: 80,
                            orders: 30,
                        },
                    ],
                    security_alerts: {
                        critical_alerts: 2,
                        orphan_permissions: 1,
                        dangerous_permissions: 8,
                        high_risk_users: 3,
                    },
                    audit_highlights: [
                        {
                            id: 1,
                            log_name: 'authorization',
                            severity: 'high',
                            label: 'Jogosultság szinkron',
                            summary: 'Szinkron eredmény: +2 létrehozva, 1 árva.',
                            timestamp: '2026-04-23T08:00:00+00:00',
                        },
                    ],
                    order_profit_trend: {
                        points: [
                            {
                                date: '2026-04-23',
                                revenue: 10000,
                                estimated_cost: 3500,
                                estimated_profit: 6500,
                                margin_rate: 65,
                                orders_count: 6,
                            },
                        ],
                    },
                },
            },
        });

        expect(wrapper.text()).toContain('CEO Dashboard');
        expect(wrapper.text()).toContain('Income');
        expect(wrapper.text()).toContain('Estimated profit');
        expect(wrapper.text()).toContain('Conversion overview');
        expect(wrapper.text()).toContain('Checkout funnel');
        expect(wrapper.text()).toContain('Registration funnel');
        expect(wrapper.text()).toContain('WoW');
        expect(wrapper.text()).toContain('MoM');
        expect(wrapper.text()).toContain('Green');
        expect(wrapper.text()).toContain('Top products (based on profit)');
        expect(wrapper.text()).toContain('Safety signs');
        expect(wrapper.text()).toContain('Audit highlights');
        expect(wrapper.text()).toContain('Order profit trend');
        expect(wrapper.text()).toContain('7 days');
        expect(wrapper.text()).toMatch(/HUF\s*100,000/);
        expect(wrapper.text()).toContain('Kovászos cipó');
    });
});
