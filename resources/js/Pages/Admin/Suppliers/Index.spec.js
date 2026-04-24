import { mount } from '@vue/test-utils';
import IndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    router: { get: vi.fn(), delete: vi.fn() },
    useForm: (data) => ({
        ...data,
        processing: false,
        errors: {},
        reset: vi.fn(),
        clearErrors: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
    }),
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({ default: { template: '<button><slot /></button>' } }));
vi.mock('primevue/column', () => ({ default: { template: '<div><slot /></div>' } }));
vi.mock('primevue/confirmdialog', () => ({ default: { template: '<div />' } }));
vi.mock('primevue/datatable', () => ({ default: { template: '<div><slot name="empty" /><slot /></div>' } }));
vi.mock('primevue/inputtext', () => ({ default: { template: '<input />' } }));
vi.mock('primevue/select', () => ({
    default: {
        props: ['modelValue', 'options'],
        emits: ['update:modelValue'],
        template: '<div class="select-stub"></div>',
    },
}));
vi.mock('primevue/useconfirm', () => ({
    useConfirm: () => ({ require: vi.fn() }),
}));
vi.mock('@/Components/Admin/AdminTableToolbar.vue', () => ({
    default: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
}));
vi.mock('@/Components/Admin/Suppliers/CreateModal.vue', () => ({ default: { template: '<div />' } }));
vi.mock('@/Components/Admin/Suppliers/EditModal.vue', () => ({ default: { template: '<div />' } }));
vi.mock('@/Components/SectionTitle.vue', () => ({ default: { props: ['title'], template: '<div>{{ title }}</div>' } }));

describe('Admin suppliers page', () => {
    it('renders datatable shell and create action', () => {
        const wrapper = mount(IndexPage, {
            props: {
                suppliers: { data: [], current_page: 1, per_page: 10, total: 0 },
                filters: {},
            },
        });

        expect(wrapper.text()).toContain('Beszállítók');
        expect(wrapper.text()).toContain('Nincs megjeleníthető beszállító.');
    });
});
