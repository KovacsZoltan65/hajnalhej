import { mount } from '@vue/test-utils';
import PermissionsShowPage from './Show.vue';

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
    PermissionBadge: { props: ['name'], template: '<span>{{ name }}</span>' },
    PermissionDangerBadge: { template: '<span>danger</span>' },
    PermissionRegistryStateBadge: { props: ['state'], template: '<span>{{ state }}</span>' },
    PermissionUsageCard: { template: '<div>usage-card</div>' },
    SectionTitle: { template: '<div />' },
};

describe('Admin Permissions Show', () => {
    it('renders permission details and usage card', () => {
        const wrapper = mount(PermissionsShowPage, {
            props: {
                permission: {
                    name: 'permissions.view',
                    registry_state: 'synced',
                    label: 'Jogosultsagok megtekintese',
                    module: 'Roles & Permissions',
                    description: 'Permission lista megtekintese',
                    dangerous: false,
                    guard_name: 'web',
                    audit_sensitive: false,
                    roles_count: 1,
                    users_count: 1,
                    role_names: ['admin'],
                },
            },
            global: { stubs },
        });

        expect(wrapper.text()).toContain('permissions.view');
        expect(wrapper.text()).toContain('usage-card');
    });
});
