import { mount } from '@vue/test-utils';
import SidebarGroup from './SidebarGroup.vue';

vi.mock('@inertiajs/vue3', () => ({
    Link: { name: 'Link', props: ['href'], template: '<a :href="href"><slot /></a>' },
}));

const group = {
    label: 'Katalógus',
    items: [
        { label: 'Products', route: '/admin/products', icon: 'pi pi-box' },
        { label: 'Categories', route: '/admin/categories', icon: 'pi pi-tags' },
    ],
};

describe('SidebarGroup', () => {
    it('renders the group label and menu items', () => {
        const wrapper = mount(SidebarGroup, {
            props: { group },
        });

        expect(wrapper.text()).toContain('Katalógus');
        expect(wrapper.text()).toContain('Products');
        expect(wrapper.text()).toContain('Categories');
    });

    it('can collapse future collapsible groups', async () => {
        const wrapper = mount(SidebarGroup, {
            props: {
                group: {
                    ...group,
                    collapsible: true,
                },
            },
        });

        expect(wrapper.text()).toContain('Products');

        await wrapper.get('button').trigger('click');

        expect(wrapper.find('a[href="/admin/products"]').isVisible()).toBe(false);
    });
});
