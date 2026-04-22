import { mount } from '@vue/test-utils';
import SecurityDashboardEvent from './Event.vue';

vi.mock('@inertiajs/vue3', () => ({
    Head: { name: 'Head', template: '<span />' },
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
}));

vi.mock('@/Layouts/AdminLayout.vue', () => ({
    default: { template: '<div><slot /></div>' },
}));

const stubs = {
    SectionTitle: { template: '<div />' },
};

describe('Security Dashboard Event page', () => {
    it('renders event details', () => {
        const wrapper = mount(SecurityDashboardEvent, {
            props: {
                event: {
                    id: 12,
                    log_name: 'authorization',
                    event_key: 'permissions.synced',
                    description: 'Permissions synced',
                    created_at: '2026-04-22T09:00:00+02:00',
                    properties: { event_key: 'permissions.synced' },
                },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('authorization');
        expect(wrapper.text()).toContain('permissions.synced');
    });
});

