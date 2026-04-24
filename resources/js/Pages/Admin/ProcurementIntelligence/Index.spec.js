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
        props: ['modelValue', 'options', 'optionLabel', 'optionValue', 'placeholder', 'showClear', 'filter'],
        emits: ['update:model-value'],
        template: '<select class="select-stub"></select>',
    },
}));

const filterOptions = {
    ingredients: [{ label: 'Liszt', value: 1 }],
    suppliers: [{ label: 'Malom Kft.', value: 1 }],
    days: [{ label: '30 nap', value: 30 }],
    urgencies: [{ label: 'Kritikus', value: 'critical' }],
    alert_types: [{ label: 'Áremelkedés', value: 'price_increase' }],
};

const dashboard = {
    defaults: {
        consumption_window_days: 28,
        price_increase_alert_percent: 10,
        stockout_warning_days: 7,
        minimum_stock_target_days: 14,
    },
    summary: {
        alerts_count: 2,
        critical_minimum_stock_count: 1,
        price_increase_count: 1,
        stockout_risk_count: 1,
    },
    supplier_price_trends: [
        {
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            unit: 'kg',
            supplier_id: 1,
            supplier_name: 'Malom Kft.',
            last_unit_cost: 360,
            previous_unit_cost: 300,
            change_amount: 60,
            change_percent: 20,
            cheapest_supplier: { supplier_name: 'Malom Kft.', unit_cost: 360 },
            most_expensive_supplier: { supplier_name: 'Malom Kft.', unit_cost: 360 },
            trend: 'emelkedik',
        },
    ],
    ingredient_cost_trends: [
        {
            period_date: '2026-04-24',
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            unit: 'kg',
            supplier_id: 1,
            supplier_name: 'Malom Kft.',
            average_unit_cost: 360,
            weighted_average_cost: 360,
            last_unit_cost: 360,
            purchased_quantity: 10,
            purchases_count: 1,
        },
    ],
    recent_purchases: [
        {
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            supplier_name: 'Malom Kft.',
            quantity: 10,
            unit: 'kg',
            unit_cost: 360,
            line_total: 3600,
            purchase_date: '2026-04-24',
        },
    ],
    minimum_stock_recommendations: [
        {
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            unit: 'kg',
            current_stock: 2,
            minimum_stock: 5,
            weekly_average_consumption: 7,
            days_on_hand: 2,
            suggested_order_quantity: 12,
            urgency: 'critical',
        },
    ],
    weekly_consumption_forecast: [
        {
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            unit: 'kg',
            last_week_consumption: 7,
            four_week_average: 7,
            next_week_forecast: 7,
            coverage_days: 2,
        },
    ],
    alerts: [
        {
            type: 'price_increase',
            severity: 'high',
            ingredient_id: 1,
            ingredient_name: 'Liszt',
            message: 'Az utolsó beszerzési ár legalább 10%-kal emelkedett.',
            context: {},
        },
    ],
};

const mountPage = (overrides = {}) =>
    mount(IndexPage, {
        props: {
            filters: { days: 30, ingredient_id: null, supplier_id: null, urgency: '', alert_type: '' },
            dashboard: { ...dashboard, ...overrides },
            filter_options: filterOptions,
        },
    });

describe('Admin Procurement Intelligence page', () => {
    it('renders dashboard cards, tables and alert panel', () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain('Beszerzési intelligencia');
        expect(wrapper.text()).toContain('Aktív figyelmeztetés');
        expect(wrapper.text()).toContain('Beszállítói ártrend');
        expect(wrapper.text()).toContain('Ingredient költség-idősor');
        expect(wrapper.text()).toContain('Utánrendelési javaslat');
        expect(wrapper.text()).toContain('Heti várható fogyás');
        expect(wrapper.text()).toContain('Beszerzési figyelmeztetések');
        expect(wrapper.text()).toContain('Liszt');
        expect(wrapper.text()).toContain('Kritikus');
    });

    it('renders empty states', () => {
        const wrapper = mountPage({
            supplier_price_trends: [],
            ingredient_cost_trends: [],
            recent_purchases: [],
            minimum_stock_recommendations: [],
            weekly_consumption_forecast: [],
            alerts: [],
            summary: {
                alerts_count: 0,
                critical_minimum_stock_count: 0,
                price_increase_count: 0,
                stockout_risk_count: 0,
            },
        });

        expect(wrapper.text()).toContain('Nincs beszerzési ártrend');
        expect(wrapper.text()).toContain('Nincs utánrendelési javaslat');
        expect(wrapper.text()).toContain('Nincs beszerzési figyelmeztetés');
        expect(wrapper.text()).toContain('Nincs production_out fogyási adat');
    });
});
