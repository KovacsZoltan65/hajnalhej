import { mount } from '@vue/test-utils';
import OrdersShowPage from './Show.vue';

const { translate } = vi.hoisted(() => {
    const translations = {
        'admin_orders.eyebrow': 'Admin / Rendelések',
        'admin_orders.show_meta_title': 'Rendelés :number',
        'admin_orders.show_title': 'Rendelés: :number',
        'admin_orders.show_description':
            'Rendelési adatok, tételek, státuszfrissítés és belső megjegyzések egy helyen.',
        'admin_orders.sections.customer_details': 'Ügyfél adatok',
        'admin_orders.sections.items': 'Rendelési tételek',
        'admin_orders.sections.actions': 'Műveletek',
        'admin_orders.fields.name': 'Név',
        'admin_orders.fields.email': 'Email',
        'admin_orders.fields.phone': 'Telefon',
        'admin_orders.fields.pickup': 'Átvétel',
        'admin_orders.fields.status': 'Státusz',
        'admin_orders.fields.internal_notes': 'Belső megjegyzés',
        'admin_orders.fields.pickup_date': 'Átvétel dátuma',
        'admin_orders.fields.pickup_time_slot': 'Átvételi idősáv',
        'admin_orders.fields.subtotal': 'Részösszeg',
        'admin_orders.fields.total': 'Végösszeg',
        'admin_orders.actions.update_status': 'Státusz frissítése',
        'admin_orders.placeholders.pickup_time_slot': 'pl. 08:00-10:00',
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
    useForm: (data) => ({
        ...data,
        errors: {},
        processing: false,
        patch: vi.fn(),
    }),
}));

vi.mock('laravel-vue-i18n', () => ({
    trans: translate,
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: { props: ['label'], template: '<button>{{ label }}</button>' },
}));
vi.mock('primevue/inputtext', () => ({
    default: { props: ['placeholder'], template: '<input :placeholder="placeholder" />' },
}));
vi.mock('primevue/select', () => ({
    default: { template: '<select />' },
}));
vi.mock('primevue/textarea', () => ({
    default: { template: '<textarea />' },
}));

const stubs = {
    OrderStatusBadge: { props: ['status'], template: '<span>{{ status }}</span>' },
    SectionTitle: {
        props: ['eyebrow', 'title', 'description'],
        template: '<section>{{ eyebrow }} {{ title }} {{ description }}</section>',
    },
};

describe('Admin Orders Show', () => {
    it('renders localized order detail sections', () => {
        const wrapper = mount(OrdersShowPage, {
            props: {
                order: {
                    id: 1,
                    order_number: 'ORD-001',
                    status: 'pending',
                    internal_notes: null,
                    pickup_date: '2026-05-02',
                    pickup_time_slot: '08:00-10:00',
                    customer_name: 'Teszt Elek',
                    customer_email: 'teszt@example.com',
                    customer_phone: '+361234567',
                    subtotal: 4000,
                    total: 4500,
                    items: [
                        {
                            id: 1,
                            product_name_snapshot: 'Kovászos kenyér',
                            quantity: 2,
                            unit_price: 2000,
                            line_total: 4000,
                        },
                    ],
                },
                statusOptions: ['pending'],
            },
            global: {
                stubs,
                mocks: { $t: translate },
            },
        });

        expect(wrapper.text()).toContain('Rendelés: ORD-001');
        expect(wrapper.text()).toContain('Ügyfél adatok');
        expect(wrapper.text()).toContain('Rendelési tételek');
        expect(wrapper.text()).toContain('Státusz frissítése');
    });
});
