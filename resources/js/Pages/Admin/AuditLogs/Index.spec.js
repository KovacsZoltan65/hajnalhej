import { mount } from '@vue/test-utils';
import AuditLogsIndexPage from './Index.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    router: { get: vi.fn() },
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
                filters: { search: '', event_key: '', subject_type: '', per_page: 20 },
                eventOptions: ['role.created'],
                eventLabels: { 'role.created': 'Role letrehozva' },
                subjectTypeLabels: { role: 'Role', user: 'User' },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Szures');
        expect(wrapper.text()).toContain('role.created');
    });
});
