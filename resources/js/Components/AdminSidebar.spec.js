import { mount } from '@vue/test-utils';

let mockPage = {
    url: '/admin/dashboard',
    props: {
        auth: {
            can: {
                manage_roles: false,
                assign_user_roles: false,
                view_user_permissions: false,
                manage_permissions: false,
                view_security_dashboard: false,
            },
        },
    },
};

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    usePage: () => mockPage,
}));

import AdminSidebar from './AdminSidebar.vue';

describe('AdminSidebar', () => {
    it('shows permissions menu item when allowed', () => {
        mockPage = {
            ...mockPage,
            props: {
                auth: {
                    can: {
                        manage_roles: false,
                        assign_user_roles: false,
                        view_user_permissions: false,
                        manage_permissions: true,
                        view_security_dashboard: false,
                    },
                },
            },
        };

        const wrapper = mount(AdminSidebar);
        expect(wrapper.text()).toContain('Jogosultságok');
    });

    it('shows security dashboard menu item when allowed', () => {
        mockPage = {
            ...mockPage,
            props: {
                auth: {
                    can: {
                        manage_roles: false,
                        assign_user_roles: false,
                        view_user_permissions: false,
                        manage_permissions: false,
                        view_security_dashboard: true,
                    },
                },
            },
        };

        const wrapper = mount(AdminSidebar);
        expect(wrapper.text()).toContain('Biztonsági irányítópult');
    });
});

