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

describe('Admin CEO Dashboard page', () => {
    it('renders ceo summary, security and audit sections', () => {
        const wrapper = mount(IndexPage, {
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

        expect(wrapper.text()).toContain('CEO irányítópult');
        expect(wrapper.text()).toContain('Bevétel');
        expect(wrapper.text()).toContain('Becsült profit');
        expect(wrapper.text()).toContain('Konverziós összkép');
        expect(wrapper.text()).toContain('Checkout funnel');
        expect(wrapper.text()).toContain('Regisztrációs funnel');
        expect(wrapper.text()).toContain('WoW');
        expect(wrapper.text()).toContain('MoM');
        expect(wrapper.text()).toContain('Zöld');
        expect(wrapper.text()).toContain('Top termékek (profit alapján)');
        expect(wrapper.text()).toContain('Biztonsági jelzések');
        expect(wrapper.text()).toContain('Audit kiemelések');
        expect(wrapper.text()).toContain('Rendelési profit trend');
        expect(wrapper.text()).toContain('Kovászos cipó');
    });
});
