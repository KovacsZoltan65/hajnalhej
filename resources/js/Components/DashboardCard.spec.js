import { mount } from '@vue/test-utils';
import DashboardCard from './DashboardCard.vue';

describe('DashboardCard', () => {
    it('renders incoming props', () => {
        const wrapper = mount(DashboardCard, {
            props: {
                title: 'Orders Today',
                value: '24',
                icon: 'pi pi-shopping-cart',
            },
        });

        expect(wrapper.text()).toContain('Orders Today');
        expect(wrapper.text()).toContain('24');
    });
});
