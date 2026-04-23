import { mount } from '@vue/test-utils';
import IndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    router: { get: vi.fn() },
    useForm: (data) => ({
        ...data,
        processing: false,
        errors: {},
        post: vi.fn(),
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

describe('Admin inventory page', () => {
    it('renders summary cards and ledger section', () => {
        const wrapper = mount(IndexPage, {
            props: {
                dashboard: {
                    summary: {
                        total_stock_value: 1000,
                        low_stock_count: 1,
                        out_of_stock_count: 0,
                        weekly_waste_cost: 50,
                        weekly_purchase_value: 300,
                    },
                },
                ledger: { data: [] },
                filters: {},
                movement_types: [],
                ingredient_options: [],
                waste_reasons: [],
            },
        });

        expect(wrapper.text()).toContain('Készlet dashboard');
        expect(wrapper.text()).toContain('Készletmozgás főkönyv');
    });
});
