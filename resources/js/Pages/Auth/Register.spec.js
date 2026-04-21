import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import RegisterPage from './Register.vue';

const mockPost = vi.fn();
const mockReset = vi.fn();

let formState = reactive({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    errors: {},
    processing: false,
    post: mockPost,
    reset: mockReset,
});

vi.mock('@inertiajs/vue3', () => ({
    Head: {
        name: 'Head',
        props: ['title'],
        template: '<span><slot /></span>',
    },
    Link: {
        name: 'Link',
        props: ['href'],
        template: '<a :href="href"><slot /></a>',
    },
    usePage: () => ({
        props: {
            ui: {
                register: {
                    title: 'Hozd letre a fiokodat',
                    subtitle: 'Regisztralj, hogy gyorsabban rendelhesd kedvenceidet.',
                    cta: 'Fiok letrehozasa',
                },
            },
        },
    }),
    useForm: () => formState,
}));

vi.mock('../../Layouts/PublicLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/inputtext', () => ({
    default: {
        name: 'InputText',
        props: ['modelValue', 'invalid'],
        emits: ['update:modelValue'],
        template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
}));

vi.mock('primevue/password', () => ({
    default: {
        name: 'Password',
        props: ['modelValue', 'invalid'],
        emits: ['update:modelValue'],
        template: '<input :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    },
}));

vi.mock('primevue/button', () => ({
    default: {
        name: 'Button',
        props: ['label', 'loading', 'disabled'],
        template: '<button :disabled="disabled">{{ label }}</button>',
    },
}));

describe('Register page', () => {
    beforeEach(() => {
        mockPost.mockReset();
        mockReset.mockReset();

        formState = reactive({
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            errors: {},
            processing: false,
            post: mockPost,
            reset: mockReset,
        });
    });

    it('renders registration fields and CTA', () => {
        const wrapper = mount(RegisterPage);

        expect(wrapper.text()).toContain('Hozd letre a fiokodat');
        expect(wrapper.text()).toContain('Fiok letrehozasa');
        expect(wrapper.find('#name').exists()).toBe(true);
        expect(wrapper.find('#email').exists()).toBe(true);
        expect(wrapper.find('#password').exists()).toBe(true);
        expect(wrapper.find('#password_confirmation').exists()).toBe(true);
    });

    it('disables submit while processing', () => {
        formState.processing = true;

        const wrapper = mount(RegisterPage);

        expect(wrapper.find('button').attributes('disabled')).toBeDefined();
    });
});
