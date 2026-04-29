import { mount } from '@vue/test-utils';

let mockPage = {
    url: '/admin/dashboard',
};

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
    usePage: () => mockPage,
}));

import AdminSidebar from './AdminSidebar.vue';

describe('AdminSidebar', () => {
    it('renders grouped menu items', () => {
        const wrapper = mount(AdminSidebar, {
            props: {
                groups: [
                    {
                        label: 'Katalógus',
                        items: [
                            { label: 'Products', route: '/admin/products', icon: 'pi pi-box' },
                            { label: 'Categories', route: '/admin/categories', icon: 'pi pi-tags' },
                        ],
                    },
                ],
            },
        });

        expect(wrapper.text()).toContain('Katalógus');
        expect(wrapper.text()).toContain('Products');
        expect(wrapper.text()).toContain('Categories');
    });

    it('marks the current route as active', () => {
        mockPage = { url: '/admin/products' };

        const wrapper = mount(AdminSidebar, {
            props: {
                groups: [
                    {
                        label: 'Katalógus',
                        items: [{ label: 'Products', route: '/admin/products', icon: 'pi pi-box' }],
                    },
                ],
            },
        });

        expect(wrapper.find('a').classes()).toContain('bg-bakery-brown');
    });

    it('skips empty groups and invalid items', () => {
        const wrapper = mount(AdminSidebar, {
            props: {
                groups: [
                    { label: 'Üres', items: [] },
                    { label: 'Adminisztráció', items: [null, { label: 'Roles', route: '/admin/roles' }] },
                ],
            },
        });

        expect(wrapper.text()).not.toContain('Üres');
        expect(wrapper.text()).toContain('Roles');
    });
});

