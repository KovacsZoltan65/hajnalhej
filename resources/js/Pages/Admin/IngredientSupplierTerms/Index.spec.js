import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import IngredientSupplierTermsIndex from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    router: { get: vi.fn(), delete: vi.fn() },
    useForm: (initial) =>
        reactive({
            ...initial,
            errors: {},
            processing: false,
            clearErrors: vi.fn(),
            reset: vi.fn(),
            post: vi.fn(),
            put: vi.fn(),
        }),
}));

vi.mock('primevue/useconfirm', () => ({
    useConfirm: () => ({ require: vi.fn() }),
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

const stubs = {
    AdminTableToolbar: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
    Button: { props: ['label'], emits: ['click'], template: '<button @click="$emit(\'click\')">{{ label }}</button>' },
    Column: { template: '<div><slot /></div>' },
    ConfirmDialog: { template: '<div />' },
    DataTable: {
        props: ['value'],
        template: '<div><div v-for="row in value" :key="row.id">{{ row.ingredient_name }} {{ row.supplier_name }}</div><slot /></div>',
    },
    InputText: { template: '<input />' },
    Select: { template: '<select />' },
    CreateModal: { props: ['visible'], template: '<div v-if="visible">Új beszállítói feltétel</div>' },
    EditModal: { props: ['visible'], template: '<div v-if="visible">Beszállítói feltétel szerkesztése</div>' },
    PreferredBadge: { props: ['preferred', 'active'], template: '<span>{{ preferred && active ? "Preferált" : "Normál" }}</span>' },
    SectionTitle: { props: ['title'], template: '<h1>{{ title }}</h1>' },
};

describe('IngredientSupplierTerms Index', () => {
    const props = {
        terms: {
            data: [
                {
                    id: 1,
                    ingredient_id: 1,
                    supplier_id: 1,
                    ingredient_name: 'Buzaliszt',
                    ingredient_unit: 'kg',
                    supplier_name: 'Malom Kft.',
                    lead_time_days: 2,
                    minimum_order_quantity: '25.000',
                    pack_size: '5.000',
                    unit_cost_override: '280.00',
                    preferred: true,
                    active: true,
                    meta: null,
                },
            ],
            current_page: 1,
            per_page: 10,
            total: 1,
        },
        filters: {
            search: '',
            active: '',
            sort_field: 'ingredient',
            sort_direction: 'asc',
            per_page: 10,
        },
        ingredients: [{ id: 1, name: 'Buzaliszt', unit: 'kg' }],
        suppliers: [{ id: 1, name: 'Malom Kft.' }],
    };

    it('renders the supplier terms list and opens create modal', async () => {
        const wrapper = mount(IngredientSupplierTermsIndex, {
            props,
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Beszállítói feltételek');
        expect(wrapper.text()).toContain('Buzaliszt');
        expect(wrapper.text()).toContain('Malom Kft.');

        await wrapper.findAll('button').find((button) => button.text() === 'Új feltétel').trigger('click');

        expect(wrapper.text()).toContain('Új beszállítói feltétel');
    });
});
