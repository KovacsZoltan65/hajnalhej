import { mount } from '@vue/test-utils';
import ProductionPlansIndex from './Index.vue';

const { translate, routerGet, routerDelete, confirmRequire } = vi.hoisted(() => {
    const translations = {
        'common.all': 'Mind',
        'common.search': 'Keresés',
        'common.clear_filters': 'Szűrők törlése',
        'common.cancel': 'Mégse',
        'common.delete': 'Törlés',
        'common.actions': 'Műveletek',
        'admin_production_plans.meta_title': 'Gyártási tervek',
        'admin_production_plans.eyebrow': 'Admin / Gyártástervezés',
        'admin_production_plans.title': 'Gyártástervező',
        'admin_production_plans.description':
            'Célidő alapú gyártástervezés: mennyiségek, időzítés és összesített alapanyagigény egy helyen.',
        'admin_production_plans.summary.total_plans': 'Tervek',
        'admin_production_plans.summary.ready_plans': 'Kész',
        'admin_production_plans.summary.draft_plans': 'Piszkozat',
        'admin_production_plans.summary.total_recipe_minutes': 'Össz receptidő',
        'admin_production_plans.filters.search': 'Keresés',
        'admin_production_plans.filters.search_placeholder': 'Tervszám vagy termék',
        'admin_production_plans.filters.status': 'Státusz',
        'admin_production_plans.filters.target_from': 'Célidő -tól',
        'admin_production_plans.filters.target_to': 'Célidő -ig',
        'admin_production_plans.filters.per_page': 'Találat / oldal',
        'admin_production_plans.filters.per_page_option': ':count / oldal',
        'admin_production_plans.columns.plan': 'Terv',
        'admin_production_plans.columns.target_time': 'Célidő',
        'admin_production_plans.columns.planned_start': 'Javasolt kezdés',
        'admin_production_plans.columns.status': 'Státusz',
        'admin_production_plans.columns.total_time_minutes': 'Teljes idő (perc)',
        'admin_production_plans.columns.items_count': 'Tétel db',
        'admin_production_plans.actions.create': 'Új gyártási terv',
        'admin_production_plans.actions.edit': 'Gyártási terv szerkesztése',
        'admin_production_plans.actions.delete': 'Gyártási terv törlése',
        'admin_production_plans.empty': 'Nincs gyártási terv.',
        'admin_production_plans.units.minutes': ':count perc',
    };

    return {
        routerGet: vi.fn(),
        routerDelete: vi.fn(),
        confirmRequire: vi.fn(),
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
    router: { get: routerGet, delete: routerDelete },
    useForm: (values) => ({
        ...values,
        errors: {},
        processing: false,
        clearErrors: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
    }),
}));

vi.mock('laravel-vue-i18n', () => ({
    trans: translate,
}));

vi.mock('primevue/useconfirm', () => ({
    useConfirm: () => ({ require: confirmRequire }),
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('@/Components/Admin/ProductionPlans/CreateModal.vue', () => ({
    default: { template: '<div />' },
}));

vi.mock('@/Components/Admin/ProductionPlans/EditModal.vue', () => ({
    default: { template: '<div />' },
}));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    Button: {
        props: ['label', 'ariaLabel'],
        emits: ['click'],
        template: '<button type="button" :aria-label="ariaLabel" @click="$emit(\'click\')">{{ label }}<slot /></button>',
    },
    Column: {
        props: ['header'],
        template: '<div>{{ header }}<slot /></div>',
    },
    ConfirmDialog: {
        template: '<div />',
    },
    DataTable: {
        props: ['value'],
        template: '<div><slot name="empty" /><div v-for="row in value" :key="row.id">{{ row.plan_number }}</div><slot /></div>',
    },
    InputText: {
        props: ['modelValue', 'placeholder'],
        template: '<input :value="modelValue" :placeholder="placeholder" />',
    },
    SectionTitle: {
        props: ['eyebrow', 'title', 'description'],
        template: '<section>{{ eyebrow }} {{ title }} {{ description }}</section>',
    },
    Select: {
        props: ['modelValue', 'options', 'optionLabel', 'optionValue'],
        template: '<select />',
    },
};

describe('Admin Production Plans Index', () => {
    it('renders localized production planning controls and table headings', () => {
        const wrapper = mount(ProductionPlansIndex, {
            props: {
                productionPlans: {
                    data: [
                        {
                            id: 1,
                            plan_number: 'PLAN-001',
                            status: 'draft',
                            items: [],
                            details: {},
                        },
                    ],
                    current_page: 1,
                    per_page: 10,
                    total: 1,
                },
                products: [{ id: 1, name: 'Kovászos kenyér' }],
                statuses: [{ value: 'draft', label: 'Piszkozat' }],
                filters: {},
                summary: {
                    total_plans: 3,
                    ready_plans: 1,
                    draft_plans: 2,
                    total_recipe_minutes: 420,
                },
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        expect(wrapper.text()).toContain('Admin / Gyártástervezés');
        expect(wrapper.text()).toContain('Gyártástervező');
        expect(wrapper.text()).toContain('Össz receptidő');
        expect(wrapper.text()).toContain('420 perc');
        expect(wrapper.find('input[placeholder="Tervszám vagy termék"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('Új gyártási terv');
        expect(wrapper.text()).toContain('Javasolt kezdés');
        expect(wrapper.text()).toContain('PLAN-001');
    });
});
