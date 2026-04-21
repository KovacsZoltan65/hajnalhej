import { mount } from '@vue/test-utils';
import CartPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { patch: vi.fn(), delete: vi.fn() },
}));

vi.mock('../../Layouts/PublicLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: { name: 'Button', template: '<button><slot /></button>' },
}));

vi.mock('primevue/inputnumber', () => ({
    default: { name: 'InputNumber', template: '<input />' },
}));

describe('Cart page', () => {
    it('renders empty cart state', () => {
        const wrapper = mount(CartPage, {
            props: {
                cart: {
                    items: [],
                    summary: {
                        is_empty: true,
                        total: 0,
                        total_quantity: 0,
                    },
                },
            },
        });

        expect(wrapper.text()).toContain('A kosarad jelenleg ures');
    });

    it('renders cart items and totals', () => {
        const wrapper = mount(CartPage, {
            props: {
                cart: {
                    items: [
                        {
                            product_id: 1,
                            name: 'Kovaszos vekni',
                            short_description: 'Ropogos hej',
                            unit_price: 1200,
                            quantity: 2,
                            line_total: 2400,
                        },
                    ],
                    summary: {
                        is_empty: false,
                        total: 2400,
                        total_quantity: 2,
                    },
                },
            },
        });

        expect(wrapper.text()).toContain('Kovaszos vekni');
        expect(wrapper.text()).toContain('Vegosszeg');
    });
});
