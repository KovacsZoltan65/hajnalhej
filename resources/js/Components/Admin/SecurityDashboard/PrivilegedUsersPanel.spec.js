import { mount } from '@vue/test-utils';
import PrivilegedUsersPanel from './PrivilegedUsersPanel.vue';

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
}));

const stubs = {
    RiskBadge: { props: ['level'], template: '<span>{{ level }}</span>' },
};

describe('PrivilegedUsersPanel', () => {
    it('renders privileged user records', () => {
        const wrapper = mount(PrivilegedUsersPanel, {
            props: {
                users: [
                    {
                        id: 1,
                        name: 'Admin User',
                        email: 'admin@example.com',
                        roles: ['admin'],
                        effective_permissions_count: 25,
                        dangerous_permissions_count: 6,
                        risk_level: 'critical',
                        last_relevant_activity_at: '2026-04-22 09:00:00',
                    },
                ],
                links: { user_roles: '/admin/user-roles' },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('Admin User');
        expect(wrapper.text()).toContain('admin@example.com');
        expect(wrapper.text()).toContain('25');
    });
});

