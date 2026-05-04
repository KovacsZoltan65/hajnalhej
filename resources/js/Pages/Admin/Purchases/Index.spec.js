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
        clearErrors: vi.fn(),
    }),
}));

vi.mock('primevue/useconfirm', () => ({
    useConfirm: () => ({
        require: vi.fn(),
    }),
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('@/Components/Admin/AdminTableToolbar.vue', () => ({
    default: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
}));

vi.mock('@/Components/Admin/Purchases/CreateModal.vue', () => ({
    default: {
        props: ['visible'],
        template: '<div v-if="visible">create modal</div>',
    },
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
vi.mock('primevue/datatable', () => ({ default: { template: '<div><slot /><slot name="empty" /></div>' } }));
vi.mock('primevue/column', () => ({
    default: { template: '<div><slot name="body" :data="{ id: 1, status: \'draft\' }" /></div>' },
}));
vi.mock('primevue/confirmdialog', () => ({ default: { template: '<div />' } }));
vi.mock('@/Components/SectionTitle.vue', () => ({ default: { props: ['title'], template: '<div>{{ title }}</div>' } }));

describe('Admin purchases page', () => {
    it('renders purchases table and create button', () => {
        const wrapper = mount(IndexPage, {
            props: {
                purchases: { data: [], current_page: 1, per_page: 10, total: 0 },
                suppliers: [],
                ingredient_options: [],
                statuses: ['draft', 'posted', 'cancelled'],
                filters: {},
            },
        });

        expect(wrapper.text()).toContain('Beszerzések');
        expect(wrapper.text()).toContain('Nincs megjeleníthető beszerzés.');
    });
});
