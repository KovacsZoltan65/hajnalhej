import { mount } from '@vue/test-utils';
import { reactive } from 'vue';
import IngredientSupplierTermsIndex from './Index.vue';

const { translate } = vi.hoisted(() => {
    const translations = {
        'admin_supplier_terms.filters.per_page_option': ':count / oldal',
        'admin_supplier_terms.meta_title': 'Beszállítói feltételek',
        'admin_supplier_terms.eyebrow': 'Admin / Beszerzés',
        'admin_supplier_terms.title': 'Beszállítói feltételek',
        'admin_supplier_terms.description':
            'Alapanyag-beszállító feltételek, preferált források és rendelési paraméterek kezelése.',
        'admin_supplier_terms.filters.search': 'Keresés',
        'admin_supplier_terms.filters.search_placeholder': 'Alapanyag, beszállító vagy SKU',
        'admin_supplier_terms.filters.status': 'Státusz',
        'admin_supplier_terms.filters.per_page': 'Találat / oldal',
        'admin_supplier_terms.columns.ingredient': 'Alapanyag',
        'admin_supplier_terms.columns.supplier': 'Beszállító',
        'admin_supplier_terms.columns.lead_time': 'Lead time',
        'admin_supplier_terms.columns.minimum': 'Minimum',
        'admin_supplier_terms.columns.pack_size': 'Kiszerelés',
        'admin_supplier_terms.columns.unit_cost_override': 'Egyedi ár',
        'admin_supplier_terms.columns.preferred': 'Preferált',
        'admin_supplier_terms.columns.status': 'Státusz',
        'admin_supplier_terms.actions.search': 'Keresés',
        'admin_supplier_terms.actions.create': 'Új feltétel',
        'admin_supplier_terms.actions.edit': 'Beszállítói feltétel szerkesztése',
        'admin_supplier_terms.actions.delete': 'Beszállítói feltétel törlése',
        'admin_supplier_terms.empty': 'Nincs megjeleníthető beszállítói feltétel.',
        'admin_supplier_terms.confirm_delete_header': 'Beszállítói feltétel törlése',
        'admin_supplier_terms.confirm_delete_message':
            'Biztosan törlöd ezt a feltételt: :ingredient / :supplier?',
        'common.all': 'Mind',
        'common.active': 'Aktív',
        'common.inactive': 'Inaktív',
        'common.cancel': 'Mégse',
        'common.delete': 'Törlés',
        'common.clear_filters': 'Szűrők törlése',
        'common.actions': 'Műveletek',
        'common.locale': 'hu-HU',
        'common.currency': 'HUF',
        'common.day_count': ':count nap',
    };

    return {
        translate: (key, replacements = {}) => {
            let value = translations[key] ?? key;

            Object.entries(replacements).forEach(([name, replacement]) => {
                value = value.replace(`:${name}`, replacement);
            });

            return value;
        },
    };
});

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

vi.mock('laravel-vue-i18n', () => ({
    trans: translate,
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
    SectionTitle: {
        props: ['eyebrow', 'title', 'description'],
        template: '<section>{{ eyebrow }} {{ title }} {{ description }}</section>',
    },
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
            global: {
                stubs,
                mocks: {
                    $t: translate,
                },
            },
        });

        expect(wrapper.text()).toContain('Beszállítói feltételek');
        expect(wrapper.text()).toContain('Keresés');
        expect(wrapper.text()).toContain('Buzaliszt');
        expect(wrapper.text()).toContain('Malom Kft.');

        await wrapper.findAll('button').find((button) => button.text() === 'Új feltétel').trigger('click');

        expect(wrapper.text()).toContain('Új beszállítói feltétel');
    });
});
