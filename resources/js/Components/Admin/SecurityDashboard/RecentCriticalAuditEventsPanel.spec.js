import { mount } from '@vue/test-utils';
import RecentCriticalAuditEventsPanel from './RecentCriticalAuditEventsPanel.vue';

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
}));

const stubs = {
    SeverityBadge: { props: ['severity'], template: '<span>{{ severity }}</span>' },
};

describe('RecentCriticalAuditEventsPanel', () => {
    it('renders critical event items', () => {
        const wrapper = mount(RecentCriticalAuditEventsPanel, {
            props: {
                events: [
                    {
                        id: 10,
                        label: 'Permission registry sync',
                        log_name: 'authorization',
                        event_key: 'permissions.synced',
                        severity: 'high',
                        summary: 'Sync eredmeny',
                        timestamp: '2026-04-22T08:00:00+02:00',
                        causer: 'Admin',
                        subject: 'N/A',
                    },
                ],
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Permission registry sync');
        expect(wrapper.text()).toContain('authorization');
        expect(wrapper.text()).toContain('permissions.synced');
    });
});

