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
        reset: vi.fn(),
        clearErrors: vi.fn(),
    }),
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('@/Components/Admin/AdminTableToolbar.vue', () => ({
    default: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
}));

vi.mock('@/Components/Admin/Inventory/WasteEntryModal.vue', () => ({
    default: {
        props: ['visible'],
        template: '<div v-if="visible">waste modal</div>',
    },
}));

vi.mock('@/Components/Admin/Inventory/AdjustmentModal.vue', () => ({
    default: {
        props: ['visible'],
        template: '<div v-if="visible">adjustment modal</div>',
    },
}));

vi.mock('primevue/button', () => ({ default: { template: '<button><slot /></button>' } }));
vi.mock('primevue/inputtext', () => ({ default: { template: '<input />' } }));
vi.mock('primevue/datepicker', () => ({
    default: {
        props: ['modelValue'],
        emits: ['update:modelValue'],
        template: '<div class="datepicker-stub"></div>',
    },
}));
vi.mock('primevue/select', () => ({
    default: {
        props: ['modelValue', 'options'],
        emits: ['update:modelValue'],
        template: '<div class="select-stub"></div>',
    },
}));
vi.mock('primevue/datatable', () => ({ default: { template: '<div><slot /><slot name="empty" /></div>' } }));
vi.mock('primevue/column', () => ({ default: { template: '<div><slot name="body" :data="{}" /></div>' } }));
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
                ledger: { data: [], current_page: 1, per_page: 15, total: 0 },
                filters: {},
                movement_types: [],
                ingredient_options: [],
                product_options: [],
                waste_reasons: [],
            },
        });

        expect(wrapper.text()).toContain('Készletmozgások');
        expect(wrapper.text()).toContain('Nincs megjeleníthető készletmozgás.');
    });
});
