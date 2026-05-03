import { mount } from '@vue/test-utils';
import AuditLogsIndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn() },
}));

const translations = {
    'audit_logs.filters.per_page_option': ':count / oldal',
    'audit_logs.filters.all_events': 'Minden esemény',
    'audit_logs.filters.all_domains': 'Minden domain',
    'audit_logs.filters.all_subjects': 'Minden érintett elem',
    'audit_logs.subject_types.role': 'Szerepkör',
    'audit_logs.subject_types.user': 'Felhasználó',
    'audit_logs.subject_types.order': 'Rendelés',
    'audit_logs.actions.filter': 'Szűrés',
    'audit_logs.actions.details': 'Részletek',
    'audit_logs.meta_title': 'Auditnaplók',
    'audit_logs.eyebrow': 'Admin / Auditnaplók',
    'audit_logs.title': 'Teljes audit napló',
    'audit_logs.description':
        'Jogosultsági, felhasználói aktivitási és rendelési kritikus események egy helyen.',
    'audit_logs.filters.search': 'Keresés',
    'audit_logs.filters.search_placeholder': 'Végrehajtó neve vagy email...',
    'audit_logs.filters.domain': 'Domain',
    'audit_logs.filters.event': 'Esemény',
    'audit_logs.filters.subject_type': 'Érintett elem típusa',
    'audit_logs.filters.per_page': 'Találat / oldal',
    'audit_logs.empty': 'Nincs audit bejegyzés a szűrők szerint.',
    'audit_logs.columns.created_at': 'Időpont',
    'audit_logs.columns.domain': 'Domain',
    'audit_logs.columns.event': 'Esemény',
    'audit_logs.columns.causer': 'Végrehajtó',
    'audit_logs.columns.subject': 'Érintett elem',
    'audit_logs.columns.description': 'Leírás',
    'common.clear_filters': 'Szűrők törlése',
    'common.actions': 'Műveletek',
};

vi.mock('laravel-vue-i18n', () => ({
    trans: (key, replacements = {}) => {
        let value = translations[key] ?? key;

        Object.entries(replacements).forEach(([name, replacement]) => {
            value = value.replace(`:${name}`, replacement);
        });

        return value;
    },
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

vi.mock('primevue/button', () => ({
    default: { props: ['label'], template: '<button>{{ label }}</button>' },
}));
vi.mock('primevue/datatable', () => ({
    default: {
        props: ['value'],
        template: '<div><div v-for="row in value" :key="row.id">{{ row.event_key }}</div><slot name="empty" /><slot /></div>',
    },
}));
vi.mock('primevue/column', () => ({
    default: { template: '<div><slot /></div>' },
}));
vi.mock('primevue/inputtext', () => ({
    default: { template: '<input />' },
}));
vi.mock('primevue/select', () => ({
    default: { template: '<div />' },
}));

const stubs = {
    AdminTableToolbar: { template: '<div><slot name="filters" /><slot name="actions" /></div>' },
    AuditEventBadge: { props: ['eventKey', 'label'], template: '<span>{{ label || eventKey }}</span>' },
    SectionTitle: { template: '<div />' },
};

describe('Admin Audit Logs Index', () => {
    it('renders audit log filters and rows', () => {
        const wrapper = mount(AuditLogsIndexPage, {
            props: {
                logs: {
                    data: [
                        {
                            id: 1,
                            event_key: 'role.created',
                            log_name: 'authorization',
                            description: 'Role created',
                            causer: { name: 'Admin', email: 'admin@example.com' },
                            subject: { label: 'admin', type: 'role' },
                            created_at: '2026-04-21 12:00:00',
                        },
                    ],
                    current_page: 1,
                    per_page: 20,
                    total: 1,
                },
                filters: { search: '', log_name: '', event_key: '', subject_type: '', per_page: 20 },
                logNameLabels: { authorization: 'Authorization', orders: 'Orders', 'user-activity': 'User activity' },
                eventOptions: ['role.created'],
                eventLabels: { 'role.created': 'Role létrehozva' },
                subjectTypeLabels: { role: 'Role', user: 'User', order: 'Order' },
            },
            global: {
                stubs,
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain('Szűrés');
        expect(wrapper.text()).toContain('role.created');
        expect(wrapper.text()).toContain('Keresés');
    });
});

