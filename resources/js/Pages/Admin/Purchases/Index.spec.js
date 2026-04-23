import { mount } from '@vue/test-utils';
import IndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn(), post: vi.fn() },
    useForm: (data) => ({
        ...data,
        processing: false,
        errors: {},
        post: vi.fn(),
        reset: vi.fn(),
    }),
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({ default: { template: '<button><slot /></button>' } }));
vi.mock('primevue/inputtext', () => ({ default: { template: '<input />' } }));
vi.mock('primevue/select', () => ({
    default: {
        props: ['modelValue', 'options'],
        emits: ['update:modelValue'],
        template: '<div class="select-stub"></div>',
    },
}));
vi.mock('@/Components/SectionTitle.vue', () => ({ default: { props: ['title'], template: '<div>{{ title }}</div>' } }));

describe('Admin purchases page', () => {
    it('renders purchase list and create section', () => {
        const wrapper = mount(IndexPage, {
            props: {
                purchases: { data: [], current_page: 1, last_page: 1 },
                suppliers: [],
                ingredient_options: [],
                statuses: ['draft', 'posted', 'cancelled'],
                filters: {},
            },
        });

        expect(wrapper.text()).toContain('Dátum');
        expect(wrapper.text()).toContain('Új beszerzés');
    });
});
