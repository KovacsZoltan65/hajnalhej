import { mount } from '@vue/test-utils';
import SuccessPage from './Success.vue';

const translations = {
    'orders.success.meta_title': 'Rendelés sikeres',
    'orders.success.brand_name': 'Hajnalhéj Bakery',
    'orders.success.title': 'Köszönjük a rendelésedet!',
    'orders.success.subtitle': 'A rendelésedet rögzítettük. Hamarosan visszaigazoljuk.',
    'orders.success.order_number': 'Rendelés azonosító',
    'orders.success.total_label': 'Végösszeg',
    'orders.success.pickup_date': 'Átvétel dátuma',
    'orders.success.pickup_time_slot': 'Átvételi idősáv',
    'orders.success.start_new_order': 'Újabb rendelés indítása',
    'orders.success.account': 'Fiókom',
};

const translate = (key) => translations[key] ?? key;

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', props: ['title'], template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
}));

vi.mock('../../Layouts/PublicLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('@/composables/useLocaleFormat', () => ({
    useLocaleFormat: () => ({
        formatCurrency: (value) => `${value} Ft`,
    }),
}));

describe('Order success page', () => {
    it('renders the localized success summary', () => {
        const wrapper = mount(SuccessPage, {
            props: {
                order: {
                    order_number: 'ORD-001',
                    total: 4200,
                    pickup_date: '2026-05-14',
                    pickup_time_slot: '08:00-10:00',
                },
            },
            global: {
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.text()).toContain('Köszönjük a rendelésedet!');
        expect(wrapper.text()).toContain('Rendelés azonosító');
        expect(wrapper.text()).toContain('ORD-001');
        expect(wrapper.text()).toContain('4200 Ft');
        expect(wrapper.text()).toContain('Újabb rendelés indítása');
        expect(wrapper.find('a[href="/account"]').text()).toBe('Fiókom');
    });
});
