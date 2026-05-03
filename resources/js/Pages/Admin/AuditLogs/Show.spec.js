import { mount } from '@vue/test-utils';
import AuditLogsShowPage from './Show.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
}));

const translations = {
    'audit_logs.eyebrow': 'Admin / Auditnaplók',
    'audit_logs.show_meta_title': 'Audit bejegyzés #:id',
    'audit_logs.show_title': 'Audit bejegyzés #:id',
    'audit_logs.show_description':
        'Részletes előtte/utána, környezet és eltérés adatok jogosultsági, felhasználói és rendelési eseményekhez.',
    'audit_logs.actions.back_to_list': 'Vissza a listára',
    'audit_logs.columns.event': 'Esemény',
    'audit_logs.columns.created_at': 'Időpont',
    'audit_logs.columns.domain': 'Domain',
    'audit_logs.columns.causer': 'Végrehajtó',
    'audit_logs.columns.subject': 'Érintett elem',
    'audit_logs.sections.before': 'Előtte',
    'audit_logs.sections.after': 'Utána',
    'audit_logs.sections.context': 'Környezet',
    'audit_logs.sections.diff_meta': 'Eltérés / Meta',
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

const stubs = {
    AuditEventBadge: { props: ['eventKey', 'label'], template: '<span>{{ label || eventKey }}</span>' },
    SectionTitle: { template: '<div />' },
};

describe('Admin Audit Logs Show', () => {
    it('renders audit log detail sections', () => {
        const wrapper = mount(AuditLogsShowPage, {
            props: {
                log: {
                    id: 10,
                    event: 'role.permissions.synced',
                    log_name: 'authorization',
                    created_at: '2026-04-21 13:00:00',
                    causer: { name: 'Admin', email: 'admin@example.com' },
                    subject: { label: 'admin', type: 'role' },
                    properties: {
                        before: { permissions: ['products.view'] },
                        after: { permissions: ['products.view', 'orders.view'] },
                        context: { operation: 'role.permissions.sync' },
                        added_permissions: ['orders.view'],
                        removed_permissions: [],
                    },
                },
                eventLabels: { 'role.permissions.synced': 'Szerepkör jogosultságok szinkronizálva' },
            },
            global: {
                stubs,
                mocks: {
                    $t: (key) => translations[key] ?? key,
                },
            },
        });

        expect(wrapper.text()).toContain('Szerepkör jogosultságok szinkronizálva');
        expect(wrapper.text()).toContain('Előtte');
        expect(wrapper.text()).toContain('Utána');
        expect(wrapper.text()).toContain('Környezet');
        expect(wrapper.text()).toContain('Vissza a listára');
    });
});
