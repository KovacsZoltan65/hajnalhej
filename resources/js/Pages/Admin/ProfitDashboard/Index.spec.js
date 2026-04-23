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
                        period_margin_rate: 50,
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
                                estimated_profit: 2100,
                                margin_rate: 58.33,
                                orders_count: 1,
                            },
                        ],
                    },
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
