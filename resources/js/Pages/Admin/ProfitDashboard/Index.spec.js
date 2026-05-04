import { mount } from '@vue/test-utils';
import IndexPage from './Index.vue';

const { translate } = vi.hoisted(() => {
    const translations = {
        'common.locale': 'hu-HU',
        'common.currency': 'HUF',
        'common.day_count': ':count nap',
        'common.product': 'Termék',
        'common.piece': 'Darab',
        'common.date': 'Dátum',
        'common.order': 'Rendelés',
        'admin_profit_dashboard.meta_title': 'Profit irányítópult',
        'admin_profit_dashboard.title': 'Profit irányítópult',
        'admin_profit_dashboard.description':
            'Recept/BOM alapú becsült önköltség, termék margin és rendelési profit trend.',
        'admin_profit_dashboard.summary.estimated_cost_total': 'Becsült önköltség (katalógus)',
        'admin_profit_dashboard.summary.catalog_value_total': 'Katalógus érték',
        'admin_profit_dashboard.summary.period_estimated_profit': 'Becsült profit (időszak)',
        'admin_profit_dashboard.summary.period_actual_material_cost': 'Valós anyagköltség (időszak)',
        'admin_profit_dashboard.summary.period_waste_cost': 'Selejt költség (időszak)',
        'admin_profit_dashboard.summary.period_gross_profit': 'Bruttó profit (valós)',
        'admin_profit_dashboard.summary.period_margin_rate': 'Margin % (valós)',
        'admin_profit_dashboard.summary.estimated_vs_actual_delta': 'Becsült és valós eltérés',
        'admin_profit_dashboard.product_margins.title': 'Termék margin (BOM becslés)',
        'admin_profit_dashboard.top_profit_products.title': 'Top profit termékek (időszak)',
        'admin_profit_dashboard.order_profit_trend.title': 'Rendelési profit trend',
        'admin_profit_dashboard.columns.price': 'Ár',
        'admin_profit_dashboard.columns.estimated_cost': 'Becsült költség',
        'admin_profit_dashboard.columns.margin': 'Margin',
        'admin_profit_dashboard.columns.margin_rate': 'Margin %',
        'admin_profit_dashboard.columns.bom_items': 'BOM tétel',
        'admin_profit_dashboard.columns.revenue': 'Bevétel',
        'admin_profit_dashboard.columns.estimated_profit': 'Becsült profit',
        'admin_profit_dashboard.columns.actual_material_cost': 'Valós anyagköltség',
        'admin_profit_dashboard.columns.gross_profit': 'Bruttó profit',
    };

    return {
        translate: (key, params = {}) =>
            Object.entries(params).reduce(
                (text, [param, replacement]) => text.replace(`:${param}`, String(replacement)),
                translations[key] ?? key
            ),
    };
});

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    router: { get: vi.fn() },
}));

vi.mock('laravel-vue-i18n', () => ({
    trans: translate,
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

describe('Admin Profit Dashboard page', () => {
    it('renders margin, top profit and trend sections', () => {
        const wrapper = mount(IndexPage, {
            props: {
                filters: { days: 30 },
                dashboard: {
                    summary: {
                        estimated_cost_total: 5000,
                        catalog_value_total: 9000,
                        potential_margin_total: 4000,
                        products_with_recipe: 2,
                        period_revenue: 12000,
                        period_estimated_cost: 6000,
                        period_estimated_profit: 6000,
                        period_actual_material_cost: 5500,
                        period_waste_cost: 250,
                        period_gross_profit: 6250,
                        period_margin_rate: 50,
                        estimated_vs_actual_delta: 500,
                    },
                    product_margins: [
                        {
                            product_id: 1,
                            product_name: 'Vajas kalács',
                            product_price: 1200,
                            estimated_unit_cost: 500,
                            margin_amount: 700,
                            margin_rate: 58.33,
                            bom_items: 2,
                        },
                    ],
                    top_profit_products: [
                        {
                            product_id: 1,
                            product_name: 'Vajas kalács',
                            revenue: 3600,
                            estimated_cost: 1500,
                            estimated_profit: 2100,
                            margin_rate: 58.33,
                            quantity: 3,
                            orders: 1,
                        },
                    ],
                    order_profit_trend: {
                        points: [
                            {
                                date: '2026-04-23',
                                revenue: 3600,
                                estimated_cost: 1500,
                                actual_material_cost: 1400,
                                gross_profit: 2200,
                                estimated_profit: 2100,
                                margin_rate: 58.33,
                                orders_count: 1,
                            },
                        ],
                    },
                },
            },
            global: {
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.text()).toContain('Profit irányítópult');
        expect(wrapper.text()).toContain('Termék margin');
        expect(wrapper.text()).toContain('Top profit termékek');
        expect(wrapper.text()).toContain('Rendelési profit trend');
        expect(wrapper.text()).toContain('Vajas kalács');
    });
});
