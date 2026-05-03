import { mount } from '@vue/test-utils';
import CategoriesIndexPage from './Index.vue';

const { confirmRequire, translate } = vi.hoisted(() => {
    const translations = {
        'admin_categories.filters.per_page_option': ':count / oldal',
        'admin_categories.meta_title': 'Kategóriák',
        'admin_categories.eyebrow': 'Admin / Kategóriák',
        'admin_categories.title': 'Kategóriák',
        'admin_categories.description':
            'Referencia CRUD modul teljes repository-service-policy architektúrával.',
        'admin_categories.filters.search': 'Keresés',
        'admin_categories.filters.search_placeholder': 'Név vagy slug',
        'admin_categories.filters.per_page': 'Találat / oldal',
        'admin_categories.columns.name': 'Név',
        'admin_categories.columns.sort_order': 'Sorrend',
        'admin_categories.columns.products': 'Termékek',
        'admin_categories.columns.status': 'Státusz',
        'admin_categories.actions.search': 'Keresés',
        'admin_categories.actions.create': 'Új kategória',
        'admin_categories.actions.edit': 'Kategória szerkesztése',
        'admin_categories.actions.delete': 'Kategória törlése',
        'admin_categories.empty': 'Nincs megjeleníthető kategória.',
        'admin_categories.confirm_delete_header': 'Kategória törlése',
        'admin_categories.confirm_delete_message':
            'Biztosan törlöd ezt a kategóriát: :name?',
        'common.cancel': 'Mégse',
        'common.delete': 'Törlés',
        'common.clear_filters': 'Szűrők törlése',
        'common.actions': 'Műveletek',
    };

    return {
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
    router: { get: vi.fn(), delete: vi.fn() },
    useForm: (defaults) => ({
        ...defaults,
        errors: {},
        reset: vi.fn(),
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

vi.mock('primevue/button', () => ({
    default: {
        props: ['label', 'ariaLabel'],
        emits: ['click'],
        template: '<button :aria-label="ariaLabel" @click="$emit(\'click\')">{{ label }}</button>',
    },
}));
vi.mock('primevue/confirmdialog', () => ({
    default: { template: '<div />' },
}));
vi.mock('primevue/datatable', () => ({
    default: {
        props: ['value'],
        template: '<div><slot name="empty" /><div v-for="row in value" :key="row.id">{{ row.name }} {{ row.slug }}</div><slot /></div>',
    },
}));
vi.mock('primevue/column', () => ({
    default: { props: ['header'], template: '<div>{{ header }}<slot /></div>' },
}));
vi.mock('primevue/inputtext', () => ({
    default: { props: ['placeholder'], template: '<input :placeholder="placeholder" />' },
}));
vi.mock('primevue/select', () => ({
    default: { template: '<div />' },
}));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    CategoryStatusBadge: {
        props: ['active'],
        template: '<span>{{ active ? "active" : "inactive" }}</span>',
    },
    CreateModal: { template: '<div />' },
    EditModal: { template: '<div />' },
    SectionTitle: {
        props: ['eyebrow', 'title', 'description'],
        template: '<section>{{ eyebrow }} {{ title }} {{ description }}</section>',
    },
};

const mountPage = (categories = []) =>
    mount(CategoriesIndexPage, {
        props: {
            categories: {
                data: categories,
                current_page: 1,
                per_page: 10,
                total: categories.length,
            },
            filters: {
                search: '',
                sort_field: 'sort_order',
                sort_direction: 'asc',
                per_page: 10,
            },
        },
        global: {
            stubs,
            mocks: {
                $t: translate,
            },
        },
    });

describe('Admin Categories Index', () => {
    it('renders localized category table controls and rows', () => {
        const wrapper = mountPage([
            {
                id: 1,
                name: 'Kenyerek',
                slug: 'kenyerek',
                description: null,
                is_active: true,
                sort_order: 1,
                products_count: 3,
            },
        ]);

        expect(wrapper.text()).toContain('Admin / Kategóriák');
        expect(wrapper.text()).toContain('Keresés');
        expect(wrapper.text()).toContain('Új kategória');
        expect(wrapper.text()).toContain('Név');
        expect(wrapper.text()).toContain('Sorrend');
        expect(wrapper.text()).toContain('Kenyerek');
    });

    it('renders localized empty state', () => {
        const wrapper = mountPage();

        expect(wrapper.text()).toContain('Nincs megjeleníthető kategória.');
        expect(wrapper.text()).toContain('Szűrők törlése');
    });
});
