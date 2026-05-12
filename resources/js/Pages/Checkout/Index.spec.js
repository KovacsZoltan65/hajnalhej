import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import CheckoutPage from './Index.vue';

const translations = {
    'checkout.page.meta_title': 'Pénztár',
    'checkout.page.title': 'Pénztár',
    'checkout.page.subtitle': 'Töltsd ki az adatokat, ellenőrizd az összegzést, és add le a rendelést.',
    'checkout.page.customer_name': 'Teljes név',
    'checkout.page.customer_email': 'Email',
    'checkout.page.customer_phone': 'Telefonszám',
    'checkout.page.pickup_date': 'Átvétel dátuma',
    'checkout.page.pickup_time_slot': 'Átvételi idősáv',
    'checkout.page.pickup_time_placeholder': 'pl. 08:00-10:00',
    'checkout.page.notes': 'Megjegyzés',
    'checkout.page.accept_privacy': 'Elfogadom az adatkezelési tájékoztatót.',
    'checkout.page.accept_terms': 'Elfogadom az ÁSZF-et.',
    'checkout.page.submit_order': 'Rendelés leadása',
    'checkout.page.order_summary': 'Rendelés összegzése',
    'checkout.page.total_label': 'Végösszeg',
    'checkout.page.back_to_cart': 'Vissza a kosárhoz',
};

const translate = (key) => translations[key] ?? key;

let formState = reactive({
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    notes: '',
    pickup_date: null,
    pickup_time_slot: null,
    accept_privacy: false,
    accept_terms: false,
    errors: {},
    processing: false,
    post: vi.fn(),
});

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    useForm: () => formState,
}));

vi.mock('../../Layouts/PublicLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: {
        name: 'Button',
        props: ['disabled', 'label'],
        template: '<button :disabled="disabled">{{ label }}</button>',
    },
}));
vi.mock('primevue/inputtext', () => ({
    default: { name: 'InputText', template: '<input />' },
}));
vi.mock('primevue/inputmask', () => ({
    default: { name: 'InputMask', template: '<input />' },
}));
vi.mock('primevue/textarea', () => ({
    default: { name: 'Textarea', template: '<textarea />' },
}));
vi.mock('primevue/checkbox', () => ({
    default: { name: 'Checkbox', template: '<input type="checkbox" />' },
}));

describe('Checkout page', () => {
    beforeEach(() => {
        formState = reactive({
            customer_name: '',
            customer_email: '',
            customer_phone: '',
            notes: '',
            pickup_date: null,
            pickup_time_slot: null,
            accept_privacy: false,
            accept_terms: false,
            errors: {},
            processing: false,
            post: vi.fn(),
        });
    });

    it('renders order summary', () => {
        const wrapper = mount(CheckoutPage, {
            props: {
                cart: {
                    items: [
                        { product_id: 1, name: 'Kovaszos vekni', quantity: 1, unit_price: 1200, line_total: 1200 },
                    ],
                    summary: { total: 1200 },
                },
                prefill: {
                    customer_name: 'Teszt',
                    customer_email: 'teszt@example.com',
                    customer_phone: '',
                    notes: '',
                    pickup_date: null,
                    pickup_time_slot: null,
                },
            },
            global: {
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.text()).toContain('Rendelés összegzése');
        expect(wrapper.text()).toContain('Végösszeg');
        expect(wrapper.text()).toContain('Vissza a kosárhoz');
        expect(wrapper.text()).toContain('Kovaszos vekni');
    });

    it('disables submit while processing', () => {
        formState.processing = true;

        const wrapper = mount(CheckoutPage, {
            props: {
                cart: { items: [], summary: { total: 0 } },
                prefill: {
                    customer_name: '',
                    customer_email: '',
                    customer_phone: '',
                    notes: '',
                    pickup_date: null,
                    pickup_time_slot: null,
                },
            },
            global: {
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.find('button').attributes('disabled')).toBeDefined();
    });
});
