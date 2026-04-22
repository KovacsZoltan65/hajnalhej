import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import CheckoutPage from './Index.vue';

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
        });

        expect(wrapper.text()).toContain('Rendelés összegzése');
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
        });

        expect(wrapper.find('button').attributes('disabled')).toBeDefined();
    });
});

