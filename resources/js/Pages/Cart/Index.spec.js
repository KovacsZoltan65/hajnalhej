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
    default: { name: 'Button', props: ['label'], template: '<button>{{ label }}<slot /></button>' },
}));

vi.mock('primevue/inputnumber', () => ({
    default: { name: 'InputNumber', template: '<input />' },
}));

const translations = {
    'cart.page.meta_title': 'Kosár',
    'cart.page.title': 'Kosár',
    'cart.page.subtitle': 'Itt ellenőrizheted a kívánt termékeket, majd továbbléphetsz a pénztárhoz.',
    'cart.page.empty_title': 'A kosarad jelenleg üres',
    'cart.page.empty_description': 'Válassz a heti kínálatból, és tedd be a kedvenceidet.',
    'cart.page.view_weekly_menu': 'Heti menü megtekintése',
    'cart.page.unit_piece': 'db',
    'cart.page.remove_item': 'Törlés',
    'cart.page.line_total': 'Részösszeg',
    'cart.page.summary_title': 'Összegzés',
    'cart.page.items_label': 'Tételek',
    'cart.page.quantity_count': ':count db',
    'cart.page.total_label': 'Végösszeg',
    'cart.page.proceed_to_checkout': 'Tovább a pénztárhoz',
    'cart.page.clear_cart': 'Kosár ürítése',
};

const translate = (key, replacements = {}) => {
    let value = translations[key] ?? key;

    Object.entries(replacements).forEach(([name, replacement]) => {
        value = value.replace(`:${name}`, replacement);
    });

    return value;
};

const mountCartPage = (cart) =>
    mount(CartPage, {
        global: {
            mocks: {
                $t: translate,
            },
        },
        props: {
            cart,
        },
    });

describe('Cart page', () => {
    it('renders empty cart state', () => {
        const wrapper = mountCartPage({
            items: [],
            summary: {
                is_empty: true,
                total: 0,
                total_quantity: 0,
            },
        });

        expect(wrapper.text()).toContain('A kosarad jelenleg üres');
        expect(wrapper.text()).toContain('Heti menü megtekintése');
    });

    it('renders cart items and totals', () => {
        const wrapper = mountCartPage({
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
        });

        expect(wrapper.text()).toContain('Kovaszos vekni');
        expect(wrapper.text()).toContain('Végösszeg');
        expect(wrapper.text()).toContain('2 db');
        expect(wrapper.text()).toContain('Kosár ürítése');
    });
});
