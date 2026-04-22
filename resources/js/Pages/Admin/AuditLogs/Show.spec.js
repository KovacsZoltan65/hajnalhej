import { mount } from '@vue/test-utils';
import AuditLogsShowPage from './Show.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
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
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Szerepkör jogosultságok szinkronizálva');
        expect(wrapper.text()).toContain('Előtte');
        expect(wrapper.text()).toContain('Utána');
        expect(wrapper.text()).toContain('Környezet');
    });
});
