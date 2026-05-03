import { mount } from '@vue/test-utils';
import OrdersIndexPage from './Index.vue';

const { translate } = vi.hoisted(() => {
    const translations = {
        'admin_orders.filters.per_page_option': ':count / oldal',
        'admin_orders.meta_title': 'Rendelések',
        'admin_orders.eyebrow': 'Admin / Rendelések',
        'admin_orders.title': 'Rendelések',
        'admin_orders.description':
            'Teljes rendelési lista állapotokkal, kereséssel, szűréssel és részletekkel.',
        'admin_orders.filters.search': 'Keresés',
        'admin_orders.filters.search_placeholder': 'Rendelésszám vagy ügyfél',
        'admin_orders.filters.status': 'Státusz',
        'admin_orders.filters.per_page': 'Találat / oldal',
        'admin_orders.columns.identifier': 'Azonosító',
        'admin_orders.columns.customer': 'Vásárló',
        'admin_orders.columns.status': 'Státusz',
        'admin_orders.columns.pickup': 'Átvétel',
        'admin_orders.columns.total': 'Végösszeg',
        'admin_orders.actions.search': 'Keresés',
        'admin_orders.actions.details': 'Részletek',
        'admin_orders.empty': 'Nincs megjeleníthető rendelés.',
        'common.all': 'Mind',
        'common.clear_filters': 'Szűrők törlése',
        'common.actions': 'Műveletek',
        'common.locale': 'hu-HU',
        'common.currency': 'HUF',
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
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn() },
}));

vi.mock('laravel-vue-i18n', () => ({
    trans: translate,
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: { props: ['label'], template: '<button>{{ label }}<slot /></button>' },
}));
vi.mock('primevue/inputtext', () => ({
    default: { props: ['placeholder'], template: '<input :placeholder="placeholder" />' },
}));
vi.mock('primevue/select', () => ({
    default: { template: '<div />' },
}));
vi.mock('primevue/datatable', () => ({
    default: {
        props: ['value'],
        template: '<div><slot name="empty" /><div v-for="row in value" :key="row.id">{{ row.order_number }} {{ row.customer_name }}</div><slot /></div>',
    },
}));
vi.mock('primevue/column', () => ({
    default: { props: ['header'], template: '<div>{{ header }}<slot /></div>' },
}));

const stubs = {
    AdminTableToolbar: {
        template: '<div><slot name="filters" /><slot name="actions" /></div>',
    },
    OrderStatusBadge: {
        props: ['status'],
        template: '<span>{{ status }}</span>',
    },
    SectionTitle: {
        props: ['eyebrow', 'title', 'description'],
        template: '<section>{{ eyebrow }} {{ title }} {{ description }}</section>',
    },
};

describe('Admin Orders Index', () => {
    it('renders localized order list controls and rows', () => {
        const wrapper = mount(OrdersIndexPage, {
            props: {
                orders: {
                    data: [
                        {
                            id: 1,
                            order_number: 'ORD-001',
                            customer_name: 'Teszt Elek',
                            customer_email: 'teszt@example.com',
                            status: 'pending',
                            pickup_date: '2026-05-02',
                            pickup_time_slot: '08:00-10:00',
                            total: 4500,
                        },
                    ],
                    current_page: 1,
                    per_page: 15,
                    total: 1,
                },
                statusOptions: ['pending'],
                filters: {},
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        expect(wrapper.text()).toContain('Admin / Rendelések');
        expect(wrapper.text()).toContain('Keresés');
        expect(wrapper.text()).toContain('Vásárló');
        expect(wrapper.text()).toContain('ORD-001');
        expect(wrapper.text()).toContain('Műveletek');
    });
});
